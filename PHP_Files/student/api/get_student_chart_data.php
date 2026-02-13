<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

// Debug: log the request
error_log("Chart API called - student: " . ($_GET['student_id'] ?? 'none') . ", type: " . ($_GET['type'] ?? 'none'));

$student_id = $_GET['student_id'] ?? '';
$type = $_GET['type'] ?? 'semester';

if (!$student_id) {
    echo json_encode(['success' => false, 'error' => 'Student ID required']);
    exit();
}

if ($type === 'semester') {
    // Get semester-wise performance
    $sql = "SELECT 
                sem.semester_name,
                AVG(r.percentage) as avg_percentage
            FROM result r
            JOIN semester sem ON r.semester_id = sem.semester_id
            WHERE r.student_id = ? AND r.status = 'published'
            GROUP BY r.semester_id
            ORDER BY r.semester_id";
            
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    $values = [];
    
    while($row = $result->fetch_assoc()) {
        $categories[] = $row['semester_name'];
        $values[] = round($row['avg_percentage'], 1);
    }
    
    echo json_encode([
        'success' => true,
        'type' => 'semester',
        'title' => 'Semester-wise Performance',
        'xAxis' => 'Semester',
        'categories' => $categories,
        'seriesName' => 'Average Percentage',
        'values' => $values
    ]);
    
} else {
    // Get subject-wise performance for current semester
    $semester = $_SESSION['student_semester'] ?? 1;
    
    $sql = "SELECT 
                s.subject_name,
                r.percentage
            FROM result r
            JOIN subject s ON r.subject_id = s.subject_id
            WHERE r.student_id = ? AND r.semester_id = ? AND r.status = 'published'
            ORDER BY s.subject_name";
            
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $student_id, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    $values = [];
    
    while($row = $result->fetch_assoc()) {
        $categories[] = $row['subject_name'];
        $values[] = round($row['percentage'], 1);
    }
    
    echo json_encode([
        'success' => true,
        'type' => 'subject',
        'title' => 'Subject-wise Performance (Current Semester)',
        'xAxis' => 'Subject',
        'categories' => $categories,
        'seriesName' => 'Percentage',
        'values' => $values
    ]);
}
?>