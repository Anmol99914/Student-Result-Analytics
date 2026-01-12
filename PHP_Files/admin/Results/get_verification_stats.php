<?php
// Results/get_verification_stats.php - FIXED VERSION
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    // Get today's date
    $today = date('Y-m-d');
    
    // First, check what columns exist
    $has_verification_date = false;
    $has_updated_at = false;
    
    $check = $connection->query("SHOW COLUMNS FROM result LIKE 'verification_date'");
    if ($check && $check->num_rows > 0) {
        $has_verification_date = true;
    }
    
    $check = $connection->query("SHOW COLUMNS FROM result LIKE 'updated_at'");
    if ($check && $check->num_rows > 0) {
        $has_updated_at = true;
    }
    
    // Count verified TODAY - use whichever date column exists
    if ($has_verification_date) {
        $verified_today_sql = "SELECT COUNT(*) as count FROM result 
                              WHERE verification_status = 'verified' 
                              AND DATE(verification_date) = ?";
    } elseif ($has_updated_at) {
        $verified_today_sql = "SELECT COUNT(*) as count FROM result 
                              WHERE verification_status = 'verified' 
                              AND DATE(updated_at) = ?";
    } else {
        // If no date column, count all verified (fallback)
        $verified_today_sql = "SELECT COUNT(*) as count FROM result 
                              WHERE verification_status = 'verified'";
    }
    
    $stmt1 = $connection->prepare($verified_today_sql);
    if (strpos($verified_today_sql, '?') !== false) {
        $stmt1->bind_param("s", $today);
    }
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $verified_today = $result1->fetch_assoc()['count'] ?? 0;
    $stmt1->close();
    
    // Count rejected TODAY
    if ($has_verification_date) {
        $rejected_today_sql = "SELECT COUNT(*) as count FROM result 
                              WHERE verification_status = 'rejected' 
                              AND DATE(verification_date) = ?";
    } elseif ($has_updated_at) {
        $rejected_today_sql = "SELECT COUNT(*) as count FROM result 
                              WHERE verification_status = 'rejected' 
                              AND DATE(updated_at) = ?";
    } else {
        $rejected_today_sql = "SELECT COUNT(*) as count FROM result 
                              WHERE verification_status = 'rejected'";
    }
    
    $stmt2 = $connection->prepare($rejected_today_sql);
    if (strpos($rejected_today_sql, '?') !== false) {
        $stmt2->bind_param("s", $today);
    }
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $rejected_today = $result2->fetch_assoc()['count'] ?? 0;
    $stmt2->close();
    
    // Count total verified (ALL TIME)
    $total_verified_sql = "SELECT COUNT(*) as count FROM result 
                          WHERE verification_status = 'verified'";
    $result3 = $connection->query($total_verified_sql);
    $total_verified = $result3->fetch_assoc()['count'] ?? 0;
    
    // Count pending
    $pending_sql = "SELECT COUNT(*) as count FROM result 
                   WHERE verification_status = 'pending'";
    $result4 = $connection->query($pending_sql);
    $pending_count = $result4->fetch_assoc()['count'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'verified_today' => (int)$verified_today,
        'rejected_today' => (int)$rejected_today,
        'total_verified' => (int)$total_verified,
        'pending_count' => (int)$pending_count,
        'debug' => [
            'has_verification_date' => $has_verification_date,
            'has_updated_at' => $has_updated_at,
            'today' => $today
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>