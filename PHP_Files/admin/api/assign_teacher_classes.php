<?php
// api/assign_teacher_classes.php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$teacher_id = $_POST['teacher_id'] ?? 0;
$class_ids = $_POST['class_ids'] ?? [];

if (!$teacher_id) {
    echo json_encode(['success' => false, 'error' => 'Teacher ID required']);
    exit();
}

try {
    $connection->begin_transaction();
    
    // Delete existing assignments
    $delete_sql = "DELETE FROM teacher_class_assignments WHERE teacher_id = ?";
    $stmt = $connection->prepare($delete_sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    
    // Insert new assignments
    if (!empty($class_ids)) {
        $insert_sql = "INSERT INTO teacher_class_assignments (teacher_id, class_id) VALUES (?, ?)";
        $stmt = $connection->prepare($insert_sql);
        
        foreach ($class_ids as $class_id) {
            $stmt->bind_param("ii", $teacher_id, $class_id);
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