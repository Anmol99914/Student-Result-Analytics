<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    die('Access denied');
}

$class_id = $_GET['class_id'] ?? 0;
$subject_id = $_GET['subject_id'] ?? 0;

// Get students
$sql = "SELECT student_id, student_name FROM student WHERE class_id = ? ORDER BY student_id";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$students = $stmt->get_result();

// Create CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="marks_template.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Student ID', 'Student Name', 'Marks (0-100)']);

while($student = $students->fetch_assoc()) {
    fputcsv($output, [
        $student['student_id'],
        $student['student_name'],
        '' // Empty marks column
    ]);
}

fclose($output);
?>