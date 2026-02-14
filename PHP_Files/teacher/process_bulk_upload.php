<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$class_id = $_POST['class_id'] ?? 0;
$subject_id = $_POST['subject_id'] ?? 0;
$teacher_id = $_POST['teacher_id'] ?? 0;

if (!$class_id || !$subject_id || !$teacher_id) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit();
}

// Handle file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit();
}

$file = $_FILES['file']['tmp_name'];
$handle = fopen($file, 'r');

// Skip header row
fgetcsv($handle);

$inserted = 0;
$updated = 0;
$skipped = 0;

while (($data = fgetcsv($handle)) !== FALSE) {
    if (count($data) < 3) continue;
    
    $student_id = trim($data[0]);
    $student_name = trim($data[1]); // For reference only
    $marks = trim($data[2]);
    
    // Skip if marks is empty
    if ($marks === '') {
        $skipped++;
        continue;
    }
    
    // Validate marks
    if (!is_numeric($marks) || $marks < 0 || $marks > 100) {
        $skipped++;
        continue;
    }
    
    // Check if result exists and is not verified
    $check_sql = "SELECT result_id, verification_status FROM result 
                  WHERE student_id = ? AND subject_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("si", $student_id, $subject_id);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    
    if ($existing && $existing['verification_status'] == 'verified') {
        $skipped++;
        continue;
    }
    
    // Calculate grade
    $percentage = $marks;
    if ($percentage >= 90) $grade = 'A+';
    elseif ($percentage >= 80) $grade = 'A';
    elseif ($percentage >= 70) $grade = 'B+';
    elseif ($percentage >= 60) $grade = 'B';
    elseif ($percentage >= 50) $grade = 'C+';
    elseif ($percentage >= 40) $grade = 'C';
    else $grade = 'F';
    
    if ($existing) {
        // Update
        $sql = "UPDATE result SET 
                marks_obtained = ?,
                percentage = ?,
                grade = ?,
                updated_at = NOW()
                WHERE result_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ddsi", $marks, $percentage, $grade, $existing['result_id']);
        $stmt->execute();
        $updated++;
    } else {
        // Insert
        $sql = "INSERT INTO result (student_id, subject_id, marks_obtained, total_marks, 
                                    percentage, grade, entered_by_teacher_id, class_id)
                VALUES (?, ?, ?, 100, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("siiddii", $student_id, $subject_id, $marks, $percentage, $grade, $teacher_id, $class_id);
        $stmt->execute();
        $inserted++;
    }
}

fclose($handle);

echo json_encode([
    'success' => true,
    'inserted' => $inserted,
    'updated' => $updated,
    'skipped' => $skipped,
    'message' => "Bulk upload complete: $inserted new, $updated updated, $skipped skipped"
]);
?>