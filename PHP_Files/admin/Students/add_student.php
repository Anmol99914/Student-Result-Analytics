<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$student_id = $_POST['student_id'] ?? '';
$student_name = $_POST['student_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
if (empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Password is required']);
    exit();
}
$class_id = $_POST['class_id'] ?? null;
$phone_number = $_POST['phone_number'] ?? null;
$admission_year = $_POST['admission_year'] ?? date('Y');
$batch_code = $_POST['batch_code'] ?? (date('Y') . '-' . (date('Y')+4));

if (!$student_id || !$student_name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Required fields missing']);
    exit();
}

// Check if student ID already exists
$check = $connection->query("SELECT student_id FROM student WHERE student_id = '$student_id'");
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Student ID already exists']);
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Get semester_id from class
$semester_id = null;
if ($class_id) {
    $sem = $connection->query("SELECT semester FROM class WHERE class_id = $class_id");
    if ($sem->num_rows > 0) {
        $semester_id = $sem->fetch_assoc()['semester'];
    }
}

$sql = "INSERT INTO student (student_id, student_name, email, password, class_id, semester_id, 
                            phone_number, is_active, created_at, updated_at, admission_year, batch_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW(), ?, ?)";

$stmt = $connection->prepare($sql);
$stmt->bind_param("ssssiisss", 
    $student_id, $student_name, $email, $hashed_password, 
    $class_id, $semester_id, $phone_number, $admission_year, $batch_code
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Student added successfully']);
} else {
    echo json_encode(['success' => false, 'error' => $connection->error]);
}
?>