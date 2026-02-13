<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    die('Unauthorized');
}

// Get filters
$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$class_id = $_GET['class_id'] ?? '';

// Build query
$sql = "SELECT 
            s.student_id,
            s.student_name,
            c.faculty,
            c.semester,
            sub.subject_name,
            r.marks_obtained,
            r.total_marks,
            r.percentage,
            r.grade,
            r.status
        FROM result r
        JOIN student s ON r.student_id = s.student_id
        JOIN class c ON s.class_id = c.class_id
        JOIN subject sub ON r.subject_id = sub.subject_id
        WHERE 1=1";

if ($faculty) {
    $sql .= " AND c.faculty = '" . $connection->real_escape_string($faculty) . "'";
}
if ($semester) {
    $sql .= " AND c.semester = '" . $connection->real_escape_string($semester) . "'";
}
if ($class_id) {
    $sql .= " AND s.class_id = '" . $connection->real_escape_string($class_id) . "'";
}

$sql .= " ORDER BY c.faculty, c.semester, s.student_name";

$result = $connection->query($sql);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=results_export_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, [
    'Student ID',
    'Student Name',
    'Faculty',
    'Semester',
    'Subject',
    'Marks Obtained',
    'Total Marks',
    'Percentage',
    'Grade'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_id'],
        $row['student_name'],
        $row['faculty'],
        $row['semester'],
        $row['subject_name'],
        $row['marks_obtained'],
        $row['total_marks'],
        $row['percentage'] . '%',
        $row['grade']
    ]);
}

fclose($output);
?>