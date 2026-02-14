<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$pending = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'pending'")->fetch_assoc()['count'];
$verified = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'verified'")->fetch_assoc()['count'];
$rejected = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'rejected'")->fetch_assoc()['count'];

echo json_encode([
    ['name' => 'Pending', 'y' => (int)$pending, 'color' => '#ffc107'],
    ['name' => 'Verified', 'y' => (int)$verified, 'color' => '#28a745'],
    ['name' => 'Rejected', 'y' => (int)$rejected, 'color' => '#dc3545']
]);
?>