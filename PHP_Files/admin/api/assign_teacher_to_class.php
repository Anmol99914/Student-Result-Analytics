<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$teacher_id = $_POST['teacher_id'] ?? 0;
$class_id = $_POST['class_id'] ?? 0;
$assignment_type = $_POST['assignment_type'] ?? 'primary';

if (!$teacher_id || !$class_id) {
    echo json_encode(['success' => false, 'error' => 'Teacher ID and Class ID required']);
    exit();
}

try {
    // Check if already assigned
    $check = $connection->query("SELECT id FROM teacher_class_assignments 
                                 WHERE teacher_id = $teacher_id AND class_id = $class_id");
    
    if ($check->num_rows > 0) {
        // Update
        $query = "UPDATE teacher_class_assignments 
                  SET status = 'active', assignment_type = '$assignment_type' 
                  WHERE teacher_id = $teacher_id AND class_id = $class_id";
    } else {
        // Insert
        $query = "INSERT INTO teacher_class_assignments (teacher_id, class_id, assignment_type, status) 
                  VALUES ($teacher_id, $class_id, '$assignment_type', 'active')";
    }
    
    $connection->query($query);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>