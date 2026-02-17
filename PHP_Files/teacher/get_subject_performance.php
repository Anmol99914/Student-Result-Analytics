<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo 'Access denied';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$teacher_id = $_SESSION['teacher_id'];

if (!$class_id || !$subject_id) {
    echo 'Invalid parameters';
    exit();
}

// Verify teacher has access to this subject
$check_sql = "SELECT id FROM teacher_subject_assignment 
              WHERE teacher_id = ? AND class_id = ? AND subject_id = ?";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("iii", $teacher_id, $class_id, $subject_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows == 0) {
    echo 'Access denied to this subject';
    exit();
}

// Get subject details
$subject_sql = "SELECT subject_name, subject_code FROM subject WHERE subject_id = ?";
$subject_stmt = $connection->prepare($subject_sql);
$subject_stmt->bind_param("i", $subject_id);
$subject_stmt->execute();
$subject = $subject_stmt->get_result()->fetch_assoc();

// Get student marks
$marks_sql = "SELECT 
                s.student_name,
                COALESCE(r.marks_obtained, 0) as marks
              FROM student s
              LEFT JOIN result r ON r.student_id = s.student_id 
                AND r.subject_id = ? 
                AND r.class_id = ?
              WHERE s.class_id = ? AND s.is_active = 1
              ORDER BY s.student_name";
$marks_stmt = $connection->prepare($marks_sql);
$marks_stmt->bind_param("iii", $subject_id, $class_id, $class_id);
$marks_stmt->execute();
$students = $marks_stmt->get_result();

$student_data = [];
while($student = $students->fetch_assoc()) {
    $student_data[] = [
        'name' => $student['student_name'],
        'marks' => (int)$student['marks']
    ];
}

// Calculate stats
$marks = array_column($student_data, 'marks');
$valid_marks = array_filter($marks, function($m) { return $m > 0; });
$avg = !empty($valid_marks) ? round(array_sum($valid_marks) / count($valid_marks), 1) : 0;
$pass = count(array_filter($valid_marks, function($m) { return $m >= 40; }));
$pass_rate = !empty($valid_marks) ? round(($pass / count($valid_marks)) * 100) : 0;
$max = !empty($valid_marks) ? max($valid_marks) : 0;
$min = !empty($valid_marks) ? min($valid_marks) : 0;
?>

<!-- Subject Info -->
<div class="mb-3">
    <h6 class="text-muted mb-1">Current Subject</h6>
    <h4 class="current-subject"><?= htmlspecialchars($subject['subject_name']) ?> 
        <small class="text-muted">(<?= htmlspecialchars($subject['subject_code']) ?>)</small>
    </h4>
</div>

<!-- Performance Stats -->
<div class="row g-3 mb-4 performance-stats">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Class Average</h6>
                <h3 class="mb-0 text-primary" id="avg-mark"><?= $avg ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Pass Rate</h6>
                <h3 class="mb-0 text-success" id="pass-rate"><?= $pass_rate ?>%</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Highest Score</h6>
                <h3 class="mb-0 text-warning" id="max-mark"><?= $max ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Lowest Score</h6>
                <h3 class="mb-0 text-danger" id="min-mark"><?= $min ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Chart Container -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Student Marks Distribution - <?= htmlspecialchars($subject['subject_code']) ?></h6>
    </div>
    <div class="card-body">
        <div id="chart" style="height: 400px; width: 100%;"></div>
    </div>
</div>

<!-- Legend -->
<div class="mt-3 d-flex flex-wrap gap-3">
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #28a745; border-radius: 2px;"></span> Excellent (80-100%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #ffc107; border-radius: 2px;"></span> Good (60-79%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #fd7e14; border-radius: 2px;"></span> Average (40-59%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #dc3545; border-radius: 2px;"></span> Needs Work (Below 40%)</span>
    <span><span class="d-inline-block" style="width: 12px; height: 12px; background: #6c757d; border-radius: 2px;"></span> Not Entered</span>
</div>

<script>
// PASS DATA TO JAVASCRIPT
    window.classData = {
        class: <?= json_encode([
            'faculty' => $class['faculty'],
            'semester' => $class['semester']
        ]) ?>,
        subject: {
            id: <?= $subject_id ?>,
            name: '<?= addslashes($subject['subject_name']) ?>',
            code: '<?= addslashes($subject['subject_code']) ?>'
        },
        students: <?= json_encode($student_data) ?>
    };
    console.log('AJAX loaded class data:', window.classData);

// Reinitialize the chart with new data
if (typeof Highcharts !== 'undefined') {
    const colors = <?= json_encode(array_map(function($s) {
        if ($s['marks'] >= 80) return '#28a745';
        if ($s['marks'] >= 60) return '#ffc107';
        if ($s['marks'] >= 40) return '#fd7e14';
        if ($s['marks'] > 0) return '#dc3545';
        return '#6c757d';
    }, $student_data)) ?>;

    Highcharts.chart('chart', {
        chart: { type: 'column' },
        title: { text: null },
        xAxis: { 
            categories: <?= json_encode(array_column($student_data, 'name')) ?>,
            labels: { rotation: -30, style: { fontSize: '12px' } }
        },
        yAxis: { min: 0, max: 100, title: { text: 'Marks' } },
        series: [{
            name: 'Marks',
            data: <?= json_encode(array_column($student_data, 'marks')) ?>,
            colorByPoint: true,
            colors: colors
        }],
        credits: { enabled: false }
    });
}
</script>