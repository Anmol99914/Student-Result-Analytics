<?php
session_start();
require_once '../../../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Get pending results count
$sql = "SELECT COUNT(*) as count FROM result WHERE verification_status = 'pending'";
$result = $connection->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'count' => $row['count']
    ]);
} else {
    echo json_encode(['success' => false, 'count' => 0]);
}
?>