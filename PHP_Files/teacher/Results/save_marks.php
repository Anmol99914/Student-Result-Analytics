<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start();

session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($connection) || !$connection) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    ob_end_flush();
    exit();
}

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    ob_end_flush();
    exit();
}

$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
$subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
$teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : $_SESSION['teacher_id'];
$marks = isset($_POST['marks']) ? $_POST['marks'] : [];

error_log("========== SAVE MARKS DEBUG ==========");
error_log("Class ID: " . $class_id);
error_log("Subject ID: " . $subject_id);
error_log("Teacher ID: " . $teacher_id);
error_log("Raw marks received: " . print_r($marks, true));

if (!$class_id || !$subject_id || empty($marks)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid data: Missing class, subject or marks']);
    ob_end_flush();
    exit();
}

// =====  Get valid students for this class =====
$valid_students_sql = "SELECT student_id FROM student WHERE class_id = ? AND is_active = 1";
$valid_stmt = $connection->prepare($valid_students_sql);
$valid_stmt->bind_param("i", $class_id);
$valid_stmt->execute();
$valid_result = $valid_stmt->get_result();

$valid_student_ids = [];
while($row = $valid_result->fetch_assoc()) {
    $valid_student_ids[] = $row['student_id'];
}

error_log("Valid students in class $class_id: " . implode(', ', $valid_student_ids));

// ===== Filter marks to only include valid students =====
$filtered_marks = [];
foreach ($marks as $student_id => $marks_obtained) {
    if (in_array($student_id, $valid_student_ids)) {
        $filtered_marks[$student_id] = $marks_obtained;
    } else {
        error_log("Skipping invalid student $student_id - not in class $class_id");
    }
}

$marks = $filtered_marks;
error_log("Filtered marks: " . print_r($marks, true));

if (empty($marks)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'No valid students selected for this class']);
    ob_end_flush();
    exit();
}

// Get semester from class
$semester_sql = "SELECT semester FROM class WHERE class_id = ?";
$semester_stmt = $connection->prepare($semester_sql);
if (!$semester_stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $connection->error]);
    ob_end_flush();
    exit();
}

$semester_stmt->bind_param("i", $class_id);
$semester_stmt->execute();
$semester_result = $semester_stmt->get_result();
$class_data = $semester_result->fetch_assoc();
$semester_id = $class_data['semester'] ?? 1;

$success_count = 0;
$error_count = 0;
$verified_skipped = [];
$errors = [];
$updated_count = 0;
$inserted_count = 0;
$skipped_count = 0;

foreach ($marks as $student_id => $marks_obtained) {
    if (trim($marks_obtained) === '') {
        $skipped_count++;
        continue;
    }
    
    $marks_obtained = floatval($marks_obtained);
    $total_marks = 100;
    $percentage = ($marks_obtained / $total_marks) * 100;
    $grade = calculateGrade($percentage);
    
    // ===== Check if student has verified marks =====
    // ===== Check if student has verified marks =====
$check_verified_sql = "SELECT result_id, marks_obtained, verification_status FROM result 
WHERE student_id = ? AND subject_id = ? 
AND verification_status = 'verified'
LIMIT 1";
$check_verified_stmt = $connection->prepare($check_verified_sql);
$check_verified_stmt->bind_param("si", $student_id, $subject_id);
$check_verified_stmt->execute();
$verified_result = $check_verified_stmt->get_result();
$verified = $verified_result->fetch_assoc();

if ($verified) {
// If the verified record has marks > 0, it's truly verified and cannot be changed
if ($verified['marks_obtained'] > 0) {
$verified_skipped[] = $student_id;
$skipped_count++;
$errors[] = "Student $student_id: Cannot modify - verified marks ({$verified['marks_obtained']}) exist";
continue;
} else {
// Verified record with 0 marks - probably a placeholder
// Delete it so we can insert fresh
error_log("Deleting placeholder verified record for $student_id (marks=0)");
$delete_sql = "DELETE FROM result WHERE result_id = ?";
$delete_stmt = $connection->prepare($delete_sql);
$delete_stmt->bind_param("i", $verified['result_id']);
$delete_stmt->execute();
// Now proceed with insert/update as normal
}
}
    // if ($verified_exists) {
    //     $verified_skipped[] = $student_id;
    //     $skipped_count++;
    //     $errors[] = "Student $student_id: Cannot modify - verified marks exist for this subject";
    //     continue;
    // }
    
    // ===== Check for existing pending result =====
    $check_sql = "SELECT result_id, verification_status FROM result 
                  WHERE student_id = ? AND subject_id = ? AND class_id = ? 
                  AND verification_status != 'verified'";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("sii", $student_id, $subject_id, $class_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        // Update existing pending result
        $update_sql = "UPDATE result SET 
                      marks_obtained = ?, 
                      total_marks = ?, 
                      percentage = ?, 
                      grade = ?,
                      status = 'submitted',
                      verification_status = 'pending',
                      updated_at = NOW(),
                      published_date = NOW()
                      WHERE result_id = ?";
        
        $update_stmt = $connection->prepare($update_sql);
        if (!$update_stmt) {
            $errors[] = "Student $student_id: Update prepare failed";
            $error_count++;
            continue;
        }
        
        $update_stmt->bind_param("dddsi", 
            $marks_obtained, 
            $total_marks,     
            $percentage,      
            $grade,          
            $existing['result_id']
        );
        
        if ($update_stmt->execute()) {
            $success_count++;
            $updated_count++;
            error_log("Updated marks for $student_id: $marks_obtained");
        } else {
            $error_count++;
            $errors[] = "Student $student_id: " . $update_stmt->error;
            error_log("Update failed for $student_id: " . $update_stmt->error);
        }
    } else {
        // Insert new result
        $insert_sql = "INSERT INTO result (
            student_id, subject_id, marks_obtained, total_marks, 
            percentage, grade, semester_id, entered_by_teacher_id, 
            status, verification_status, published_date, class_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 'pending', NOW(), ?)";
        
        $insert_stmt = $connection->prepare($insert_sql);
        if (!$insert_stmt) {
            $errors[] = "Student $student_id: Insert prepare failed";
            $error_count++;
            continue;
        }
        
        $insert_stmt->bind_param("sidddsiii", 
            $student_id,      
            $subject_id,      
            $marks_obtained,  
            $total_marks,     
            $percentage,      
            $grade,          
            $semester_id,    
            $teacher_id,     
            $class_id        
        );
        
        if ($insert_stmt->execute()) {
            $success_count++;
            $inserted_count++;
            error_log("Inserted marks for $student_id: $marks_obtained");
        } else {
            $error_count++;
            $errors[] = "Student $student_id: " . $insert_stmt->error;
            error_log("Insert failed for $student_id: " . $insert_stmt->error);
        }
    }
}

ob_clean();

$response = [
    'success' => ($success_count > 0),
    'message' => '',
    'summary' => [
        'total' => count($marks),
        'inserted' => $inserted_count,
        'updated' => $updated_count,
        'skipped' => $skipped_count,
        'errors' => $error_count,
        'verified_skipped' => count($verified_skipped)
    ]
];

if ($success_count > 0) {
    $response['success'] = true;
    $response['message'] = "Saved $success_count student(s)";
    
    if (!empty($verified_skipped)) {
        $response['message'] .= "Error:" . count($verified_skipped) . " verified student(s) were skipped";
        $response['verified_skipped'] = $verified_skipped;
    }
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
} else {
    if (!empty($verified_skipped) && $success_count == 0) {
        $response['message'] = "No changes saved. All selected students have verified marks.";
        $response['verified_skipped'] = $verified_skipped;
    } else {
        $response['message'] = "Failed to save marks";
        $response['errors'] = $errors;
    }
}

echo json_encode($response);
ob_end_flush();
exit();

function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C+';
    if ($percentage >= 40) return 'C';
    return 'F';
}
?>