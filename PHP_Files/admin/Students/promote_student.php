<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$student_id = $_POST['student_id'] ?? '';

if (empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID required']);
    exit();
}

try {
    $connection->begin_transaction();
    
    // Get student's current class and semester
    $sql = "SELECT s.class_id, c.semester, c.faculty 
            FROM student s
            JOIN class c ON s.class_id = c.class_id
            WHERE s.student_id = ?";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        throw new Exception('Student not found');
    }

    // ===== CHECK 1: Payment status =====
    $payment_sql = "SELECT payment_status FROM payment 
                    WHERE student_id = ? AND is_latest = 1 
                    LIMIT 1";
    $payment_stmt = $connection->prepare($payment_sql);
    $payment_stmt->bind_param("s", $student_id);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payment = $payment_result->fetch_assoc();
    $payment_status = $payment['payment_status'] ?? 'Unpaid';

    if ($payment_status !== 'Paid') {
        throw new Exception('Cannot promote: Student has not paid fees for current semester. Current status: ' . $payment_status);
    }

    // ===== CHECK 2: All current semester results are verified =====
    $current_semester = (int)$student['semester'];
    
    $results_sql = "SELECT COUNT(*) as total, 
                           SUM(CASE WHEN verification_status = 'verified' THEN 1 ELSE 0 END) as verified
                    FROM result 
                    WHERE student_id = ? AND semester_id = ?";
    $results_stmt = $connection->prepare($results_sql);
    $results_stmt->bind_param("si", $student_id, $current_semester);
    $results_stmt->execute();
    $results_data = $results_stmt->get_result()->fetch_assoc();
    
    if ($results_data['total'] > 0 && $results_data['verified'] < $results_data['total']) {
        throw new Exception('Cannot promote: Not all results are verified for current semester.');
    }

    $next_semester = $current_semester + 1;
    $faculty = $student['faculty'];

    // Check if already in final semester
    if ($current_semester >= 8) {
        throw new Exception('Student already in final semester (8)');
    }

    // Check if next semester class exists
    $class_sql = "SELECT class_id FROM class 
                  WHERE faculty = ? AND semester = ? AND status = 'active'";
    $class_stmt = $connection->prepare($class_sql);
    $class_stmt->bind_param("si", $faculty, $next_semester);
    $class_stmt->execute();
    $class_result = $class_stmt->get_result();

    if ($class_result->num_rows == 0) {
        throw new Exception("Class for Semester $next_semester not found. Please create it first.");
    }

    $new_class = $class_result->fetch_assoc();
    $new_class_id = $new_class['class_id'];

    // ===== STEP 1: Remove from old class enrollments =====
    // If you have an enrollment table, delete old records
    $delete_enrollments = "DELETE FROM student_subject_enrollment 
                          WHERE student_id = ?";
    $delete_stmt = $connection->prepare($delete_enrollments);
    $delete_stmt->bind_param("s", $student_id);
    $delete_stmt->execute();

    // ===== STEP 2: Payment reset for new semester =====
    // Mark all previous payments as NOT latest
    $update_old_payments = "UPDATE payment SET is_latest = 0 WHERE student_id = ?";
    $update_stmt = $connection->prepare($update_old_payments);
    $update_stmt->bind_param("s", $student_id);
    $update_stmt->execute();
    
    // Create NEW unpaid payment record for the new semester
    $total_amount = 50000; // Default fee amount
    $amount_paid = 0;
    $due_amount = $total_amount;
    $payment_status = 'Unpaid';
    
    $insert_payment = "INSERT INTO payment (student_id, total_amount, amount_paid, due_amount, payment_status, is_latest, payment_date) 
                       VALUES (?, ?, ?, ?, ?, 1, NOW())";
    $insert_stmt = $connection->prepare($insert_payment);
    $insert_stmt->bind_param("sddds", $student_id, $total_amount, $amount_paid, $due_amount, $payment_status);
    $insert_stmt->execute();

    // ===== STEP 3: Update student's class and semester =====
    $update_sql = "UPDATE student SET class_id = ?, semester_id = ?, is_active = 1 WHERE student_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param("iis", $new_class_id, $next_semester, $student_id);

    if ($update_stmt->execute()) {
        $connection->commit();
        echo json_encode([
            'success' => true, 
            'message' => "Student promoted to Semester $next_semester. Payment reset to Unpaid. Removed from old class."
        ]);
    } else {
        throw new Exception('Update failed: ' . $update_stmt->error);
    }

} catch (Exception $e) {
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>