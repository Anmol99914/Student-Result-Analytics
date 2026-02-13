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

// Get the first subject assigned to this teacher for this class
$subjects_sql = "SELECT s.subject_id, s.subject_name, s.subject_code
                FROM subject s
                INNER JOIN teacher_subject_assignment tsa ON s.subject_id = tsa.subject_id
                WHERE tsa.teacher_id = ? AND tsa.class_id = ?
                LIMIT 1";
$subjects_stmt = $connection->prepare($subjects_sql);
$subjects_stmt->bind_param("ii", $teacher_id, $class_id);
$subjects_stmt->execute();
$subject = $subjects_stmt->get_result()->fetch_assoc();

$subject_id = $subject ? $subject['subject_id'] : 0;
$subject_name = $subject ? $subject['subject_name'] : '';
$subject_code = $subject ? $subject['subject_code'] : '';

// Get student marks
$student_data = [];
if ($subject_id) {
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
    
    while($student = $students->fetch_assoc()) {
        $student_data[] = [
            'name' => $student['student_name'],
            'marks' => (int)$student['marks']
        ];
    }
}
?>

<!-- PASS DATA TO JAVASCRIPT -->
<script>
    window.classData = {
        class: <?= json_encode([
            'faculty' => $class['faculty'],
            'semester' => $class['semester']
        ]) ?>,
        subject: {
            id: <?= $subject_id ?>,
            name: '<?= addslashes($subject_name) ?>',
            code: '<?= addslashes($subject_code) ?>'
        },
        students: <?= json_encode($student_data) ?>
    };
    console.log('Class data loaded:', window.classData);
</script>

<!-- Subject Info -->
<?php if ($subject): ?>
<div class="mb-3">
    <h6 class="text-muted mb-1">Subject</h6>
    <h5><?= htmlspecialchars($subject_name) ?> (<?= htmlspecialchars($subject_code) ?>)</h5>
</div>
<?php endif; ?>

<!-- Performance Stats -->
<?php if (!empty($student_data)): ?>
    <?php
    $marks = array_column($student_data, 'marks');
    $valid_marks = array_filter($marks, function($m) { return $m > 0; });
    $avg = !empty($valid_marks) ? round(array_sum($valid_marks) / count($valid_marks), 1) : 0;
    $pass = count(array_filter($valid_marks, function($m) { return $m >= 40; }));
    $pass_rate = !empty($valid_marks) ? round(($pass / count($valid_marks)) * 100) : 0;
    $max = !empty($valid_marks) ? max($valid_marks) : 0;
    $min = !empty($valid_marks) ? min($valid_marks) : 0;
    ?>
    
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
            <h6 class="mb-0">Student Marks Distribution</h6>
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
    
<?php else: ?>
    <div class="text-center py-5">
        <i class="bi bi-emoji-frown display-1 text-muted mb-3"></i>
        <h4 class="text-muted">No Students Found</h4>
        <p class="text-muted">This class has no active students.</p>
    </div>
<?php endif; ?>

<!-- Load Highcharts and performance.js -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="http://localhost/Student_Result_Analytics/js/teacher/performance.js"></script>