<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$faculty_code = isset($_GET['faculty']) ? $_GET['faculty'] : ''; // This is actually faculty_code from class table
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

if (!$class_id || !$faculty_code) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit();
}

error_log("Looking for faculty_code: '$faculty_code', Semester: $semester");

// Get faculty_id from faculty_code (not faculty_name!)
$faculty_sql = "SELECT faculty_id FROM faculty WHERE faculty_code = ?";
$faculty_stmt = $connection->prepare($faculty_sql);
$faculty_stmt->bind_param("s", $faculty_code);
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();

if ($faculty_result->num_rows === 0) {
    $alt_sql = "SELECT faculty_id FROM faculty WHERE faculty_name = ?";
    $alt_stmt = $connection->prepare($alt_sql);
    $alt_stmt->bind_param("s", $faculty_code);
    $alt_stmt->execute();
    $alt_result = $alt_stmt->get_result();
    
    if ($alt_result->num_rows === 0) {
        echo '<div class="alert alert-danger">';
        echo '<h5><i class="bi bi-exclamation-triangle"></i> Faculty Not Found</h5>';
        echo '<p>Looking for faculty code/name: <strong>' . htmlspecialchars($faculty_code) . '</strong></p>';
        
        // Show available faculty
        $all_sql = "SELECT faculty_code, faculty_name FROM faculty";
        $all_result = $connection->query($all_sql);
        echo '<p>Available faculty:</p><ul>';
        while ($row = $all_result->fetch_assoc()) {
            echo '<li><strong>' . htmlspecialchars($row['faculty_code']) . '</strong> - ' . 
                 htmlspecialchars($row['faculty_name']) . '</li>';
        }
        echo '</ul></div>';
        exit();
    }
    
    $faculty_data = $alt_result->fetch_assoc();
    $faculty_id = $faculty_data['faculty_id'];
    error_log("Found via faculty_name: faculty_id=$faculty_id for '$faculty_code'");
} else {
    $faculty_data = $faculty_result->fetch_assoc();
    $faculty_id = $faculty_data['faculty_id'];
    error_log("Found via faculty_code: faculty_id=$faculty_id for '$faculty_code'");
}

// Get faculty name for display
$faculty_name_sql = "SELECT faculty_name FROM faculty WHERE faculty_id = ?";
$faculty_name_stmt = $connection->prepare($faculty_name_sql);
$faculty_name_stmt->bind_param("i", $faculty_id);
$faculty_name_stmt->execute();
$faculty_name_result = $faculty_name_stmt->get_result();
$faculty_name_data = $faculty_name_result->fetch_assoc();
$faculty_display_name = $faculty_name_data['faculty_name'] ?? $faculty_code;

// Get ONLY subjects assigned to this teacher for this class
$sql = "SELECT s.* 
        FROM subject s
        INNER JOIN teacher_subject_assignment tsa ON s.subject_id = tsa.subject_id
        WHERE tsa.teacher_id = ? 
        AND tsa.class_id = ?
        AND s.faculty_id = ? 
        AND s.semester = ?
        AND s.status = 'active'
        ORDER BY s.subject_name";

$stmt = $connection->prepare($sql);
$stmt->bind_param("iiii", $_SESSION['teacher_id'], $class_id, $faculty_id, $semester);
$stmt->execute();
$result = $stmt->get_result();

error_log("Found " . $result->num_rows . " ASSIGNED subjects for teacher_id={$_SESSION['teacher_id']}, class_id=$class_id");

if ($result->num_rows === 0) {
    echo '<div class="alert alert-warning">';
    echo '<h5><i class="bi bi-exclamation-triangle"></i> No Subjects Assigned</h5>';
    echo '<p>You don\'t have any subjects assigned for this class.</p>';
    echo '<p class="mb-0">Contact administrator to assign subjects to you.</p>';
    echo '</div>';
    exit();
}

// Get student count for this class
$student_sql = "SELECT COUNT(*) as student_count FROM student WHERE class_id = ?";
$student_stmt = $connection->prepare($student_sql);
$student_stmt->bind_param("i", $class_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_data = $student_result->fetch_assoc();
$student_count = $student_data['student_count'] ?? 0;

echo '<div class="row mb-3">';
echo '<div class="col-12">';
echo '<button class="btn btn-outline-secondary mb-3" onclick="window.ResultsSystem.loadClasses()">';
echo '<i class="bi bi-arrow-left"></i> Back to Classes';
echo '</button>';
echo '<h5>' . htmlspecialchars($faculty_display_name) . ' - Semester ' . $semester . '</h5>';
echo '<p class="text-muted">Select a subject to enter marks for ' . $student_count . ' students.</p>';
echo '</div>';
echo '</div>';

echo '<div class="row">';

while ($subject = $result->fetch_assoc()) {
    // Check if marks already entered for this subject
    $marks_sql = "SELECT COUNT(DISTINCT r.student_id) as marked_count 
                  FROM result r 
                  JOIN student s ON r.student_id = s.student_id 
                  WHERE s.class_id = ? 
                  AND r.subject_id = ? 
                  AND r.entered_by_teacher_id = ?";
    
    $marks_stmt = $connection->prepare($marks_sql);
    $marks_stmt->bind_param("iii", $class_id, $subject['subject_id'], $_SESSION['teacher_id']);
    $marks_stmt->execute();
    $marks_result = $marks_stmt->get_result();
    $marks_data = $marks_result->fetch_assoc();
    $marked_count = $marks_data['marked_count'] ?? 0;
    
    $progress_percent = $student_count > 0 ? round(($marked_count / $student_count) * 100) : 0;
    
    echo '<div class="col-md-4 mb-4">';
    echo '<div class="card h-100 shadow-sm">';
    
    echo '<div class="card-header bg-light d-flex justify-content-between align-items-center">';
    echo '<h6 class="mb-0">' . htmlspecialchars($subject['subject_name']) . '</h6>';
    echo '<span class="badge bg-secondary">' . htmlspecialchars($subject['subject_code']) . '</span>';
    echo '</div>';
    
    echo '<div class="card-body">';
    
    // Progress
    if ($student_count > 0) {
        echo '<div class="mb-3">';
        echo '<div class="d-flex justify-content-between mb-1">';
        echo '<small class="text-muted">Marks Entered</small>';
        echo '<small class="text-muted">' . $progress_percent . '%</small>';
        echo '</div>';
        echo '<div class="progress" style="height: 6px;">';
        echo '<div class="progress-bar ' . ($progress_percent == 100 ? 'bg-success' : ($progress_percent > 0 ? 'bg-warning' : 'bg-secondary')) . '" 
              style="width: ' . $progress_percent . '%"></div>';
        echo '</div>';
        echo '<small class="text-muted d-block mt-1">';
        echo $marked_count . ' of ' . $student_count . ' students';
        echo '</small>';
        echo '</div>';
    }
    
    // Subject Info
    echo '<div class="small text-muted">';
    echo '<div class="mb-2">';
    echo '<i class="bi bi-journal-text"></i> <strong>Credits:</strong> ' . $subject['credits'];
    echo '</div>';
    
    if ($subject['is_elective'] == 1) {
        echo '<div class="mb-2">';
        echo '<i class="bi bi-star"></i> <span class="badge bg-info">Elective</span>';
        echo '</div>';
    }
    
    if (!empty($subject['description']) && $subject['description'] != '0') {
        echo '<div class="mb-2">';
        echo '<i class="bi bi-info-circle"></i> ' . htmlspecialchars(substr($subject['description'], 0, 60));
        if (strlen($subject['description']) > 60) echo '...';
        echo '</div>';
    }
    
    echo '</div>'; // end subject info
    echo '</div>'; // end card-body
    
    // Single Action Button
    echo '<div class="card-footer bg-transparent">';
    echo '<div class="d-grid gap-2">';

    echo '<button class="btn ' . ($marked_count > 0 ? 'btn-warning' : 'btn-primary') . ' btn-sm enter-marks-btn" 
            data-class-id="' . $class_id . '"
            data-subject-id="' . $subject['subject_id'] . '"
            data-subject-name="' . htmlspecialchars($subject['subject_name']) . '"
            data-faculty="' . htmlspecialchars($faculty_display_name) . '"
            data-semester="' . $semester . '">
            <i class="bi ' . ($marked_count > 0 ? 'bi-pencil' : 'bi-pencil-square') . '"></i>
            ' . ($marked_count > 0 ? 'View/Edit Marks (' . $marked_count . ')' : 'Enter Marks') . '
        </button>';

    echo '</div>'; // end grid
    echo '</div>'; // end card-footer
    
    echo '</div>'; // end card
    echo '</div>'; // end col
}

echo '</div>'; // end row
?>