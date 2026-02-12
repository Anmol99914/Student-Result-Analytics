<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$class_id = $_GET['class_id'] ?? 0;

$query = "SELECT teacher_id, name, email 
          FROM teacher 
          WHERE status = 'active' 
          AND teacher_id NOT IN (
              SELECT teacher_id FROM teacher_class_assignments 
              WHERE class_id = ? AND status = 'active'
          )
          ORDER BY name";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

echo json_encode([
    'success' => true,
    'teachers' => $teachers
]);
?>