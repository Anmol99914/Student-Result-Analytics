<?php
// File: PHP_Files/student/pages/home.php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];
$student_name = $_SESSION['student_name'];

// Fetch student data - CORRECTED QUERY (no address column)
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
    WHERE student_id = ? AND status = 'published'
");
$result_stmt->bind_param("s", $student_id);
$result_stmt->execute();
$result_count = $result_stmt->get_result()->fetch_assoc()['total_results'];

// Check payment status
// Get CURRENT payment status (only latest record)
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

// If no payment record exists, set defaults
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
                    <a href="../pages/results.php" class="small text-primary text-decoration-none stretched-link">
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
                            <h6 class="text-muted mb-2">Total Paid</h6>
                            <h2 class="mb-0">NPR <?php echo number_format($payment['total_paid'] ?? 0); ?></h2>
                                <small class="text-muted">Status: 
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
                    <!-- <a href="../pages/payments.php" class="small text-success text-decoration-none stretched-link">
                        Payment Details <i class="bi bi-arrow-right"></i>
                    </a> -->
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
    
    <!-- Recent Activity & Quick Actions -->
    <div class="row">
        <!-- Recent Results -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Recent Results
                    </h5>
                    <a href="../pages/results.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php
                    // Fetch recent results
                    $recent_stmt = $connection->prepare("
                        SELECT r.result_id, r.total_marks, r.marks_obtained, 
                               r.grade, r.published_date, sub.subject_name
                        FROM result r
                        JOIN subject sub ON r.subject_id = sub.subject_id
                        WHERE r.student_id = ? AND r.status = 'published'
                        ORDER BY r.published_date DESC 
                        LIMIT 5
                    ");
                    $recent_stmt->bind_param("s", $student_id);
                    $recent_stmt->execute();
                    $recent_results = $recent_stmt->get_result();
                    
                    if ($recent_results->num_rows > 0): 
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recent_results->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td>
                                        <span class="badge bg-info">Regular</span>
                                    </td>
                                    <td>
                                        <strong><?php echo $row['marks_obtained']; ?></strong>
                                        <small class="text-muted">/<?php echo $row['total_marks']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($row['grade']) {
                                                case 'A': echo 'success'; break;
                                                case 'B': echo 'primary'; break;
                                                case 'C': echo 'warning'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo $row['grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['published_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-x display-5 text-muted"></i>
                        <p class="mt-3 text-muted">No results published yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-charge text-warning me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../pages/results.php" class="btn btn-outline-primary text-start">
                            <i class="bi bi-clipboard-data me-2"></i> View All Results
                        </a>
                        <a href="../pages/profile.php" class="btn btn-outline-success text-start">
                            <i class="bi bi-person-circle me-2"></i> Update Profile
                        </a>
                        <a href="../pages/payments.php" class="btn btn-outline-info text-start">
                            <i class="bi bi-credit-card me-2"></i> Make Payment
                        </a>
                        <a href="#" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-download me-2"></i> Download Report
                        </a>
                    </div>
                    
                    <!-- Notice Board -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">
                            <i class="bi bi-megaphone text-danger me-2"></i>
                            Notices
                        </h6>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item px-0 border-0">
                                <small class="text-primary">Today</small>
                                <p class="mb-1 small">Final exam schedule has been updated.</p>
                            </div>
                            <div class="list-group-item px-0 border-0">
                                <small class="text-primary">2 days ago</small>
                                <p class="mb-1 small">Last date for fee submission: 25th Dec 2024</p>
                            </div>
                            <div class="list-group-item px-0 border-0">
                                <small class="text-primary">1 week ago</small>
                                <p class="mb-1 small">Semester 3 results will be published next week.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Deadlines -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check text-success me-2"></i>
                        Important Dates
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="text-primary fw-bold fs-4">15 Dec</div>
                                <small class="text-muted">Fee Payment Deadline</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="text-primary fw-bold fs-4">20 Dec</div>
                                <small class="text-muted">Semester Exams Start</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="text-primary fw-bold fs-4">05 Jan</div>
                                <small class="text-muted">Result Publication</small>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="text-primary fw-bold fs-4">10 Jan</div>
                                <small class="text-muted">Next Semester Starts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>