<?php
require_once '../config.php';

header('Content-Type: application/json');

// Get all classes and check if ALL results are verified
$class_info = [];
$class_query = "SELECT class_id, faculty, semester FROM class WHERE status = 'active'";
$class_result = $connection->query($class_query);

while($class = $class_result->fetch_assoc()) {
    // Count total subjects for this class
    $subject_count_sql = "SELECT COUNT(*) as total FROM subject WHERE faculty_id = (
                            SELECT faculty_id FROM faculty WHERE faculty_code = ?
                          ) AND semester = ?";
    $subject_stmt = $connection->prepare($subject_count_sql);
    $subject_stmt->bind_param("si", $class['faculty'], $class['semester']);
    $subject_stmt->execute();
    $subject_count = $subject_stmt->get_result()->fetch_assoc()['total'];
    
    // Count total students in class
    $student_sql = "SELECT COUNT(*) as total FROM student WHERE class_id = ? AND is_active = 1";
    $student_stmt = $connection->prepare($student_sql);
    $student_stmt->bind_param("i", $class['class_id']);
    $student_stmt->execute();
    $student_count = $student_stmt->get_result()->fetch_assoc()['total'];
    
    // Calculate expected total entries (subjects × students)
    $expected_total = $subject_count * $student_count;
    
    // Count verified results for this class (last 7 days)
    $verified_sql = "SELECT COUNT(*) as verified_count
                    FROM result r
                    WHERE r.class_id = ? 
                    AND r.verification_status = 'verified'
                    AND r.verification_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $verified_stmt = $connection->prepare($verified_sql);
    $verified_stmt->bind_param("i", $class['class_id']);
    $verified_stmt->execute();
    $verified_count = $verified_stmt->get_result()->fetch_assoc()['verified_count'];
    
    // If ALL results are verified, add to notices
    if ($expected_total > 0 && $verified_count >= $expected_total) {
        $class_info[] = [
            'faculty' => $class['faculty'],
            'semester' => $class['semester'],
            'date' => date('d M Y'),
            'message' => "{$class['faculty']} Semester {$class['semester']} results published"
        ];
    }
}

// Sort by date (newest first)
usort($class_info, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limit to 5 most recent
$class_info = array_slice($class_info, 0, 5);

echo json_encode($class_info);
?>