<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

$sql = "SELECT DISTINCT 
            c.class_id, 
            CONCAT(c.faculty, ' - Semester ', c.semester, ' (', c.batch_year, ')') as class_name,
            s.subject_id,
            s.subject_name,
            s.subject_code
        FROM teacher_subject_assignment tsa
        JOIN class c ON tsa.class_id = c.class_id
        JOIN subject s ON tsa.subject_id = s.subject_id
        WHERE tsa.teacher_id = ?
        ORDER BY c.faculty, c.semester, s.subject_name";

$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    if (!isset($data[$row['class_id']])) {
        $data[$row['class_id']] = [
            'class_name' => $row['class_name'],
            'subjects' => []
        ];
    }
    $data[$row['class_id']]['subjects'][] = [
        'id' => $row['subject_id'],
        'name' => $row['subject_name'],
        'code' => $row['subject_code']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>