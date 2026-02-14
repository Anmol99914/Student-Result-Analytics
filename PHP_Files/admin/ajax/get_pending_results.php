<?php
session_start();
require_once '../../../config.php';

// Admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$status = $_GET['status'] ?? '';
$faculty = $_GET['faculty'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT r.*, 
        s.student_name, s.student_id,
        sub.subject_name,
        c.faculty,
        t.name as teacher_name
        FROM result r
        JOIN student s ON r.student_id = s.student_id
        JOIN subject sub ON r.subject_id = sub.subject_id
        JOIN class c ON s.class_id = c.class_id
        JOIN teacher t ON r.entered_by_teacher_id = t.teacher_id
        WHERE r.verification_status = 'pending'";

if ($status) {
    $sql .= " AND r.verification_status = '$status'";
}

if ($faculty) {
    $sql .= " AND c.faculty = '$faculty'";
}

if ($search) {
    $sql .= " AND (s.student_name LIKE '%$search%' OR s.student_id LIKE '%$search%')";
}

$sql .= " ORDER BY r.created_at DESC";

$result = $connection->query($sql);
$results = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);
?>