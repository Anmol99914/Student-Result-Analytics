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
$phone_number = $_POST['phone_number'] ?? null;
$class_id = $_POST['class_id'] ?? null;
$is_active = $_POST['is_active'] ?? 1;
$admission_year = $_POST['admission_year'] ?? date('Y');
$batch_code = $_POST['batch_code'] ?? '';
$password = $_POST['password'] ?? '';

if (!$student_id || !$student_name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Required fields missing']);
    exit();
}

try {
    // Get semester_id from class
    $semester_id = null;
    if ($class_id) {
        $sem = $connection->query("SELECT semester FROM class WHERE class_id = $class_id");
        if ($sem->num_rows > 0) {
            $semester_id = $sem->fetch_assoc()['semester'];
        }
    }
    
    // Build update query
    $sql = "UPDATE student SET 
            student_name = ?,
            email = ?,
            phone_number = ?,
            class_id = ?,
            semester_id = ?,
            is_active = ?,
            admission_year = ?,
            batch_code = ?,
            updated_at = NOW()";
    
    $params = [$student_name, $email, $phone_number, $class_id, $semester_id, $is_active, $admission_year, $batch_code];
    $types = "sssiiiss";
    
    // Add password if provided
    if (!empty($password)) {
        $sql .= ", password = ?";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $params[] = $hashed_password;
        $types .= "s";
    }
    
    $sql .= " WHERE student_id = ?";
    $params[] = $student_id;
    $types .= "s";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student updated successfully']);
    } else {
        throw new Exception($connection->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>