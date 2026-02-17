<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo 'Access denied';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$teacher_id = $_SESSION['teacher_id'];

if (!$class_id) {
    echo 'No class selected';
    exit();
}

// Get class data
$class_sql = "SELECT faculty, semester FROM class WHERE class_id = ?";
$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class = $class_stmt->get_result()->fetch_assoc();

// Get ALL subjects assigned to this teacher for this class
$subjects_sql = "SELECT s.subject_id, s.subject_name, s.subject_code
                FROM subject s
                INNER JOIN teacher_subject_assignment tsa ON s.subject_id = tsa.subject_id
                WHERE tsa.teacher_id = ? AND tsa.class_id = ?
                ORDER BY s.subject_name";
$subjects_stmt = $connection->prepare($subjects_sql);
$subjects_stmt->bind_param("ii", $teacher_id, $class_id);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();

$subject_list = [];
while($row = $subjects_result->fetch_assoc()) {
    $subject_list[] = $row;
}

// If no subjects assigned
if (empty($subject_list)) {
    echo '<div class="alert alert-warning">No subjects assigned to you for this class.</div>';
    exit();
}
?>

<!-- Class Info Header -->
<div class="mb-4">
    <h3><?= htmlspecialchars($class['faculty']) ?> - Semester <?= $class['semester'] ?></h3>
    <p class="text-muted">You have <?= count($subject_list) ?> subject(s) assigned</p>
</div>

<!-- Display EACH subject in its own card -->
<?php foreach ($subject_list as $subject): 
    $subject_id = $subject['subject_id'];
    
    // Get student marks for this subject
    $student_data = [];
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

<!-- Subject Card -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-book me-2"></i>
            <?= htmlspecialchars($subject['subject_name']) ?> 
            <small>(<?= htmlspecialchars($subject['subject_code']) ?>)</small>
        </h5>
    </div>
    <div class="card-body">
        
        <!-- Stats for this subject -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Class Average</h6>
                        <h3 class="mb-0 text-primary"><?= $avg ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Pass Rate</h6>
                        <h3 class="mb-0 text-success"><?= $pass_rate ?>%</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Highest Score</h6>
                        <h3 class="mb-0 text-warning"><?= $max ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Lowest Score</h6>
                        <h3 class="mb-0 text-danger"><?= $min ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart for this subject -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Marks Distribution - <?= htmlspecialchars($subject['subject_code']) ?></h6>
            </div>
            <div class="card-body">
                <div id="chart_<?= $subject_id ?>" style="height: 300px; width: 100%;"></div>
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
    </div>
</div>

<!-- Chart Script for this subject -->
<script>
(function() {
    const container = document.getElementById('chart_<?= $subject_id ?>');
    if (!container) return;
    
    const students = <?= json_encode($student_data) ?>;
    
    if (students.length === 0) {
        container.innerHTML = '<div class="text-center py-5 text-muted">No data available</div>';
        return;
    }
    
    const colors = students.map(s => {
        if (s.marks >= 80) return '#28a745';
        if (s.marks >= 60) return '#ffc107';
        if (s.marks >= 40) return '#fd7e14';
        if (s.marks > 0) return '#dc3545';
        return '#6c757d';
    });
    
    Highcharts.chart('chart_<?= $subject_id ?>', {
        chart: { type: 'column' },
        title: { text: null },
        xAxis: { 
            categories: students.map(s => s.name),
            labels: { rotation: -30, style: { fontSize: '11px' } }
        },
        yAxis: { min: 0, max: 100, title: { text: 'Marks' } },
        series: [{
            name: 'Marks',
            data: students.map(s => s.marks),
            colorByPoint: true,
            colors: colors
        }],
        credits: { enabled: false }
    });
})();
</script>

<?php endforeach; ?>

<!-- Load Highcharts once at the bottom -->


<!-- Debug -->
<script>
console.log('Highcharts loaded:', typeof Highcharts !== 'undefined');
console.log('Number of subjects:', <?= count($subject_list) ?>);
</script>