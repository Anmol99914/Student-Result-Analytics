<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$class_id = $_GET['class_id'] ?? 0;

if (!$class_id) {
    echo json_encode(['success' => false, 'error' => 'Class ID required']);
    exit();
}

// Get class semester and faculty
$class_query = "SELECT faculty, semester FROM class WHERE class_id = ?";
$stmt = $connection->prepare($class_query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if (!$class) {
    echo json_encode(['success' => false, 'error' => 'Class not found']);
    exit();
}

// Get faculty_id
$faculty_query = "SELECT faculty_id FROM faculty WHERE faculty_code = ?";
$stmt = $connection->prepare($faculty_query);
$stmt->bind_param("s", $class['faculty']);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();

if (!$faculty) {
    echo json_encode(['success' => false, 'error' => 'Faculty not found']);
    exit();
}

// Get subjects for this faculty and semester
$subjects_query = "SELECT subject_id, subject_name, subject_code, credits 
                   FROM subject 
                   WHERE faculty_id = ? AND semester = ? AND status = 'active'
                   ORDER BY subject_name";
$stmt = $connection->prepare($subjects_query);
$stmt->bind_param("ii", $faculty['faculty_id'], $class['semester']);
$stmt->execute();
$subjects = $stmt->get_result();

$data = [];
while($row = $subjects->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'faculty' => $class['faculty'],
    'semester' => $class['semester']
]);
?>