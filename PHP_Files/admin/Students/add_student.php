<?php
// Turn off error display - they should go to log, not output
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

// Catch any errors and return as JSON
function returnError($message) {
    echo json_encode(['success' => false, 'error' => $message]);
    exit();
}

try {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
        returnError('Access denied');
    }

    $student_id = $_POST['student_id'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($password)) {
        returnError('Password is required');
    }
    
    // $class_id = $_POST['class_id'] ?? null;
    $class_id = $_POST['class_id'] ?? '';
    $phone_number = $_POST['phone_number'] ?? null;
    $admission_year = $_POST['admission_year'] ?? date('Y');
    $batch_code = $_POST['batch_code'] ?? (date('Y') . '-' . (date('Y')+4));

    if (!$student_id || !$student_name || !$email) {
        returnError('Required fields missing');
    }

    // Check if student ID already exists
    $check = $connection->query("SELECT student_id FROM student WHERE student_id = '$student_id'");
    if (!$check) {
        returnError('Database error: ' . $connection->error);
    }
    
    if ($check->num_rows > 0) {
        returnError('Student ID already exists');
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    if (!$hashed_password) {
        returnError('Password hashing failed');
    }

if (empty($class_id)) {
    echo json_encode(['success' => false, 'error' => 'Class selection is required']);
    exit();
}

    // Get semester_id from class
    $semester_id = null;
    if ($class_id) {
        $sem = $connection->query("SELECT semester FROM class WHERE class_id = $class_id");
        if ($sem && $sem->num_rows > 0) {
            $semester_id = $sem->fetch_assoc()['semester'];
        }
    }

    

    $sql = "INSERT INTO student (student_id, student_name, email, password, class_id, semester_id, 
                                phone_number, is_active, created_at, updated_at, admission_year, batch_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW(), ?, ?)";

    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        returnError('Prepare failed: ' . $connection->error);
    }

    $stmt->bind_param("ssssiisss", 
        $student_id, $student_name, $email, $hashed_password, 
        $class_id, $semester_id, $phone_number, $admission_year, $batch_code
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student added successfully']);
    } else {
        returnError('Execute failed: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    returnError('Exception: ' . $e->getMessage());
}
?>