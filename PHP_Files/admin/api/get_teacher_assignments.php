<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

error_log("get_teacher_assignments.php called");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    error_log("Access denied");
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$teacher_id = $_GET['teacher_id'] ?? 0;
$class_id = $_GET['class_id'] ?? 0;

error_log("Teacher ID: $teacher_id, Class ID: $class_id");

if (!$teacher_id || !$class_id) {
    echo json_encode(['success' => false, 'error' => 'Teacher ID and Class ID required']);
    exit();
}

try {
    // Get assigned subjects - FIXED: specify table name
    $query = "SELECT subject_id FROM teacher_subject_assignment 
              WHERE teacher_id = ? AND class_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $teacher_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assigned_ids = [];
    while($row = $result->fetch_assoc()) {
        $assigned_ids[] = (int)$row['subject_id'];
    }

    // Check duplicates - FIXED: specify table aliases
    $duplicate_query = "SELECT tsa.subject_id, t.teacher_id, t.name 
                        FROM teacher_subject_assignment tsa
                        JOIN teacher t ON tsa.teacher_id = t.teacher_id
                        WHERE tsa.class_id = ? AND tsa.teacher_id != ?";
    $stmt = $connection->prepare($duplicate_query);
    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    $duplicates = $stmt->get_result();

    $duplicate_map = [];
    while($row = $duplicates->fetch_assoc()) {
        $duplicate_map[$row['subject_id']] = [
            'teacher_id' => $row['teacher_id'],
            'teacher_name' => $row['name']
        ];
    }

    echo json_encode([
        'success' => true,
        'assigned_ids' => $assigned_ids,
        'duplicates' => $duplicate_map
    ]);

} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>