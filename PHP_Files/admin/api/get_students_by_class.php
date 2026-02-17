<?php
// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

// Return error as JSON
function returnError($message) {
    echo json_encode(['error' => $message]);
    exit();
}

// Check admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    returnError('Unauthorized');
}

$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';

if (!$faculty || !$semester) {
    echo json_encode([]);
    exit();
}

try {
    // First, find the class_id
    $class_sql = "SELECT class_id FROM class WHERE faculty = ? AND semester = ? AND status = 'active'";
    $class_stmt = $connection->prepare($class_sql);
    if (!$class_stmt) {
        returnError('Database prepare error: ' . $connection->error);
    }
    
    $class_stmt->bind_param("si", $faculty, $semester);
    $class_stmt->execute();
    $class_result = $class_stmt->get_result();
    $class = $class_result->fetch_assoc();

    if (!$class) {
        echo json_encode([]);
        exit();
    }

    // Get students in this class
    $student_sql = "SELECT student_id, student_name FROM student WHERE class_id = ? AND is_active = 1 ORDER BY student_name";
    $student_stmt = $connection->prepare($student_sql);
    if (!$student_stmt) {
        returnError('Database prepare error: ' . $connection->error);
    }
    
    $student_stmt->bind_param("i", $class['class_id']);
    $student_stmt->execute();
    $result = $student_stmt->get_result();

    $students = [];
    while($row = $result->fetch_assoc()) {
        $students[] = [
            'student_id' => $row['student_id'],
            'student_name' => $row['student_name']
        ];
    }

    echo json_encode($students);
    
} catch (Exception $e) {
    returnError('Exception: ' . $e->getMessage());
}
?>