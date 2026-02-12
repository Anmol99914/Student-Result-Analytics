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
    echo '<button class="btn btn-sm btn-outline-primary me-2" onclick="viewClassStudents(' . $class_id . ')">';
    echo '<i class="bi bi-people me-1"></i> View Students</button>';
    echo '<button class="btn btn-sm btn-outline-warning" onclick="loadAddResultForm()">';
    echo '<i class="bi bi-trophy me-1"></i> Enter Results</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Quick stats
    echo '<div class="col-md-4">';
    echo '<div class="card border-success">';
    echo '<div class="card-header bg-success text-white">';
    echo '<h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Quick Stats</h6>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<div class="text-center mb-3">';
    echo '<i class="bi bi-book display-4 text-success mb-2"></i>';
    echo '<h5>' . $total_subjects . ' Subjects</h5>';
    echo '</div>';
    
    echo '<div class="list-group">';
    echo '<div class="list-group-item d-flex justify-content-between">';
    echo '<span><i class="bi bi-people text-primary me-2"></i>Total Students</span>';
    echo '<span class="badge bg-primary rounded-pill">' . $class_data['student_count'] . '</span>';
    echo '</div>';
    
    echo '<div class="list-group-item d-flex justify-content-between">';
    echo '<span><i class="bi bi-book text-success me-2"></i>Total Subjects</span>';
    echo '<span class="badge bg-success rounded-pill">' . $total_subjects . '</span>';
    echo '</div>';
    

    
    echo '<div class="list-group-item d-flex justify-content-between">';
    echo '<span><i class="bi bi-trophy text-warning me-2"></i>Results Entered</span>';
    echo '<span class="badge bg-warning rounded-pill">' . count($recent_results) . '+</span>';
    echo '</div>';
    
    echo '<div class="list-group-item d-flex justify-content-between">';
    echo '<span><i class="bi bi-person-badge text-info me-2"></i>Assigned Teachers</span>';
    echo '<span class="badge bg-info rounded-pill">' . $class_data['teacher_count'] . '</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Subjects section
if (!empty($subjects)) {
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-info text-white d-flex justify-content-between align-items-center">';
    echo '<h6 class="mb-0"><i class="bi bi-book me-2"></i> Subjects (' . $total_subjects . ')</h6>';
    echo '<button class="btn btn-sm btn-light" onclick="loadAddResultForm()">';
    echo '<i class="bi bi-trophy me-1"></i> Enter Marks</button>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<div class="row">';
    
    foreach ($subjects as $subject) {
        // Calculate student completion percentage
        $results_percent = $class_data['student_count'] > 0 
            ? round(($subject['results_count'] / $class_data['student_count']) * 100) 
            : 0;
        
        // Determine status
        if ($results_percent == 100) {
            $status_badge = '<span class="badge bg-success">Complete</span>';
            $border_color = 'success';
            $progress_color = 'success';
        } else if ($results_percent > 0) {
            $status_badge = '<span class="badge bg-warning text-dark">Partial</span>';
            $border_color = 'warning';
            $progress_color = 'warning';
        } else {
            $status_badge = '<span class="badge bg-secondary">Not Started</span>';
            $border_color = 'secondary';
            $progress_color = 'secondary';
        }
        
        echo '<div class="col-md-4 mb-3">';
        echo '<div class="card h-100 border-' . $border_color . '">';
        echo '<div class="card-body">';
        echo '<div class="d-flex justify-content-between align-items-start mb-2">';
        echo '<h6 class="card-title mb-0">' . htmlspecialchars($subject['subject_name']) . '</h6>';
        echo $status_badge;
        echo '</div>';
        
        echo '<p class="card-text small text-muted mb-2">';
        echo '<span class="badge bg-secondary">' . htmlspecialchars($subject['subject_code']) . '</span>';
        if ($subject['is_elective']) {
            echo ' <span class="badge bg-info">Elective</span>';
        }
        if ($subject['credits']) {
            echo ' <span class="badge bg-light text-dark">' . $subject['credits'] . ' cr</span>';
        }
        echo '</p>';
        
        echo '<div class="mb-2">';
        echo '<div class="d-flex justify-content-between align-items-center mb-1">';
        echo '<small class="text-muted">Student Completion</small>';
        echo '<small class="fw-bold text-' . $progress_color . '">' . $results_percent . '%</small>';
        echo '</div>';
        
        echo '<div class="progress" style="height: 8px;">';
        echo '<div class="progress-bar bg-' . $progress_color . '" 
              role="progressbar" style="width: ' . $results_percent . '%" 
              aria-valuenow="' . $results_percent . '" aria-valuemin="0" aria-valuemax="100"></div>';
        echo '</div>';
        
        echo '<div class="d-flex justify-content-between mt-1">';
        echo '<small class="text-muted">' . $subject['results_count'] . ' of ' . $class_data['student_count'] . ' students</small>';
        
        // Show missing students count if partial
        if ($results_percent > 0 && $results_percent < 100) {
            $missing = $class_data['student_count'] - $subject['results_count'];
            echo '<small class="text-danger">' . $missing . ' missing</small>';
        } else if ($results_percent == 0) {
            echo '<small class="text-muted">No entries</small>';
        }
        echo '</div>';
        echo '</div>';
        
        echo '<div class="d-flex justify-content-between mt-3">';
        echo '<small><i class="bi bi-journal-text me-1"></i>' . $subject['credits'] . ' Credits</small>';
        echo '<button class="btn btn-sm btn-outline-primary" onclick="enterSubjectMarks(' . $class_id . ', ' . $subject['subject_id'] . ')">';
        echo '<i class="bi bi-pencil"></i> Enter</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
    
    // Recent results section
    if (!empty($recent_results)) {
        echo '<div class="card">';
        echo '<div class="card-header bg-warning text-dark">';
        echo '<h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Recent Results</h6>';
        echo '</div>';
        echo '<div class="card-body">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-sm">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Student</th>';
        echo '<th>Subject</th>';
        echo '<th>Marks</th>';
        echo '<th>Grade</th>';
        echo '<th>Date</th>';
        echo '<th>Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($recent_results as $result) {
            $status_badge = $result['verification_status'] === 'verified' 
                ? '<span class="badge bg-success">Verified</span>'
                : ($result['verification_status'] === 'pending' 
                    ? '<span class="badge bg-warning">Pending</span>' 
                    : '<span class="badge bg-secondary">' . ucfirst($result['verification_status']) . '</span>');
            
            echo '<tr>';
            echo '<td><small>' . htmlspecialchars($result['student_name']) . '</small></td>';
            echo '<td><small>' . htmlspecialchars($result['subject_name']) . '</small></td>';
            echo '<td><span class="badge bg-primary">' . $result['marks_obtained'] . '/' . $result['total_marks'] . '</span></td>';
            echo '<td><span class="badge bg-info">' . $result['grade'] . '</span></td>';
            echo '<td><small class="text-muted">' . $result['result_date'] . '</small></td>';
            echo '<td>' . $status_badge . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<i class="bi bi-exclamation-triangle me-2"></i>';
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    echo '</div>';
}
?>