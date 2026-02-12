<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    exit();
}

$student_id = $_GET['student_id'] ?? '';
if (!$student_id) exit();

// Get student details
$query = "SELECT s.*, c.faculty, c.semester 
          FROM student s
          LEFT JOIN class c ON s.class_id = c.class_id
          WHERE s.student_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Get classes for dropdown
$classes = $connection->query("SELECT class_id, faculty, semester FROM class WHERE status = 'active' ORDER BY faculty, semester");
?>

<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Edit Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editStudentForm">
                    <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-control" value="<?= $student['student_id'] ?>" readonly disabled>
                            <small class="text-muted">Student ID cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="student_name" 
                                   value="<?= htmlspecialchars($student['student_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($student['email']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" 
                                   value="<?= $student['phone_number'] ?? '' ?>" maxlength="10">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id">
                                <option value="">-- Select Class --</option>
                                <?php while($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>" 
                                    <?= $student['class_id'] == $class['class_id'] ? 'selected' : '' ?>>
                                    <?= $class['faculty'] ?> - Semester <?= $class['semester'] ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active">
                                <option value="1" <?= $student['is_active'] ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= !$student['is_active'] ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Year</label>
                            <input type="number" class="form-control" name="admission_year" 
                                   value="<?= $student['admission_year'] ?? date('Y') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Batch Code</label>
                            <input type="text" class="form-control" name="batch_code" 
                                   value="<?= $student['batch_code'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" 
                               placeholder="Leave blank to keep current password">
                        <small class="text-muted">Only enter if you want to change the password</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="window.studentManager.submitEditStudent()">
                    <i class="bi bi-check-circle"></i> Update Student
                </button>
            </div>
        </div>
    </div>
</div>