<?php
// Results/get_verification_stats.php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    $today = date('Y-m-d');
    
    // Count verified today - using updated_at column (your table has this)
    $verified_today_sql = "SELECT COUNT(*) as count FROM result 
                          WHERE verification_status = 'verified' 
                          AND DATE(updated_at) = ?";
    $stmt = $connection->prepare($verified_today_sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $verified_today = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Count rejected today
    $rejected_today_sql = "SELECT COUNT(*) as count FROM result 
                          WHERE verification_status = 'rejected' 
                          AND DATE(updated_at) = ?";
    $stmt = $connection->prepare($rejected_today_sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $rejected_today = $stmt->get_result()->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    // Total verified
    $total_verified_sql = "SELECT COUNT(*) as count FROM result WHERE verification_status = 'verified'";
    $total_verified = $connection->query($total_verified_sql)->fetch_assoc()['count'] ?? 0;
    
    // Pending count
    $pending_sql = "SELECT COUNT(*) as count FROM result WHERE verification_status = 'pending'";
    $pending_count = $connection->query($pending_sql)->fetch_assoc()['count'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'verified_today' => (int)$verified_today,
        'rejected_today' => (int)$rejected_today,
        'total_verified' => (int)$total_verified,
        'pending_count' => (int)$pending_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>