<?php
// File: PHP_Files/admin/api/add_subject.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include config from root folder
require_once '../../../config.php';

// Check if connection exists
if (!isset($connection) || !$connection) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Default response
$response = ['success' => false, 'message' => ''];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $required = ['subject_name', 'subject_code', 'faculty_id', 'semester', 'credits'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Sanitize inputs - CHANGE $conn TO $connection
    $subject_name = mysqli_real_escape_string($connection, trim($data['subject_name']));
    $subject_code = mysqli_real_escape_string($connection, strtoupper(trim($data['subject_code'])));
    $faculty_id = intval($data['faculty_id']);
    $semester = intval($data['semester']);
    $credits = intval($data['credits']);
    $is_elective = isset($data['is_elective']) ? intval($data['is_elective']) : 0;
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
    $description = isset($data['description']) ? mysqli_real_escape_string($connection, trim($data['description'])) : '';
    
    // Validate faculty exists - CHANGE $conn TO $connection
    $facultyCheck = mysqli_query($connection, "SELECT faculty_id FROM faculty WHERE faculty_id = $faculty_id");
    if (mysqli_num_rows($facultyCheck) == 0) {
        throw new Exception('Invalid faculty selected');
    }
    
    // Check if subject code already exists - CHANGE $conn TO $connection
    $codeCheck = mysqli_query($connection, "SELECT subject_id FROM subject WHERE subject_code = '$subject_code'");
    if (mysqli_num_rows($codeCheck) > 0) {
        throw new Exception("Subject code '$subject_code' already exists");
    }
    
    // Validate semester (1-8)
    if ($semester < 1 || $semester > 8) {
        throw new Exception('Semester must be between 1 and 8');
    }
    
    // Validate credits (2,3,4,6,8)
    $validCredits = [2, 3, 4, 6, 8];
    if (!in_array($credits, $validCredits)) {
        throw new Exception('Credits must be 2, 3, 4, 6, or 8');
    }
    
    // Prepare SQL insert - CHANGE $conn TO $connection
    $sql = "INSERT INTO subject (
                subject_name, 
                subject_code, 
                faculty_id, 
                semester, 
                credits,
                is_elective, 
                description, 
                is_active,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        throw new Exception('Database error: ' . mysqli_error($connection));
    }
    
    mysqli_stmt_bind_param($stmt, 'ssiiiisi', 
        $subject_name, 
        $subject_code, 
        $faculty_id, 
        $semester, 
        $credits, 
        $is_elective,
        $description, 
        $is_active
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $subject_id = mysqli_insert_id($connection);
        
        $response['success'] = true;
        $response['message'] = 'Subject added successfully';
        $response['subject_id'] = $subject_id;
        $response['subject_code'] = $subject_code;
    } else {
        throw new Exception('Failed to add subject: ' . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
} finally {
    if (isset($connection)) {
        mysqli_close($connection);
    }
}

echo json_encode($response);
?>