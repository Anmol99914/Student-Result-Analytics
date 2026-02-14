<?php
// add_class.php
session_start();
include('../../config.php');

header('Content-Type: application/json');

// Debug - log the request
error_log("add_class.php called - Method: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get and validate inputs
$faculty = trim($_POST['faculty'] ?? '');
$semester = isset($_POST['semester']) ? intval($_POST['semester']) : 0;
$batch_year = trim($_POST['batch_year'] ?? date('Y'));
$status = trim($_POST['status'] ?? 'active');

error_log("Received data - Faculty: $faculty, Semester: $semester, Batch: $batch_year");

// Validate
if (empty($faculty)) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty is required']);
    exit;
}

if ($semester <= 0 || $semester > 8) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid semester (must be 1-8)']);
    exit;
}

if (empty($batch_year) || !is_numeric($batch_year) || strlen($batch_year) != 4) {
    $batch_year = date('Y');
}

// Check if class already exists
$check_query = "SELECT class_id FROM class 
                WHERE faculty = ? AND semester = ? AND batch_year = ?";
$check_stmt = $connection->prepare($check_query);

if (!$check_stmt) {
    error_log("Prepare failed (check): " . $connection->error);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$check_stmt->bind_param("sis", $faculty, $semester, $batch_year);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Class already exists for this faculty, semester, and batch'
    ]);
    $check_stmt->close();
    exit;
}
$check_stmt->close();

// Insert new class
$insert_query = "INSERT INTO class (faculty, semester, batch_year, status, created_at) 
                 VALUES (?, ?, ?, ?, NOW())";
$insert_stmt = $connection->prepare($insert_query);

if (!$insert_stmt) {
    error_log("Prepare failed (insert): " . $connection->error);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$insert_stmt->bind_param("siss", $faculty, $semester, $batch_year, $status);

if ($insert_stmt->execute()) {
    $class_id = $connection->insert_id;
    
    error_log("Class created successfully - ID: $class_id");
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Class created successfully!',
        'class_id' => $class_id,
        'faculty' => $faculty,
        'semester' => $semester,
        'batch_year' => $batch_year
    ]);
} else {
    error_log("Insert failed: " . $insert_stmt->error);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to create class: ' . $insert_stmt->error
    ]);
}

$insert_stmt->close();
?>