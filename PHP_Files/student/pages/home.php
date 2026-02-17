<?php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];
$student_name = $_SESSION['student_name'];

// Fetch student data
$stmt = $connection->prepare("
    SELECT s.student_id, s.student_name, s.email, s.phone_number, 
       s.admission_year, s.is_active, 
       CONCAT(c.faculty, ' Semester ', c.semester) as class_display,
       sem.semester_name
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    LEFT JOIN semester sem ON s.semester_id = sem.semester_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Count results
$result_stmt = $connection->prepare("
    SELECT COUNT(*) as total_results 
    FROM result 
    WHERE student_id = ? AND verification_status = 'verified'
");
$result_stmt->bind_param("s", $student_id);
$result_stmt->execute();
$result_count = $result_stmt->get_result()->fetch_assoc()['total_results'];

// Get total paid across ALL semesters
// $total_paid_query = "SELECT SUM(p.amount_paid) as total_paid_all 
//                      FROM payment p
//                      INNER JOIN (
//                          SELECT student_id, MAX(payment_id) as max_id
//                          FROM payment
//                          WHERE payment_status = 'Paid'
//                          GROUP BY student_id
//                      ) latest ON p.payment_id = latest.max_id
//                      WHERE p.student_id = ?";
// $total_paid_stmt = $connection->prepare($total_paid_query);
// $total_paid_stmt->bind_param("s", $student_id);
// $total_paid_stmt->execute();
// $total_paid_result = $total_paid_stmt->get_result();
// $total_paid_data = $total_paid_result->fetch_assoc();
// $total_paid_all = $total_paid_data['total_paid_all'] ?? 0;

// Get total paid across ALL semesters - MAX amount per semester
$total_paid_query = "SELECT SUM(semester_paid) as total_paid_all
                     FROM (
                         SELECT semester, MAX(amount_paid) as semester_paid
                         FROM payment
                         WHERE student_id = ?
                         GROUP BY semester
                     ) as per_semester";
$total_paid_stmt = $connection->prepare($total_paid_query);
$total_paid_stmt->bind_param("s", $student_id);
$total_paid_stmt->execute();
$total_paid_result = $total_paid_stmt->get_result();
$total_paid_data = $total_paid_result->fetch_assoc();
$total_paid_all = $total_paid_data['total_paid_all'] ?? 0;

// Get current payment status (latest)
$payment_stmt = $connection->prepare("
    SELECT amount_paid as total_paid, 
           payment_date as last_payment,
           payment_status,
           due_amount
    FROM payment 
    WHERE student_id = ? AND is_latest = 1
    LIMIT 1
");
$payment_stmt->bind_param("s", $student_id);
$payment_stmt->execute();
$payment = $payment_stmt->get_result()->fetch_assoc();

if (!$payment) {
    $payment = [
        'total_paid' => 0,
        'last_payment' => null,
        'payment_status' => 'Unpaid',
        'due_amount' => 50000
    ];
}
?>

<div class="container-fluid">
    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="card-title mb-2">
                                <i class="bi bi-emoji-smile me-2"></i>
                                Welcome back, <?php echo htmlspecialchars($student_name); ?>!
                            </h2>
                            <p class="card-text mb-0 opacity-75">
                                Track your academic performance and manage your student profile.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="bg-white bg-opacity-25 d-inline-block p-3 rounded-3">
                                <div class="h4 mb-0"><?php echo htmlspecialchars($student['class_display']); ?></div>
                                <small>Current Class</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Published Results</h6>
                        <h2 class="mb-0"><?php echo $result_count; ?></h2>
                    </div>
                    <div class="avatar bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-clipboard-data text-primary fs-4"></i>
                    </div>
                </div>
                <a href="results.php" class="small text-primary text-decoration-none stretched-link" onclick="loadPage(this.href); return false;">
                    View Results <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>


    <div class="col-md-3 col-sm-6 mb-3">
    <div class="card border-success h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <!-- <h6 class="text-muted mb-2">Total Paid (All Time)</h6>
                    <h2 class="mb-0">NPR <?php echo number_format($total_paid_all, 2); ?></h2> -->
                    <small class="text-muted">Current Status: 
                        <span class="badge bg-<?php 
                            echo $payment['payment_status'] == 'Paid' ? 'success' : 
                                ($payment['payment_status'] == 'Partial' ? 'warning' : 'danger'); 
                        ?>">
                            <?php echo $payment['payment_status'] ?? 'Unpaid'; ?>
                        </span>
                    </small>                        
                </div>
                <div class="avatar bg-success bg-opacity-10 p-3 rounded">
                    <i class="bi bi-credit-card text-success fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Current Semester</h6>
                            <h2 class="mb-0"><?php echo htmlspecialchars($student['semester_name']); ?></h2>
                        </div>
                        <div class="avatar bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-journal-text text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="small text-muted">Academic Session 2024/25</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Account Status</h6>
                            <h2 class="mb-0">
                                <span class="badge bg-<?php echo ($student['is_active'] == 1) ? 'success' : 'danger'; ?>">
                                    <?php echo ($student['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </h2>
                        </div>
                        <div class="avatar bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-shield-check text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="small text-muted">Since <?php echo $student['admission_year']; ?></div>
                </div>
            </div>
        </div>
    </div>

<!-- View Performance Charts Button -->
<div class="col-12 mt-3">
    <div class="row g-3">
        <div class="col-md-4 offset-md-4">
            <a href="performance.php" 
               class="btn btn-primary w-100 d-flex flex-column align-items-center py-3">
                <i class="bi bi-bar-chart-fill fs-2 mb-2"></i>
                <span>View Detailed Performance Charts</span>
            </a>
        </div>
    </div>
</div>
<br><br><br><br><br><br>
</div>