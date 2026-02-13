<?php
// api/assign_teacher_subjects.php 
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$teacher_id = $_POST['teacher_id'] ?? 0;
$class_id = $_POST['class_id'] ?? 0;
$subject_ids = $_POST['subject_ids'] ?? [];

if (!$teacher_id || !$class_id) {
    echo json_encode(['success' => false, 'error' => 'Teacher ID and Class ID required']);
    exit();
}

// Check if teacher is ACTIVE
$check_teacher = $connection->prepare("SELECT status FROM teacher WHERE teacher_id = ?");
$check_teacher->bind_param("i", $teacher_id);
$check_teacher->execute();
$teacher_result = $check_teacher->get_result();
$teacher_data = $teacher_result->fetch_assoc();

if (!$teacher_data) {
    echo json_encode(['success' => false, 'error' => 'Teacher not found']);
    exit();
}

if ($teacher_data['status'] !== 'active') {
    echo json_encode([
        'success' => false, 
        'error' => 'Cannot assign subjects to inactive teachers. Please activate the teacher first.'
    ]);
    exit();
}

// Also check if class is active
$check_class = $connection->prepare("SELECT status FROM class WHERE class_id = ?");
$check_class->bind_param("i", $class_id);
$check_class->execute();
$class_result = $check_class->get_result();
$class_data = $class_result->fetch_assoc();

if (!$class_data) {
    echo json_encode(['success' => false, 'error' => 'Class not found']);
    exit();
}

if ($class_data['status'] !== 'active') {
    echo json_encode([
        'success' => false, 
        'error' => 'Cannot assign subjects to inactive classes. Please activate the class first.'
    ]);
    exit();
}

try {
    $connection->begin_transaction();
    
    // Delete existing assignments for this teacher AND class
    $delete_sql = "DELETE FROM teacher_subject_assignment 
                   WHERE teacher_id = ? AND class_id = ?";
    $stmt = $connection->prepare($delete_sql);
    $stmt->bind_param("ii", $teacher_id, $class_id);
    $stmt->execute();
    
    // Insert new assignments
    if (!empty($subject_ids)) {
        $insert_sql = "INSERT INTO teacher_subject_assignment (teacher_id, subject_id, class_id) 
                       VALUES (?, ?, ?)";
        $stmt = $connection->prepare($insert_sql);
        
        foreach ($subject_ids as $subject_id) {
            $stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
            $stmt->execute();
        }
    }
    
    $connection->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>