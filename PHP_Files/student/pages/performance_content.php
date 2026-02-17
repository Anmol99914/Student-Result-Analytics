<?php
require_once '../includes/auth_check.php';
require_student_login();
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];
$student_name = $_SESSION['student_name'];

// Get current payment status
$payment_sql = "SELECT payment_status FROM payment 
                WHERE student_id = ? AND is_latest = 1 
                LIMIT 1";
$payment_stmt = $connection->prepare($payment_sql);
$payment_stmt->bind_param("s", $student_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payment = $payment_result->fetch_assoc();
$payment_status = $payment['payment_status'] ?? 'Unpaid';

// If unpaid, show restricted message
if ($payment_status !== 'Paid') {
    echo '<div class="alert alert-warning text-center py-5">
            <i class="bi bi-lock display-1 text-warning"></i>
            <h4 class="mt-3">Performance Charts Locked</h4>
            <p class="text-muted">Complete your fee payment to access performance charts.</p>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            <a href="#" class="btn btn-outline-primary ms-2">Go to Payments</a>
          </div>';
    exit();
}

// Get student data
$stmt = $connection->prepare("
    SELECT s.student_id, s.student_name, 
           CONCAT(c.faculty, ' Semester ', c.semester) as class_display,
           c.semester as current_semester
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Get semester data
$semester_sql = "SELECT 
                    sem.semester_name,
                    AVG(r.percentage) as avg_percentage
                FROM result r
                JOIN semester sem ON r.semester_id = sem.semester_id
                WHERE r.student_id = ? AND r.verification_status = 'verified'
                GROUP BY r.semester_id
                ORDER BY r.semester_id";
$semester_stmt = $connection->prepare($semester_sql);
$semester_stmt->bind_param("s", $student_id);
$semester_stmt->execute();
$semester_result = $semester_stmt->get_result();

$semester_data = [];
while($row = $semester_result->fetch_assoc()) {
    $semester_data[] = [
        'name' => $row['semester_name'],
        'percentage' => round($row['avg_percentage'], 1)
    ];
}

// Get subject data for current semester
$current_semester = $student['current_semester'] ?? 1;
$subject_sql = "SELECT 
                    s.subject_name,
                    r.percentage
                FROM result r
                JOIN subject s ON r.subject_id = s.subject_id
                WHERE r.student_id = ? AND r.semester_id = ? AND r.verification_status = 'verified'
                ORDER BY s.subject_name";
$subject_stmt = $connection->prepare($subject_sql);
$subject_stmt->bind_param("si", $student_id, $current_semester);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();

$subject_data = [];
while($row = $subject_result->fetch_assoc()) {
    $subject_data[] = [
        'name' => $row['subject_name'],
        'percentage' => round($row['percentage'], 1)
    ];
}
?>

<script>
    window.studentData = {
        semester: <?= json_encode($semester_data) ?>,
        subject: <?= json_encode($subject_data) ?>
    };
    console.log('Student data loaded:', window.studentData);
</script>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Overall Average</h6>
                <h3 class="mb-0 text-primary" id="avg-mark">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Pass Rate</h6>
                <h3 class="mb-0 text-success" id="pass-rate">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Highest Score</h6>
                <h3 class="mb-0 text-warning" id="max-mark">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Lowest Score</h6>
                <h3 class="mb-0 text-danger" id="min-mark">-</h3>
            </div>
        </div>
    </div>
</div>

<!-- View Toggle - Like teacher version -->
<div class="text-center mb-4">
    <div class="btn-group" role="group">
        <button class="btn btn-outline-primary active" data-view="semester">By Semester</button>
        <button class="btn btn-outline-primary" data-view="subject">By Subject</button>
    </div>
</div>

<!-- Chart Container -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0" id="chartTitle">Performance Distribution</h6>
    </div>
    <div class="card-body">
        <div id="performanceChart" style="height: 400px; width: 100%;"></div>
    </div>
</div>

<!-- Legend - Like teacher version -->
<div class="mt-3 d-flex flex-wrap gap-3">
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #28a745; border-radius: 2px;"></span> Excellent (80-100%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #ffc107; border-radius: 2px;"></span> Good (60-79%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #fd7e14; border-radius: 2px;"></span> Average (40-59%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #dc3545; border-radius: 2px;"></span> Needs Work (Below 40%)</span>
</div>

<!-- Load Highcharts and student-performance.js -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="/Student_Result_Analytics/js/student/student-performance.js"></script>