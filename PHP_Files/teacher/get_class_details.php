<?php
// get_class_details.php
session_start();
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$teacher_id = $_SESSION['teacher_id'];

if ($class_id <= 0) {
    echo '<div class="alert alert-danger">Invalid class ID</div>';
    exit();
}

try {
    // Get class details
    $class_sql = "SELECT c.*, 
                  (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count,
                  (SELECT COUNT(*) FROM teacher_class_assignments WHERE class_id = c.class_id) as teacher_count
                  FROM class c
                  WHERE c.class_id = ?";
    
    $class_stmt = $connection->prepare($class_sql);
    $class_stmt->bind_param("i", $class_id);
    $class_stmt->execute();
    $class_result = $class_stmt->get_result();
    $class_data = $class_result->fetch_assoc();
    
    if (!$class_data) {
        echo '<div class="alert alert-danger">Class not found</div>';
        exit();
    }
    
    // Get subjects for this class (faculty and semester)
    $subject_sql = "SELECT s.*, 
                   (SELECT COUNT(DISTINCT student_id) FROM result WHERE subject_id = s.subject_id 
                    AND student_id IN (SELECT student_id FROM student WHERE class_id = ?)) as results_count
                   FROM subject s
                   WHERE s.faculty_id = (SELECT faculty_id FROM faculty WHERE faculty_code = ?)
                   AND s.semester = ?
                   AND s.status = 'active'
                   ORDER BY s.subject_name";
    
    $subject_stmt = $connection->prepare($subject_sql);
    $subject_stmt->bind_param("isi", $class_id, $class_data['faculty'], $class_data['semester']);
    $subject_stmt->execute();
    $subject_result = $subject_stmt->get_result();
    $subjects = $subject_result->fetch_all(MYSQLI_ASSOC);
    
    // FIX 1: Get TOTAL subjects count for this class
    $total_subjects_sql = "SELECT COUNT(*) as total 
                           FROM subject s
                           WHERE s.faculty_id = (SELECT faculty_id FROM faculty WHERE faculty_code = ?)
                           AND s.semester = ?
                           AND s.status = 'active'";
    $total_subjects_stmt = $connection->prepare($total_subjects_sql);
    $total_subjects_stmt->bind_param("si", $class_data['faculty'], $class_data['semester']);
    $total_subjects_stmt->execute();
    $total_subjects_result = $total_subjects_stmt->get_result();
    $total_subjects = $total_subjects_result->fetch_assoc()['total'];
    
    // FIX 2: Get subjects that HAVE MARKS for this class
    $subjects_with_marks_sql = "SELECT COUNT(DISTINCT r.subject_id) as count 
                                FROM result r
                                JOIN student s ON r.student_id = s.student_id
                                WHERE s.class_id = ?
                                AND r.verification_status IN ('pending', 'verified')";
                                
    $subjects_with_marks_stmt = $connection->prepare($subjects_with_marks_sql);
    $subjects_with_marks_stmt->bind_param("i", $class_id);
    $subjects_with_marks_stmt->execute();
    $subjects_with_marks_result = $subjects_with_marks_stmt->get_result();
    $subjects_with_marks = $subjects_with_marks_result->fetch_assoc()['count'];
    
    // FIX 3: Calculate class progress based on SUBJECTS, not students
    $class_progress = $total_subjects > 0 ? round(($subjects_with_marks / $total_subjects) * 100) : 0;
    
    // DEBUG: Log the values to see what's happening
error_log("Class ID: $class_id, Total Subjects: $total_subjects, Subjects with Marks: $subjects_with_marks, Progress: $class_progress%");

    // Get recent results for this class
    $results_sql = "SELECT r.*, s.student_name, sub.subject_name,
               DATE_FORMAT(r.published_date, '%b %d, %Y') as result_date
               FROM result r
               JOIN student s ON r.student_id = s.student_id
               JOIN subject sub ON r.subject_id = sub.subject_id
               WHERE s.class_id = ?
               ORDER BY r.published_date DESC
               LIMIT 5";
               
    $results_stmt = $connection->prepare($results_sql);
    $results_stmt->bind_param("i", $class_id);
    $results_stmt->execute();
    $results_result = $results_stmt->get_result();
    $recent_results = $results_result->fetch_all(MYSQLI_ASSOC);
    
    // Display class information
    echo '<div class="row mb-4">';
    echo '<div class="col-md-8">';
    echo '<div class="card border-primary">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> Class Information</h6>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<p><strong><i class="bi bi-building me-2"></i> Faculty:</strong><br>';
    echo '<span class="badge bg-primary fs-6">' . htmlspecialchars($class_data['faculty']) . '</span></p>';
    
    echo '<p><strong><i class="bi bi-calendar-week me-2"></i> Semester:</strong><br>';
    echo '<span class="badge bg-info text-dark fs-6">Semester ' . $class_data['semester'] . '</span></p>';
    echo '</div>';
    
    echo '<div class="col-md-6">';
    echo '<p><strong><i class="bi bi-people me-2"></i> Students:</strong><br>';
    echo '<span class="badge bg-success fs-6">' . $class_data['student_count'] . ' students</span></p>';
    
    echo '<p><strong><i class="bi bi-person-badge me-2"></i> Status:</strong><br>';
    echo '<span class="badge ' . ($class_data['status'] === 'active' ? 'bg-success' : 'bg-secondary') . ' fs-6">';
    echo ucfirst($class_data['status']) . '</span></p>';
    echo '</div>';
    echo '</div>';
    
    if (!empty($class_data['batch_year'])) {
        echo '<p><strong><i class="bi bi-calendar me-2"></i> Batch Year:</strong> ' . $class_data['batch_year'] . '</p>';
    }
    
    if (!empty($class_data['description'])) {
        echo '<p><strong><i class="bi bi-card-text me-2"></i> Description:</strong><br>';
        echo htmlspecialchars($class_data['description']) . '</p>';
    }
    
    echo '</div>';
    echo '<div class="card-footer">';
    echo '<button class="btn btn-sm btn-outline-primary me-2" onclick="loadMyStudents(' . $class_id . ')">';
    echo '<i class="bi bi-people me-1"></i> View Students</button>';
    echo '<button class="btn btn-sm btn-outline-warning" onclick="loadAddResultForm()">';
    echo '<i class="bi bi-trophy me-1"></i> Enter Results</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';   
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<i class="bi bi-exclamation-triangle me-2"></i>';
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>