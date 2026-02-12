<?php
// get_classes.php - COMPLETE VERSION
session_start();
include('../../config.php');

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    echo json_encode([]);
    exit();
}

// Get filter parameters
$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$status = $_GET['status'] ?? '';

// Build query with all fields
$sql = "SELECT c.class_id, 
               c.faculty, 
               c.semester, 
               c.batch_year,
               c.status,
               c.created_at,
               COUNT(DISTINCT s.student_id) as student_count 
        FROM class c
        LEFT JOIN student s ON c.class_id = s.class_id
        WHERE 1=1";

$params = [];
$types = '';

if ($faculty) {
    $sql .= " AND c.faculty = ?";
    $params[] = $faculty;
    $types .= 's';
}

if ($semester) {
    $sql .= " AND c.semester = ?";
    $params[] = $semester;
    $types .= 'i';
}

if ($status) {
    $sql .= " AND c.status = ?";
    $params[] = $status;
    $types .= 's';
}

$sql .= " GROUP BY c.class_id ORDER BY c.faculty, c.semester";

$stmt = $connection->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    // Format dates properly
    $created_at = $row['created_at'] ?? date('Y-m-d H:i:s');
    $batch_year = $row['batch_year'] ?? date('Y');
    
    $classes[] = [
        'class_id' => $row['class_id'],
        'faculty' => $row['faculty'],
        'faculty_name' => $row['faculty'],
        'semester' => $row['semester'],
        'batch_year' => $batch_year,
        'status' => $row['status'],
        'student_count' => (int)($row['student_count'] ?? 0),
        'created_at' => $created_at
    ];
}

echo json_encode($classes);
?>