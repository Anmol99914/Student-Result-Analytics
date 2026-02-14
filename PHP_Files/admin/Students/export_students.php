<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    die('Unauthorized');
}

$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Same query as get_students.php
$sql = "SELECT s.student_id, s.student_name, s.email, s.phone_number, 
               c.faculty, c.semester, s.admission_year, s.is_active,
               p.payment_status, p.due_amount, p.payment_date
        FROM student s
        LEFT JOIN class c ON s.class_id = c.class_id
        LEFT JOIN (
            SELECT p1.* 
            FROM payment p1
            INNER JOIN (
                SELECT student_id, MAX(payment_id) as max_id
                FROM payment
                GROUP BY student_id
            ) p2 ON p1.payment_id = p2.max_id
        ) p ON s.student_id = p.student_id
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
    $sql .= " AND s.is_active = ?";
    $params[] = ($status === 'active') ? 1 : 0;
    $types .= 'i';
}

if ($search) {
    $sql .= " AND (s.student_id LIKE ? OR s.student_name LIKE ? OR s.email LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

$sql .= " ORDER BY s.student_id";

$stmt = $connection->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students_export_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, [
    'Student ID',
    'Name',
    'Email',
    'Phone',
    'Faculty',
    'Semester',
    'Admission Year',
    'Status',
    'Payment Status',
    'Due Amount',
    'Last Payment'
]);

// Add data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_id'],
        $row['student_name'],
        $row['email'],
        $row['phone_number'] ?? '',
        $row['faculty'] ?? '',
        $row['semester'] ?? '',
        $row['admission_year'] ?? '',
        $row['is_active'] == 1 ? 'Active' : 'Inactive',
        $row['payment_status'] ?? 'Unpaid',
        $row['due_amount'] ?? 0,
        $row['payment_date'] ?? ''
    ]);
}

fclose($output);
?>