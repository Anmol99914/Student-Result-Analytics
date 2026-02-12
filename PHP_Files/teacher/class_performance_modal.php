<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$teacher_id = $_SESSION['teacher_id'];

if (!$class_id) {
    echo '<div class="alert alert-warning">No class selected</div>';
    exit();
}

// Get class details
$class_sql = "SELECT faculty, semester FROM class WHERE class_id = ?";
$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class = $class_stmt->get_result()->fetch_assoc();

// Get total students
$student_sql = "SELECT COUNT(*) as total FROM student WHERE class_id = ? AND is_active = 1";
$student_stmt = $connection->prepare($student_sql);
$student_stmt->bind_param("i", $class_id);
$student_stmt->execute();
$total_students = $student_stmt->get_result()->fetch_assoc()['total'];

// Get subjects assigned to this teacher for this class
$subjects_sql = "SELECT 
                    s.subject_id, 
                    s.subject_name, 
                    s.subject_code,
                    COUNT(DISTINCT r.student_id) as marks_entered,
                    AVG(r.marks_obtained) as avg_marks,
                    MAX(r.marks_obtained) as max_marks,
                    MIN(r.marks_obtained) as min_marks,
                    SUM(CASE WHEN r.marks_obtained >= 40 THEN 1 ELSE 0 END) as passed_count
                 FROM subject s
                 INNER JOIN teacher_subject_assignment tsa ON s.subject_id = tsa.subject_id
                 LEFT JOIN result r ON r.subject_id = s.subject_id 
                    AND r.class_id = tsa.class_id
                    AND r.verification_status IN ('pending', 'verified')
                 WHERE tsa.teacher_id = ? AND tsa.class_id = ?
                 GROUP BY s.subject_id
                 ORDER BY s.subject_name";
$subjects_stmt = $connection->prepare($subjects_sql);
$subjects_stmt->bind_param("ii", $teacher_id, $class_id);
$subjects_stmt->execute();
$subjects = $subjects_stmt->get_result();

if (!$subjects || $subjects->num_rows === 0) {
    echo '<div class="text-center py-5">
            <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
            <h5>No Subjects Assigned</h5>
            <p class="text-muted">You don\'t have any subjects assigned for this class.</p>
          </div>';
    exit();
}
?>

<!-- Highcharts CDN - No download needed -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<style>
    /* Minimal styling - just enough to look clean */
    .performance-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: white;
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 1.2rem;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .insight-text {
        background: #f8f9fc;
        border-left: 4px solid #667eea;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        font-size: 0.95rem;
    }
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        border: none;
        padding: 0.6rem 1.2rem;
    }
    .nav-tabs .nav-link.active {
        color: #667eea;
        border-bottom: 3px solid #667eea;
        background: transparent;
    }
</style>

<!-- Header -->
<div class="performance-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="bi bi-bar-chart-fill me-2"></i>Class Performance</h4>
            <p class="mb-0 opacity-75"><?= htmlspecialchars($class['faculty']) ?> - Semester <?= $class['semester'] ?> | <?= $total_students ?> Students</p>
        </div>
    </div>
</div>

<!-- Subject Tabs -->
<ul class="nav nav-tabs mb-4" id="subjectTabs" role="tablist">
    <?php 
    $first = true;
    $subjects->data_seek(0);
    while($subject = $subjects->fetch_assoc()): 
    ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $first ? 'active' : '' ?>" 
                id="tab-<?= $subject['subject_id'] ?>" 
                data-bs-toggle="tab" 
                data-bs-target="#subject-<?= $subject['subject_id'] ?>" 
                type="button">
            <?= htmlspecialchars($subject['subject_code']) ?>
        </button>
    </li>
    <?php 
    $first = false;
    endwhile;
    $subjects->data_seek(0);
    ?>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="subjectTabContent">
    <?php 
    $first = true;
    while($subject = $subjects->fetch_assoc()): 
        $marks_entered = intval($subject['marks_entered']);
        $avg_marks = round($subject['avg_marks'] ?? 0, 1);
        $pass_rate = $marks_entered > 0 ? round(($subject['passed_count'] / $marks_entered) * 100) : 0;
        $completion = $total_students > 0 ? round(($marks_entered / $total_students) * 100) : 0;
        
        // Get student marks for chart
        $chart_sql = "SELECT 
                        s.student_name,
                        COALESCE(r.marks_obtained, 0) as marks
                     FROM student s
                     LEFT JOIN result r ON r.student_id = s.student_id 
                        AND r.subject_id = ? 
                        AND r.class_id = ?
                     WHERE s.class_id = ? AND s.is_active = 1
                     ORDER BY s.student_name";
        $chart_stmt = $connection->prepare($chart_sql);
        $chart_stmt->bind_param("iii", $subject['subject_id'], $class_id, $class_id);
        $chart_stmt->execute();
        $chart_students = $chart_stmt->get_result();
        
        $student_names = [];
        $student_marks = [];
        $student_colors = [];
        
        while($student = $chart_students->fetch_assoc()) {
            $student_names[] = $student['student_name'];
            $student_marks[] = (int)$student['marks'];
            
            if ($student['marks'] >= 80) $student_colors[] = '#28a745';
            else if ($student['marks'] >= 60) $student_colors[] = '#ffc107';
            else if ($student['marks'] >= 40) $student_colors[] = '#fd7e14';
            else if ($student['marks'] > 0) $student_colors[] = '#dc3545';
            else $student_colors[] = '#6c757d';
        }
    ?>
    
    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" 
         id="subject-<?= $subject['subject_id'] ?>" 
         role="tabpanel">
        
        <!-- Subject Info Row -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h5 class="mb-1"><?= htmlspecialchars($subject['subject_name']) ?></h5>
                <span class="badge bg-secondary"><?= $subject['subject_code'] ?></span>
                <span class="badge bg-info ms-2"><?= $subject['credits'] ?? 3 ?> Credits</span>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-inline-block p-2 bg-light rounded">
                    <span class="fw-bold text-<?= $completion == 100 ? 'success' : ($completion > 0 ? 'warning' : 'danger') ?>">
                        <?= $completion ?>% Complete
                    </span>
                    <small class="d-block text-muted"><?= $marks_entered ?>/<?= $total_students ?> students</small>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="text-primary fs-3 mb-2">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $avg_marks ?></h3>
                    <small class="text-muted">Class Average</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="text-success fs-3 mb-2">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $pass_rate ?>%</h3>
                    <small class="text-muted">Pass Rate</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="text-warning fs-3 mb-2">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $subject['max_marks'] ?? 0 ?></h3>
                    <small class="text-muted">Highest Score</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="text-info fs-3 mb-2">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                    <h3 class="mb-0 fw-bold"><?= $subject['min_marks'] ?? 0 ?></h3>
                    <small class="text-muted">Lowest Score</small>
                </div>
            </div>
        </div>
        
        <!-- CHART - Main Focus -->
        <?php if ($marks_entered > 0): ?>
        <div class="chart-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Student Performance Distribution</h6>
                <div>
                    <span class="badge bg-success me-1">90-100%</span>
                    <span class="badge bg-info me-1">70-89%</span>
                    <span class="badge bg-warning me-1">50-69%</span>
                    <span class="badge bg-danger me-1">Below 50%</span>
                </div>
            </div>
            <div id="chart-<?= $subject['subject_id'] ?>" style="height: 350px; width: 100%;"></div>
            
            <!-- Insight Text - What the chart means -->
            <div class="insight-text">
                <i class="bi bi-lightbulb-fill text-warning me-2"></i>
                <strong>Quick Insight:</strong> 
                <?php 
                if ($avg_marks >= 75) {
                    echo "Excellent performance! Class average is {$avg_marks}%. Students are doing very well.";
                } elseif ($avg_marks >= 60) {
                    echo "Good performance. Class average is {$avg_marks}%. Some students need extra attention.";
                } elseif ($avg_marks >= 40) {
                    echo "Average performance. Class average is {$avg_marks}%. Consider additional practice sessions.";
                } else {
                    echo "Needs improvement. Class average is {$avg_marks}%. Review the concepts again.";
                }
                ?>
            </div>
        </div>
        
        <script>
            Highcharts.chart('chart-<?= $subject['subject_id'] ?>', {
                chart: {
                    type: 'column',
                    backgroundColor: 'transparent',
                    style: { fontFamily: 'inherit' }
                },
                title: { text: undefined },
                subtitle: { text: undefined },
                xAxis: {
                    categories: <?= json_encode($student_names) ?>,
                    labels: {
                        rotation: -30,
                        style: { fontSize: '11px', fontWeight: 'normal' }
                    },
                    title: { text: 'Students' }
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: { 
                        text: 'Marks (%)',
                        style: { fontSize: '12px' }
                    },
                    plotLines: [{
                        value: 40,
                        color: '#ffc107',
                        dashStyle: 'dash',
                        width: 2,
                        label: { 
                            text: 'Passing Mark',
                            align: 'right',
                            style: { fontSize: '11px', color: '#666' }
                        }
                    }]
                },
                tooltip: {
                    headerFormat: '<b>{point.key}</b><br/>',
                    pointFormat: '<span style="color:{point.color}">●</span> Marks: <b>{point.y}/100</b><br/>' +
                                 '<span style="color:#666">Grade: {point.grade}</span>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        borderRadius: 5,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y}',
                            style: { fontSize: '10px', fontWeight: 'normal' }
                        },
                        pointPadding: 0.1,
                        groupPadding: 0.1
                    }
                },
                series: [{
                    name: 'Marks',
                    data: <?= json_encode($student_marks) ?>,
                    colorByPoint: true,
                    colors: <?= json_encode($student_colors) ?>,
                    point: {
                        events: {
                            mouseOver: function() {
                                const marks = this.y;
                                let grade = '';
                                if (marks >= 90) grade = 'A+';
                                else if (marks >= 80) grade = 'A';
                                else if (marks >= 70) grade = 'B+';
                                else if (marks >= 60) grade = 'B';
                                else if (marks >= 50) grade = 'C+';
                                else if (marks >= 40) grade = 'C';
                                else grade = 'F';
                                this.grade = grade;
                            }
                        }
                    }
                }],
                credits: { enabled: false },
                legend: { enabled: false }
            });
        </script>
        <?php else: ?>
        <div class="chart-container text-center py-5">
            <i class="bi bi-bar-chart display-1 text-muted mb-3"></i>
            <h5 class="text-muted">No marks entered yet</h5>
            <p class="text-muted mb-0">Click the button below to enter marks for this subject</p>
        </div>
        <?php endif; ?>
        
        <!-- Quick Action -->
        <div class="text-center mt-3">
            <a href="enter_results.php?class_id=<?= $class_id ?>&subject_id=<?= $subject['subject_id'] ?>" 
               class="btn btn-primary px-5">
                <i class="bi bi-pencil me-2"></i>Enter / Edit Marks
            </a>
        </div>
    </div>
    
    <?php 
    $first = false;
    endwhile; 
    ?>
</div>

<!-- Simple Legend -->
<div class="mt-4 p-3 bg-light rounded">
    <div class="row">
        <div class="col-md-6">
            <small class="text-muted d-block mb-2"><i class="bi bi-info-circle me-1"></i> Chart Guide:</small>
            <div class="d-flex flex-wrap gap-3">
                <span><span class="d-inline-block rounded-circle bg-success me-1" style="width: 12px; height: 12px;"></span> Excellent (80-100%)</span>
                <span><span class="d-inline-block rounded-circle bg-warning me-1" style="width: 12px; height: 12px;"></span> Good (60-79%)</span>
                <span><span class="d-inline-block rounded-circle bg-orange me-1" style="width: 12px; height: 12px;"></span> Average (40-59%)</span>
                <span><span class="d-inline-block rounded-circle bg-danger me-1" style="width: 12px; height: 12px;"></span> Needs Work (Below 40%)</span>
                <span><span class="d-inline-block rounded-circle bg-secondary me-1" style="width: 12px; height: 12px;"></span> Not Entered</span>
            </div>
        </div>
        <div class="col-md-6 text-md-end">
            <small class="text-muted">
                <i class="bi bi-arrow-repeat me-1"></i> Hover over bars for grade details
            </small>
        </div>
    </div>
</div>