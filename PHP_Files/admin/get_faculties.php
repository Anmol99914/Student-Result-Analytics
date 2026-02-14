<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$query = "SELECT faculty_id, faculty_code, faculty_name FROM faculty WHERE status = 'active' ORDER BY faculty_code";
$result = $connection->query($query);

$faculties = [];
while ($row = $result->fetch_assoc()) {
    $faculties[] = [
        'id' => $row['faculty_id'],
        'faculty_code' => $row['faculty_code'],
        'faculty_name' => $row['faculty_name']
    ];
}

echo json_encode($faculties);
?>