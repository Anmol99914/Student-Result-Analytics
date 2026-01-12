<?php
// Results/update_verification.php - FIXED VERSION

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Start session
session_start();

// Require config
require_once '../../../config.php';

// Clear any output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Check session
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Get POST data
$result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

// Validate
if ($result_id <= 0 || !in_array($status, ['verified', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // First, check what columns exist
    $check_columns = $connection->query("SHOW COLUMNS FROM result LIKE 'verification_date'");
    $has_verification_date = ($check_columns && $check_columns->num_rows > 0);
    
    $check_columns = $connection->query("SHOW COLUMNS FROM result LIKE 'verification_reason'");
    $has_verification_reason = ($check_columns && $check_columns->num_rows > 0);
    
    $check_columns = $connection->query("SHOW COLUMNS FROM result LIKE 'verified_by'");
    $has_verified_by = ($check_columns && $check_columns->num_rows > 0);
    
    $check_columns = $connection->query("SHOW COLUMNS FROM result LIKE 'updated_at'");
    $has_updated_at = ($check_columns && $check_columns->num_rows > 0);
    
    // Build dynamic SQL based on available columns
    $sql_parts = ["verification_status = ?"];
    $param_types = "s";
    $param_values = [$status];
    
    if ($has_verification_date) {
        $sql_parts[] = "verification_date = NOW()";
    }
    
    if ($has_verification_reason) {
        $sql_parts[] = "verification_reason = ?";
        $param_types .= "s";
        $param_values[] = $reason;
    }
    
    if ($has_verified_by) {
        $sql_parts[] = "verified_by = ?";
        $param_types .= "i";
        $param_values[] = $_SESSION['admin_id'] ?? 0;
    }
    
    if ($has_updated_at) {
        $sql_parts[] = "updated_at = NOW()";
    }
    
    // Final SQL
    $update_sql = "UPDATE result SET " . implode(", ", $sql_parts) . " WHERE result_id = ?";
    $param_types .= "i";
    $param_values[] = $result_id;
    
    // Prepare and execute
    $update_stmt = $connection->prepare($update_sql);
    
    if ($update_stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $connection->error]);
        exit();
    }
    
    // Bind parameters dynamically
    $update_stmt->bind_param($param_types, ...$param_values);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Result marked as {$status}",
            'columns_used' => $sql_parts
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $update_stmt->error]);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// End output buffering
ob_end_flush();
?>