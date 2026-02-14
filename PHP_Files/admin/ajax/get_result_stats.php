<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get counts using verification_status
$pending = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'pending'")->fetch_assoc()['count'];
$verified = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'verified'")->fetch_assoc()['count'];
$rejected = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'rejected'")->fetch_assoc()['count'];

// Get today's verified count
$verified_today = $connection->query("
    SELECT COUNT(*) as count 
    FROM result 
    WHERE verification_status = 'verified' 
    AND DATE(verification_date) = CURDATE()
")->fetch_assoc()['count'];

$total = $pending + $verified + $rejected;

echo json_encode([
    'pending' => (int)$pending,
    'verified' => (int)$verified,
    'rejected' => (int)$rejected,
    'verified_today' => (int)$verified_today,
    'total' => (int)$total
]);
?>