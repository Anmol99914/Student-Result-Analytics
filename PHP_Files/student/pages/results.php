<?php
// File: PHP_Files/student/pages/results.php
require_once '../includes/auth_check.php';
require_student_login();
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];
$student_name = $_SESSION['student_name'];

// Get student's current semester
$student_sql = "SELECT c.semester as current_semester FROM student s 
                LEFT JOIN class c ON s.class_id = c.class_id 
                WHERE s.student_id = ?";
$student_stmt = $connection->prepare($student_sql);
$student_stmt->bind_param("s", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_data = $student_result->fetch_assoc();
$current_semester = $student_data['current_semester'] ?? 1;

// Check payment status
$payment_check = checkPaymentStatus($student_id);

$total_paid_query = "SELECT SUM(semester_max.max_paid) as total_paid_all
                     FROM (
                         SELECT MAX(amount_paid) as max_paid
                         FROM payment
                         WHERE student_id = ?
                         GROUP BY semester
                     ) as semester_max";
$total_paid_stmt = $connection->prepare($total_paid_query);
$total_paid_stmt->bind_param("s", $student_id);
$total_paid_stmt->execute();
$total_paid_result = $total_paid_stmt->get_result();
$total_paid_data = $total_paid_result->fetch_assoc();
$total_paid_all = $total_paid_data['total_paid_all'] ?? 0;

// Get semesters and results
$semesters = getStudentSemesters($student_id);
$selected_semester = $_GET['semester'] ?? $_SESSION['student_semester'] ?? 1;
$results = getStudentResults($student_id, $selected_semester);
$stats = calculateResultStats($results);

// Helper Functions
// function checkPaymentStatus($student_id) {
//     global $connection;
//     $stmt = $connection->prepare("SELECT payment_status, amount_paid, total_amount FROM payment WHERE student_id = ? ORDER BY payment_date DESC LIMIT 1");
//     $stmt->bind_param("s", $student_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_assoc() ?: ['payment_status' => 'Unpaid', 'amount_paid' => 0, 'total_amount' => 0];
// }

function checkPaymentStatus($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT payment_status, amount_paid, total_amount FROM payment WHERE student_id = ? AND is_latest = 1 ORDER BY payment_date DESC LIMIT 1");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    
    if (!$payment) {
        return ['payment_status' => 'Unpaid', 'amount_paid' => 0, 'total_amount' => 50000];
    }
    
    return $payment;
}

function getStudentSemesters($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT DISTINCT r.semester_id, s.semester_name FROM result r JOIN semester s ON r.semester_id = s.semester_id WHERE r.student_id = ? AND r.verification_status = 'verified' ORDER BY r.semester_id");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}
function getStudentResults($student_id, $semester_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT r.marks_obtained, r.total_marks, r.percentage, r.grade, r.published_date, s.subject_name, s.subject_code, s.credits FROM result r JOIN subject s ON r.subject_id = s.subject_id WHERE r.student_id = ? AND r.semester_id = ? AND r.verification_status IN ('verified') ORDER BY s.subject_code");
    $stmt->bind_param("si", $student_id, $semester_id);
    $stmt->execute();
    return $stmt->get_result();
}
function calculateResultStats($results) {
    $stats = ['total_credits' => 0, 'earned_credits' => 0, 'total_grade_points' => 0, 'total_subjects' => 0, 'passed_subjects' => 0, 'failed_subjects' => 0];
    $grade_points = ['A+' => 4.0, 'A' => 3.7, 'B+' => 3.3, 'B' => 3.0, 'C+' => 2.7, 'C' => 2.3, 'F' => 0.0];
    
    if ($results->num_rows > 0) {
        while($row = $results->fetch_assoc()) {
            $stats['total_subjects']++;
            $stats['total_credits'] += $row['credits'];
            if ($row['grade'] !== 'F') {
                $stats['passed_subjects']++;
                $stats['earned_credits'] += $row['credits'];
                $stats['total_grade_points'] += ($grade_points[$row['grade']] ?? 0) * $row['credits'];
            } else {
                $stats['failed_subjects']++;
            }
        }
        $results->data_seek(0);
    }
    $stats['gpa'] = $stats['earned_credits'] > 0 ? round($stats['total_grade_points'] / $stats['earned_credits'], 2) : 0;
    return $stats;
}
?>

<style>
@media print {
    /* Hide all dashboard elements */
    .navbar, .sidebar, footer, .btn, .alert,
    .col-lg-2, .col-lg-3, .row.g-3.p-3,
    .card-header.bg-info, .bg-primary, .bg-info, .border-info,
    .col-lg-9 .card, .row.mb-4.align-items-center,
    .h6.text-muted, [class*="filter"], .bi-filter, .fa-filter,
    .card-header.bg-white .btn, .d-flex.gap-2,
    .badge.fs-6.px-3.py-2, .card.bg-primary.text-white.shadow,
    .row.g-3.p-3 {
        display: none !important;
    }

    /* Show student info row */
    .row.mb-4:first-of-type {
        display: block !important;
        visibility: visible !important;
    }

    /* Container full width */
    .container-fluid,
    .col-lg-10,
    .col-md-9 {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0.2in !important;
        background: white !important;
        overflow: visible !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    /* SINGLE HEADING */
    .card.shadow-sm::before {
        content: "ACADEMIC RESULT SHEET";
        display: block;
        text-align: center;
        font-size: 26px;
        font-weight: bold;
        color: #0073e6;
        margin: 0 0 15px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #0073e6;
        width: 100%;
    }

    .card-header.bg-white {
        display: none !important;
    }

    /* STUDENT DETAILS */
    .student-info-box {
        border: 1px solid #0073e6 !important;
        padding: 12px 15px !important;
        background: #f4f8ff !important;
        margin-bottom: 20px !important;
        border-radius: 5px !important;
        font-size: 15px !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    .student-name {
        font-weight: 600 !important;
        color: #0073e6 !important;
    }

    .student-id {
        font-weight: 500 !important;
        color: #333 !important;
    }

    /* Exam title */
    .exam-title {
        text-align: center !important;
        font-size: 18px !important;
        font-weight: bold !important;
        color: #0073e6 !important;
        margin: 10px 0 15px 0 !important;
        padding: 8px !important;
        background: #f0f5ff !important;
        border-radius: 4px !important;
    }

    /* TABLE */
    table.table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 13px !important;
        margin: 10px 0 !important;
        table-layout: fixed !important;
    }

    table.table th:nth-child(1) { width: 4% !important; }
    table.table th:nth-child(2) { width: 10% !important; }
    table.table th:nth-child(3) { width: 30% !important; }
    table.table th:nth-child(4) { width: 6% !important; }
    table.table th:nth-child(5) { width: 7% !important; }
    table.table th:nth-child(6) { width: 7% !important; }
    table.table th:nth-child(7) { width: 10% !important; }
    table.table th:nth-child(8) { width: 11% !important; }
    table.table th:nth-child(9) { width: 15% !important; }

    thead {
        display: table-header-group;
    }

    table.table th {
        border: 1px solid #0073e6 !important;
        background: #0073e6 !important;
        color: white !important;
        padding: 8px 4px !important;
        font-weight: 600;
        text-align: center;
        font-size: 12px !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    table.table td {
        border: 1px solid #888 !important;
        padding: 6px 4px !important;
        color: black !important;
        background: white !important;
        font-size: 12px !important;
        word-wrap: break-word !important;
    }

    td code {
        color: #0073e6 !important;
        font-weight: bold;
        font-size: 12px;
    }

    .text-primary {
        color: #0073e6 !important;
        font-weight: bold !important;
    }

    .badge.bg-success,
    .badge.bg-warning,
    .badge.bg-info,
    .badge.bg-danger {
        background: none !important;
        color: black !important;
        border: none !important;
        font-weight: bold !important;
        font-size: 12px !important;
        padding: 0 !important;
    }

    .badge.fs-6 {
        background: #0073e6 !important;
        color: white !important;
        border-radius: 12px !important;
        padding: 3px 8px !important;
        font-weight: bold !important;
        font-size: 11px !important;
        display: inline-block !important;
        min-width: 35px !important;
        text-align: center !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .badge.bg-success:last-child,
    .badge.bg-danger:last-child {
        background: none !important;
        font-weight: bold !important;
        font-size: 12px !important;
        padding: 0 !important;
    }

    .badge.bg-success:last-child {
        color: #28a745 !important;
    }

    .badge.bg-danger:last-child {
        color: #dc3545 !important;
    }

    tfoot td {
        border: 1px solid #0073e6 !important;
        font-weight: bold;
        background: #f4f8ff !important;
        color: #0073e6 !important;
        padding: 8px !important;
        font-size: 12px !important;
    }

    .result-summary {
        margin-top: 15px !important;
        padding: 10px !important;
        text-align: center !important;
        border: 1px solid #0073e6 !important;
        color: #0073e6 !important;
        font-weight: bold !important;
        background: #f4f8ff !important;
        font-size: 13px !important;
        border-radius: 4px !important;
    }

    @page {
        size: A4;
        margin: 0.3in;
    }

    table {
        page-break-inside: avoid !important;
    }
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Academic Results</h4>
                        <small class="opacity-75">View your semester-wise results</small>
                    </div>
                    <span class="badge bg-<?php echo $payment_check['payment_status'] === 'Paid' ? 'success' : 'warning'; ?> fs-6 px-3 py-2">
                        <i class="bi bi-<?php echo $payment_check['payment_status'] === 'Paid' ? 'check-circle' : 'exclamation-triangle'; ?> me-1"></i>
                        <?php echo $payment_check['payment_status']; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Warning -->
        <?php if($payment_check['payment_status'] !== 'Paid'): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-2">Fee Payment Required for <?php echo "Semester " . $current_semester; ?></h5>
                            <p class="mb-2">Your results for the current semester are restricted due to pending fees.</p>
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div>
                                    <small class="text-muted">Paid for Current Sem:</small>
                                    <div class="h5 mb-0">NPR <?php echo number_format($payment_check['amount_paid'], 2); ?></div>
                                </div>
                                <div>
                                    <small class="text-muted">Total for Current Sem:</small>
                                    <div class="h5 mb-0">NPR <?php echo number_format($payment_check['total_amount'], 2); ?></div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    
    <!-- Semester Filter Row -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-filter me-2"></i>Select Semester</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        // Get all semesters 1-8
                        $all_semesters = [];
                        for($i = 1; $i <= 8; $i++) {
                            $all_semesters[] = [
                                'semester_id' => $i,
                                'semester_name' => 'Semester ' . $i
                            ];
                        }
                        
                        // Get semesters that have results
                        $semesters_with_results = [];
                        $semesters->data_seek(0);
                        while($sem = $semesters->fetch_assoc()) {
                            $semesters_with_results[] = $sem['semester_id'];
                        }
                        
                        foreach($all_semesters as $sem): 
                            $has_results = in_array($sem['semester_id'], $semesters_with_results);
                            $active = ($selected_semester == $sem['semester_id']) ? 'active' : '';
                            $disabled = !$has_results ? 'disabled' : '';
                        ?>
                            <button class="btn <?php echo $has_results ? 'btn-outline-primary' : 'btn-outline-secondary'; ?> semester-filter <?php echo $active; ?>"
                                    onclick="<?php echo $has_results ? 'loadPage(\'results.php?semester=' . $sem['semester_id'] . '\'); return false;' : 'alert(\'No results published for ' . $sem['semester_name'] . '\');'; ?>"
                                    <?php echo $disabled; ?>>
                                <?php echo $sem['semester_name']; ?>
                                <?php if($has_results): ?>
                                    <span class="badge bg-success ms-1">✓</span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <span class="badge bg-success me-1">✓</span> Results available
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card bg-primary text-white shadow">
                <div class="card-body text-center py-3">
                    <h6 class="text-white-50 mb-1 small text-uppercase tracking-wide">Current GPA</h6>
                    <div class="display-5 fw-bold mb-0"><?php echo number_format($stats['gpa'], 2); ?></div>
                    <small class="opacity-75">out of 4.0</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="bi bi-list-check text-primary me-2"></i>Result Details - Semester <?php echo $selected_semester; ?></h5>
                    <div class="d-flex gap-2">
                        <button onclick="window.printResults(); return false;" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-printer me-1"></i> Print Results
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php 
                    $is_current_semester = ($selected_semester == $current_semester);
                    $can_view = (!$is_current_semester) || ($payment_check['payment_status'] === 'Paid');
                    
                    if ($can_view): 
                        if($results->num_rows > 0): 
                    ?>
                    
                    <!-- STUDENT DETAILS -->
                    <div class="student-info-box" style="display: none;">
                        <span class="student-name"><?php echo $student_name; ?></span>
                        <span class="student-id">ID: <?php echo $student_id; ?></span>
                    </div>
                    
                    <!-- EXAM TITLE -->
                    <div class="exam-title" style="display: none;">
                        SEMESTER <?php echo $selected_semester; ?> EXAMINATION RESULT
                    </div>
                    
                    <!-- RESULTS TABLE -->
                    <div class="table-responsive" style="overflow: visible;">
                        <table class="table table-hover table-bordered mb-0" style="table-layout: fixed;">
                            <thead class="table-light">
                                <tr>
                                    <th width="25" class="text-center">#</th>
                                    <th width="70">Code</th>
                                    <th width="260">Subject</th>
                                    <th width="45" class="text-center">Cr</th>
                                    <th width="60" class="text-center">Obtained</th>
                                    <th width="60" class="text-center">Total</th>
                                    <th width="70" class="text-center">%</th>
                                    <th width="80" class="text-center">Grade</th>
                                    <th width="110" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $counter = 1; while($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center"><?php echo $counter++; ?></td>
                                    <td><code><?php echo htmlspecialchars($row['subject_code']); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                    <td class="text-center"><?php echo $row['credits']; ?></td>
                                    <td class="text-center fw-bold text-primary"><?php echo $row['marks_obtained']; ?></td>
                                    <td class="text-center"><?php echo $row['total_marks']; ?></td>
                                    <td class="text-center">
                                        <?php echo number_format($row['percentage'], 1); ?>%
                                    </td>
                                    <td class="text-center">
                                        <span class="badge fs-6" style="background: #0073e6; color: white; padding: 3px 8px; border-radius: 12px;">
                                            <?php echo $row['grade']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center" style="color: <?php echo $row['grade'] === 'F' ? '#dc3545' : '#28a745'; ?>; font-weight: bold;">
                                        <?php echo $row['grade'] === 'F' ? 'Failed' : 'Passed'; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Summary:</td>
                                    <td class="text-center fw-bold"><?php echo $stats['total_credits']; ?></td>
                                    <td colspan="2" class="text-center">
                                        <span style="color: #28a745; font-weight: bold;"><?php echo $stats['passed_subjects']; ?> Passed</span>
                                        <?php if($stats['failed_subjects'] > 0): ?>
                                            | <span style="color: #dc3545; font-weight: bold;"><?php echo $stats['failed_subjects']; ?> Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold" style="color: #0073e6;">GPA: <?php echo number_format($stats['gpa'], 2); ?></td>
                                    <td colspan="2" class="text-center">
                                        <small>Credits: <?php echo $stats['earned_credits']; ?>/<?php echo $stats['total_credits']; ?></small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Summary Cards -->
                    <div class="row g-3 p-3">
                        <!-- GPA Card -->
                        <div class="col-md-3 col-6">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted small text-uppercase mb-2">GPA</h6>
                                    <div class="display-6 fw-bold text-success"><?php echo number_format($stats['gpa'], 2); ?></div>
                                    <small class="text-muted">/ 4.0</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pass Rate Card -->
                        <div class="col-md-3 col-6">
                            <div class="card border-primary h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted small text-uppercase mb-2">Pass Rate</h6>
                                    <div class="display-6 fw-bold text-primary">
                                        <?php echo $stats['total_subjects'] > 0 ? round(($stats['passed_subjects']/$stats['total_subjects'])*100) : 0; ?>%
                                    </div>
                                    <small class="text-muted"><?php echo $stats['passed_subjects']; ?>/<?php echo $stats['total_subjects']; ?> subjects</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Credits Card -->
                        <div class="col-md-3 col-6">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted small text-uppercase mb-2">Credits Earned</h6>
                                    <div class="display-6 fw-bold text-info"><?php echo $stats['earned_credits']; ?></div>
                                    <small class="text-muted">of <?php echo $stats['total_credits']; ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Card -->
                        <div class="col-md-3 col-6">
                            <div class="card border-<?php echo $stats['failed_subjects'] > 0 ? 'warning' : 'success'; ?> h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted small text-uppercase mb-2">Status</h6>
                                    <?php 
                                    $status_text = $stats['failed_subjects'] > 0 ? 'Needs Work' : 
                                                  ($stats['gpa'] >= 3.5 ? 'Excellent' : 
                                                  ($stats['gpa'] >= 3.0 ? 'Good' : 
                                                  ($stats['gpa'] >= 2.0 ? 'Satisfactory' : 'Needs Work')));
                                    $status_color = $stats['failed_subjects'] > 0 ? 'warning' : 
                                                   ($stats['gpa'] >= 3.5 ? 'success' : 
                                                   ($stats['gpa'] >= 3.0 ? 'info' : 
                                                   ($stats['gpa'] >= 2.0 ? 'primary' : 'secondary')));
                                    ?>
                                    <div class="display-6 fw-bold text-<?php echo $status_color; ?>"><?php echo $status_text; ?></div>
                                    <?php if($stats['failed_subjects'] > 0): ?>
                                        <small class="text-muted"><?php echo $stats['failed_subjects']; ?> subject<?php echo $stats['failed_subjects'] != 1 ? 's' : ''; ?> to improve</small>
                                    <?php else: ?>
                                        <small class="text-muted">All subjects passed</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- RESULT SUMMARY LINE -->
                    <?php
                    $total_obtained = 0;
                    $total_max = 0;
                    $results->data_seek(0);
                    while($row = $results->fetch_assoc()) {
                        $total_obtained += $row['marks_obtained'];
                        $total_max += $row['total_marks'];
                    }
                    $results->data_seek(0);
                    $percentage = $total_max > 0 ? round(($total_obtained / $total_max) * 100, 1) : 0;
                    $result_text = $stats['failed_subjects'] > 0 ? 'FAIL' : 'PASS';
                    
                    if ($percentage >= 80) $division = 'First Division';
                    elseif ($percentage >= 60) $division = 'Second Division';
                    elseif ($percentage >= 40) $division = 'Third Division';
                    else $division = 'Fail';
                    ?>
                    <div class="result-summary" style="display: none;">
                        TOTAL: <?php echo $total_obtained; ?>/<?php echo $total_max; ?> (<?php echo $percentage; ?>%) | 
                        RESULT: <?php echo $result_text; ?> | <?php echo $division; ?>
                    </div>
                    
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clipboard-x display-1 text-muted mb-3"></i>
                        <h4>No Results Found</h4>
                        <p class="text-muted">No published results available for this semester.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-lock display-1 text-warning mb-3"></i>
                        <h4>Results Locked</h4>
                        <p class="text-muted">This is your current semester. Complete fee payment to view results.</p>
                        <a href="#" class="btn btn-primary btn-lg mt-3">
                            <i class="bi bi-credit-card me-2"></i>Go to Payments
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Make print elements visible only in print -->
<style>
    @media print {
        .student-info-box {
            display: flex !important;
        }
        .exam-title {
            display: block !important;
        }
        .result-summary {
            display: block !important;
        }
    }
</style>

<style>
/* SCREEN VIEW - Make table adjustable and readable */
@media screen {
    .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
    }
    
    table.table {
        width: 100% !important;
        font-size: 14px !important;
    }
    
    table.table th,
    table.table td {
        white-space: nowrap !important;
        padding: 12px 8px !important;
    }
    
    table.table td code {
        font-size: 13px !important;
    }
    
    table.table td .text-primary {
        font-size: 14px !important;
    }
    
    table.table td .badge.fs-6 {
        padding: 5px 12px !important;
        font-size: 13px !important;
    }
    
    table.table td:last-child {
        font-weight: 600 !important;
    }
    
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin: 15px 0;
    }
}

.semester-filter {
    min-width: 100px;
    transition: all 0.3s;
}

.semester-filter.active {
    background-color: #0073e6 !important;
    color: white !important;
    border-color: #0073e6 !important;
}

.semester-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,115,230,0.2);
}

.semester-filter:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: all !important;
}

.semester-filter:disabled:hover {
    background-color: transparent;
    color: #6c757d;
    transform: none;
    box-shadow: none;
}
</style>