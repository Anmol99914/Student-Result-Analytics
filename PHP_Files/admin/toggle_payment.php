<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$student_id = $_POST['student_id'] ?? '';
$status = $_POST['status'] ?? '';

if (!$student_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Get student's current semester
$semester_sql = "SELECT c.semester 
                 FROM student s
                 JOIN class c ON s.class_id = c.class_id
                 WHERE s.student_id = ?";
$semester_stmt = $connection->prepare($semester_sql);
$semester_stmt->bind_param("s", $student_id);
$semester_stmt->execute();
$semester_result = $semester_stmt->get_result();
$semester_data = $semester_result->fetch_assoc();
$semester = $semester_data['semester'] ?? 1;

// Begin transaction
$connection->begin_transaction();

try {
    // STEP 1: Mark ALL previous payments as NOT latest
    $update_sql = "UPDATE payment SET is_latest = 0 WHERE student_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param("s", $student_id);
    $update_stmt->execute();
    
    // STEP 2: Set amounts based on status
    $total_amount = 50000.00;
    $amount_paid = ($status == 'Paid') ? $total_amount : (($status == 'Partial') ? 25000.00 : 0.00);
    $due_amount = $total_amount - $amount_paid;
    
    // STEP 3: Insert NEW record as LATEST
    $insert_sql = "INSERT INTO payment (student_id, total_amount, amount_paid, due_amount, payment_status, payment_date, is_latest) 
                   VALUES (?, ?, ?, ?, ?, NOW(), 1)";
    $insert_stmt = $connection->prepare($insert_sql);
    $insert_stmt->bind_param("sddds", $student_id, $total_amount, $amount_paid, $due_amount, $status);
    $insert_stmt->execute();
    
    $connection->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => "Payment status updated to $status for $student_id (Semester $semester)"
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>