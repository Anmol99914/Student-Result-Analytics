<?php
session_start();
include '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!$current || !$new || !$confirm) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if ($new !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit();
}

if (strlen($new) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

// Get current password
$stmt = $connection->prepare("SELECT password FROM teacher WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    echo json_encode(['success' => false, 'message' => 'Teacher not found']);
    exit();
}

// Verify current password
if (!password_verify($current, $teacher['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit();
}

// Hash new password
$hashed = password_hash($new, PASSWORD_DEFAULT);

// Update password
$update = $connection->prepare("UPDATE teacher SET password = ? WHERE teacher_id = ?");
$update->bind_param("si", $hashed, $teacher_id);

if ($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>