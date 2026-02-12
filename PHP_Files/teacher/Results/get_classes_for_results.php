<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
require_once '../../../config.php';
$connection->query("SET SESSION query_cache_type = OFF");

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$sql = "SELECT DISTINCT 
            c.class_id, 
            c.faculty, 
            c.semester, 
            c.status,
            (SELECT COUNT(*) FROM student WHERE class_id = c.class_id AND is_active = 1) as student_count,
            (SELECT COUNT(DISTINCT tsa.subject_id) 
             FROM teacher_subject_assignment tsa 
             WHERE tsa.class_id = c.class_id 
             AND tsa.teacher_id = ?) as subjects_assigned
        FROM class c
        INNER JOIN teacher_subject_assignment tsa ON c.class_id = tsa.class_id
        WHERE tsa.teacher_id = ?
        AND c.status = 'active'
        ORDER BY c.faculty, c.semester";

$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$classes = $result->fetch_all(MYSQLI_ASSOC);

if (empty($classes)) {
    echo '<div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            No classes assigned to you yet.
            <br><small>Contact admin to get subjects assigned to you.</small>
          </div>';
    return;
}

echo '<div class="row">';
echo '<div class="col-12 mb-3">';
echo '<h6>Select a class to enter results:</h6>';
echo '</div>';

foreach ($classes as $class) {
    $student_count = intval($class['student_count']);
    $subjects_assigned = intval($class['subjects_assigned']);
    $total_subjects = 5; // Default for BCA/BBM/BIM Semester 1
    
    // Calculate progress based on subjects completed
    $results_percent = $total_subjects > 0 ? round(($subjects_assigned / $total_subjects) * 100) : 0;
    
    echo '<div class="col-md-4 mb-4">';
    echo '<div class="card class-card h-100" 
          data-class-id="' . $class['class_id'] . '"
          data-faculty="' . htmlspecialchars($class['faculty']) . '"
          data-semester="' . $class['semester'] . '">';
    
    echo '<div class="card-header bg-light">';
    echo '<h6 class="mb-0">' . htmlspecialchars($class['faculty']) . '</h6>';
    echo '</div>';
    
    echo '<div class="card-body">';
    
    // Progress
    echo '<div class="mb-3">';
    echo '<div class="d-flex justify-content-between mb-1">';
    echo '<small class="text-muted">Subjects Progress</small>';
    echo '<small class="text-muted">' . $subjects_assigned . '/' . $total_subjects . ' subjects</small>';
    echo '</div>';
    echo '<div class="progress" style="height: 6px;">';
    echo '<div class="progress-bar ' . ($results_percent == 100 ? 'bg-success' : ($results_percent > 0 ? 'bg-warning' : 'bg-secondary')) . '" 
          style="width: ' . $results_percent . '%"></div>';
    echo '</div>';
    echo '<small class="text-muted d-block mt-1">';
    echo 'You teach ' . $subjects_assigned . ' subjects in this class';
    echo '</small>';
    echo '</div>';
    
    // Info
    echo '<div class="small text-muted">';
    echo '<div class="mb-1">';
    echo '<i class="bi bi-calendar-week"></i> Semester ' . $class['semester'];
    echo '</div>';
    echo '<div class="mb-1">';
    echo '<i class="bi bi-people"></i> ' . $student_count . ' students';
    echo '</div>';
    echo '</div>';
    echo '</div>'; // card-body
    
    echo '<div class="card-footer bg-transparent">';
    echo '<button class="btn btn-primary btn-sm w-100 select-class-btn" 
            data-class-id="' . $class['class_id'] . '"
            data-faculty="' . htmlspecialchars($class['faculty']) . '"
            data-semester="' . $class['semester'] . '">
            <i class="bi bi-arrow-right"></i> Select Class
          </button>';
    echo '</div>';
    
    echo '</div>'; // card
    echo '</div>'; // col
}

echo '</div>';
?>