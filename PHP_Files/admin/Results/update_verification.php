<?php
// Results/update_verification.php 
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if (!$result_id || !in_array($status, ['verified', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// SIMPLE UPDATE - only update what exists
$sql = "UPDATE result SET 
        verification_status = ?,
        updated_at = NOW()";
$params = [$status];
$types = "s";

// Add reason if column exists
$check = $connection->query("SHOW COLUMNS FROM result LIKE 'verification_reason'");
if ($check && $check->num_rows > 0) {
    $sql .= ", verification_reason = ?";
    $params[] = $reason;
    $types .= "s";
}

// Add verification date if column exists
$check = $connection->query("SHOW COLUMNS FROM result LIKE 'verification_date'");
if ($check && $check->num_rows > 0) {
    $sql .= ", verification_date = NOW()";
}

$sql .= " WHERE result_id = ?";
$params[] = $result_id;
$types .= "i";

$stmt = $connection->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => "Result marked as {$status}"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
?>