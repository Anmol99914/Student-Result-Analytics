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

try {
    // Get current payment
    $current = $connection->query("SELECT payment_status, due_amount FROM payment 
                                  WHERE student_id = '$student_id' 
                                  ORDER BY payment_id DESC LIMIT 1");
    $current_payment = $current->fetch_assoc();
    
    // Cycle through statuses: Unpaid → Partial → Paid
    if (!$new_status) {
        $status_cycle = ['Unpaid', 'Partial', 'Paid'];
        $current_idx = array_search($current_payment['payment_status'] ?? 'Unpaid', $status_cycle);
        $next_idx = ($current_idx === false || $current_idx === 2) ? 0 : $current_idx + 1;
        $new_status = $status_cycle[$next_idx];
    }
    
    // Set due amount based on status
    $due_amount = 0;
    $total_amount = 50000; // Default total fee
    
    if ($new_status == 'Unpaid') {
        $due_amount = $total_amount;
    } elseif ($new_status == 'Partial') {
        $due_amount = $total_amount / 2;
    } elseif ($new_status == 'Paid') {
        $due_amount = 0;
    }
    
    // Insert new payment record
    $sql = "INSERT INTO payment (student_id, total_amount, amount_paid, due_amount, payment_status, payment_date) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $amount_paid = $total_amount - $due_amount;
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sddds", $student_id, $total_amount, $amount_paid, $due_amount, $new_status);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => "Payment status updated to $new_status",
            'status' => $new_status,
            'due_amount' => $due_amount
        ]);
    } else {
        throw new Exception($connection->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>