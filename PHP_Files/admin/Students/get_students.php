<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode([]);
    exit();
}

$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Get students with their LATEST payment status
$sql = "SELECT s.*, c.faculty, c.semester,
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

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'student_id' => $row['student_id'],
        'student_name' => $row['student_name'],
        'email' => $row['email'],
        'faculty' => $row['faculty'] ?? 'N/A',
        'semester' => $row['semester'] ?? 'N/A',
        'is_active' => (int)$row['is_active'],
        'payment_status' => $row['payment_status'] ?? 'Unpaid',
        'due_amount' => (float)($row['due_amount'] ?? 0),
        'payment_date' => $row['payment_date'] ?? null,
        'phone_number' => $row['phone_number'] ?? '',
        'admission_year' => $row['admission_year'] ?? '',
        'batch_code' => $row['batch_code'] ?? ''
    ];
}

echo json_encode($students);
?>