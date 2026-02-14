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

if (!$student_id) {
    echo json_encode(['success' => false, 'error' => 'Student ID required']);
    exit();
}

try {
    $connection->begin_transaction();
    
    // STEP 1: Get CURRENT status
    $current = $connection->query("SELECT payment_status FROM payment 
                                  WHERE student_id = '$student_id' AND is_latest = 1 
                                  LIMIT 1");
    $current_payment = $current->fetch_assoc();
    $current_status = $current_payment['payment_status'] ?? 'Unpaid';
    
    // STEP 2: Mark ALL as not latest (keep history)
    $update_sql = "UPDATE payment SET is_latest = 0 WHERE student_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param("s", $student_id);
    $update_stmt->execute();
    
    // STEP 3: Cycle status
    $total_amount = 50000;
    
    if ($current_status == 'Unpaid') {
        $new_status = 'Partial';
        $amount_paid = 25000;
    } elseif ($current_status == 'Partial') {
        $new_status = 'Paid';
        $amount_paid = 50000;
    } else { // Paid
        $new_status = 'Unpaid';
        $amount_paid = 0;
    }
    
    // STEP 4: Insert NEW record as latest
    $sql = "INSERT INTO payment (student_id, total_amount, amount_paid, payment_status, payment_date, is_latest) 
            VALUES (?, ?, ?, ?, NOW(), 1)";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sdds", $student_id, $total_amount, $amount_paid, $new_status);
    
    if ($stmt->execute()) {
        $connection->commit();
        
        // Get the generated due_amount
        $new_id = $stmt->insert_id;
        $result = $connection->query("SELECT due_amount FROM payment WHERE payment_id = $new_id");
        $due = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true, 
            'message' => "$student_id: $current_status → $new_status",
            'status' => $new_status,
            'amount_paid' => $amount_paid,
            'due_amount' => $due['due_amount']
        ]);
    } else {
        throw new Exception($connection->error);
    }
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>