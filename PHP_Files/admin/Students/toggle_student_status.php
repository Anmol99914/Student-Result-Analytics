<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $data['student_id'] ?? '';
$new_status = $data['status'] ?? null;

if (!$student_id) {
    echo json_encode(['success' => false, 'error' => 'Student ID required']);
    exit();
}

// Get current status
$current = $connection->query("SELECT is_active FROM student WHERE student_id = '$student_id'");
$current_status = $current->fetch_assoc()['is_active'];

// Toggle if not specified
if ($new_status === null) {
    $new_status = $current_status ? 0 : 1;
}

$sql = "UPDATE student SET is_active = ?, updated_at = NOW() WHERE student_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("is", $new_status, $student_id);

if ($stmt->execute()) {
    $status_text = $new_status ? 'activated' : 'deactivated';
    echo json_encode(['success' => true, 'message' => "Student $status_text successfully", 'status' => $new_status]);
} else {
    echo json_encode(['success' => false, 'error' => $connection->error]);
}
?>