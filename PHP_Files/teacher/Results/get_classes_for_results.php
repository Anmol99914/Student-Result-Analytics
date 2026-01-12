<?php
session_start();
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
require_once '../../../config.php';
// Also add MySQL query that doesn't use cache
$connection->query("SET SESSION query_cache_type = OFF");

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Get classes assigned to this teacher
$sql = "SELECT 
            c.class_id, 
            c.faculty, 
            c.semester, 
            c.status,
            (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count,
            (SELECT COUNT(DISTINCT r.student_id) FROM result r 
             JOIN student s ON r.student_id = s.student_id 
             WHERE s.class_id = c.class_id 
             AND r.entered_by_teacher_id = ?) as results_entered
        FROM class c
        INNER JOIN teacher_class_assignments tca ON c.class_id = tca.class_id
        WHERE tca.teacher_id = ?
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
            <br><small>Contact admin to get classes assigned.</small>
          </div>';
    return;
}

echo '<div class="row">';
echo '<div class="col-12 mb-3">';
echo '<h6>Select a class to enter results:</h6>';
echo '</div>';

foreach ($classes as $class) {
    $student_count = intval($class['student_count']);
    $results_entered = intval($class['results_entered']);
    
    // Calculate progress percentage
    if ($student_count <= 0) {
        $student_count = 0;
        $results_percent = 0;
    } else {
        $results_percent = min(100, round(($results_entered / $student_count) * 100));
    }
    
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
    echo '<small class="text-muted">Results Progress</small>';
    echo '<small class="text-muted">' . $results_percent . '%</small>';
    echo '</div>';
    echo '<div class="progress" style="height: 6px;">';
    echo '<div class="progress-bar ' . ($results_percent == 100 ? 'bg-success' : ($results_percent > 0 ? 'bg-warning' : 'bg-secondary')) . '" 
          style="width: ' . $results_percent . '%"></div>';
    echo '</div>';
    echo '<small class="text-muted d-block mt-1">';
    echo $results_entered . ' of ' . $student_count . ' students';
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