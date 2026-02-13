<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$result_id = $data['result_id'] ?? 0;
$action = $data['action'] ?? '';
$comments = $data['comments'] ?? '';

if (!$result_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

try {
    if ($action === 'approve') {
        // 🔥 FIX: Set status to 'published' NOT 'verified'
        $sql = "UPDATE result SET 
                verification_status = 'verified', 
                status = 'published',
                verified_by_admin_id = ?,
                verification_date = NOW(),
                comments = ?,
                updated_at = NOW()
                WHERE result_id = ?";
        
        $stmt = $connection->prepare($sql);
        $admin_id = $_SESSION['admin_id'] ?? 1;
        $stmt->bind_param("isi", $admin_id, $comments, $result_id);
        
    } elseif ($action === 'reject') {
        $sql = "UPDATE result SET 
                verification_status = 'rejected', 
                status = 'rejected',
                verified_by_admin_id = ?,
                verification_date = NOW(),
                comments = ?,
                updated_at = NOW()
                WHERE result_id = ?";
        
        $stmt = $connection->prepare($sql);
        $admin_id = $_SESSION['admin_id'] ?? 1;
        $stmt->bind_param("isi", $admin_id, $comments, $result_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Result ' . $action . 'd successfully']);
    } else {
        throw new Exception($stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>