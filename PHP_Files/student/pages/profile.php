<?php
// File: PHP_Files/student/pages/profile.php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];

// Fetch student data with CORRECT column names
$stmt = $connection->prepare("
    SELECT s.student_id, s.student_name, s.email, s.phone_number, 
           s.admission_year, s.is_active, s.created_at, s.batch_code,
           c.faculty, c.semester, 
           sem.semester_name
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    LEFT JOIN semester sem ON s.semester_id = sem.semester_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found!</div>';
    exit;
}

// Format dates
$admission_date = $student['admission_year'];
$created_date = date('d M Y', strtotime($student['created_at']));
?>

<div class="container-fluid">
    <!-- Profile Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg bg-white bg-opacity-25 p-3 rounded-circle">
                                <i class="bi bi-person-circle fs-1"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h1 class="card-title mb-1"><?php echo htmlspecialchars($student['student_name']); ?></h1>
                            <p class="card-text opacity-75 mb-0">
                                <i class="bi bi-award me-1"></i>
                                <?php echo htmlspecialchars($student['faculty']); ?> • 
                                Semester <?php echo $student['semester']; ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-white text-primary fs-6 p-2">
                                ID: <?php echo htmlspecialchars($student_id); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Content -->
    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-vcard text-primary me-2"></i>
                        Personal Information
                    </h5>
                    
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Full Name</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($student['student_name']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Admission Year</label>
                            <div class="form-control bg-light"><?php echo $student['admission_year']; ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Email Address</label>
                            <div class="form-control bg-light student-email">
                                <?php echo htmlspecialchars($student['email']); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Phone Number</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($student['phone_number'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Batch Code</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($student['batch_code']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Account Created</label>
                            <div class="form-control bg-light"><?php echo $created_date; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Academic Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-mortarboard text-success me-2"></i>
                        Academic Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Student ID</span>
                            <strong><?php echo htmlspecialchars($student_id); ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Faculty</span>
                            <strong><?php echo htmlspecialchars($student['faculty']); ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Semester</span>
                            <strong><?php echo htmlspecialchars($student['semester_name']); ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Admission Year</span>
                            <strong><?php echo $student['admission_year']; ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-<?php echo ($student['is_active'] == 1) ? 'success' : 'danger'; ?>">
                                <?php echo ($student['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
