<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get form data
$student_id = $_POST['student_id'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$marks_obtained = $_POST['marks_obtained'] ?? '';
$total_marks = $_POST['total_marks'] ?? '';
$semester_id = $_POST['semester_id'] ?? '';
$remarks = $_POST['remarks'] ?? '';
$teacher_id = $_SESSION['teacher_id'];

// Validate inputs
if (empty($student_id) || empty($subject_id) || empty($marks_obtained) || empty($total_marks)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

// Verify teacher has access to this student
$check_sql = "SELECT s.class_id, c.semester
    FROM student s
    JOIN class c ON s.class_id = c.class_id
    WHERE s.student_id = ?
    AND (c.teacher_id = ? OR c.class_id = 
    (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?))";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("sii", $student_id, $teacher_id, $teacher_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$student_data = $check_result->fetch_assoc();

if (!$student_data) {
    echo json_encode(['success' => false, 'message' => 'Access denied to this student']);
    exit();
}

// Use student's semester if not provided
if (empty($semester_id)) {
    $semester_id = $student_data['semester'];
}

// Calculate percentage
$percentage = ($marks_obtained / $total_marks) * 100;

// Calculate grade
function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C+';
    if ($percentage >= 40) return 'C';
    return 'F';
}

$grade = calculateGrade($percentage);

// Check if result already exists
$check_sql = "SELECT result_id FROM result 
    WHERE student_id = ? AND subject_id = ? AND semester_id = ?";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("sii", $student_id, $subject_id, $semester_id);
$check_stmt->execute();
$existing_result = $check_stmt->get_result()->fetch_assoc();

try {
    if ($existing_result) {
        // Update existing result
        $sql = "UPDATE result SET 
            marks_obtained = ?, 
            total_marks = ?,
            percentage = ?,
            grade = ?,
            status = 'submitted',  // Changed from published to submitted
            verification_status = 'pending', // Needs admin verification
            entered_by_teacher_id = ?,
            remarks = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE result_id = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ddddsii", 
            $marks_obtained, 
            $total_marks,
            $percentage,
            $grade,
            $teacher_id,
            $remarks,
            $existing_result['result_id']
        );
    } else {
        // Insert new result
        $sql = "INSERT INTO result 
            (student_id, subject_id, marks_obtained, total_marks, 
             percentage, grade, status, verification_status, 
             semester_id, entered_by_teacher_id, remarks) 
            VALUES (?, ?, ?, ?, ?, ?, 'submitted', 'pending', ?, ?, ?)";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("siidddisis", 
            $student_id, 
            $subject_id, 
            $marks_obtained, 
            $total_marks,
            $percentage,
            $grade,
            $semester_id,
            $teacher_id,
            $remarks
        );
    }
    
    if ($stmt->execute()) {
        // Create notification for admin
        $notification_sql = "INSERT INTO notifications 
            (user_id, user_type, message, type, related_id, related_type) 
            VALUES ('1', 'admin', 'New result submitted for student $student_id', 
                   'result_submitted', ?, 'result')";
        $notification_stmt = $connection->prepare($notification_sql);
        $result_id = $existing_result ? $existing_result['result_id'] : $stmt->insert_id;
        $notification_stmt->bind_param("i", $result_id);
        $notification_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Results submitted for admin verification!',
            'verification_status' => 'pending',
            'percentage' => round($percentage, 2),
            'grade' => $grade
        ]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>