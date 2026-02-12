<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    exit();
}

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) exit();

// Get student details with class and payment info
$query = "SELECT s.*, c.faculty, c.semester, 
                 p.payment_status, p.due_amount, p.payment_date
          FROM student s
          LEFT JOIN class c ON s.class_id = c.class_id
          LEFT JOIN payment p ON s.student_id = p.student_id 
          WHERE s.student_id = ?
          ORDER BY p.payment_id DESC LIMIT 1";

$stmt = $connection->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<div class="modal fade" id="viewStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge"></i> Student Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Student ID</th>
                                <td><strong><?= $student['student_id'] ?></strong></td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td><?= htmlspecialchars($student['student_name']) ?></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?= $student['phone_number'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-<?= $student['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $student['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Faculty</th>
                                <td><?= $student['faculty'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Semester</th>
                                <td><?= $student['semester'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Admission Year</th>
                                <td><?= $student['admission_year'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Batch Code</th>
                                <td><?= $student['batch_code'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td><?= date('d M Y', strtotime($student['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <!-- Payment Information -->
                <h6 class="mb-3">Payment Information</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Payment Status</h6>
                                <h5>
                                    <span class="badge bg-<?php 
                                        $status = strtolower($student['payment_status'] ?? 'unpaid');
                                        echo $status == 'paid' ? 'success' : ($status == 'partial' ? 'warning' : 'danger');
                                    ?>">
                                        <?= $student['payment_status'] ?? 'Unpaid' ?>
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Due Amount</h6>
                                <h5 class="text-danger">Rs. <?= number_format($student['due_amount'] ?? 0, 2) ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6>Last Payment</h6>
                                <h6><?= $student['payment_date'] ? date('d M Y', strtotime($student['payment_date'])) : 'Never' ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="window.studentManager.editStudent('<?= $student_id ?>')">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button type="button" class="btn btn-success" onclick="window.studentManager.togglePaymentStatus('<?= $student_id ?>')">
                    <i class="bi bi-cash"></i> Toggle Payment
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>