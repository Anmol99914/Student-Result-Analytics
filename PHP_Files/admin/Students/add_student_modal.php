<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    exit();
}

// Get classes for dropdown
$classes = $connection->query("SELECT class_id, faculty, semester FROM class WHERE status = 'active' ORDER BY faculty, semester");
?>
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i> Add New Student
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="student_id" required 
                                   placeholder="e.g., BCA001, BSC.CSIT10">
                            <small class="text-muted">Unique identifier for the student</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="student_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required placeholder = "Enter password">
                            <small class="text-muted">
                                <i class="bi bi-shield-lock"></i> Min. 6 characters
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id">
                                <option value="">-- Select Class --</option>
                                <?php while($class = $classes->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>">
                                    <?= $class['faculty'] ?> - Semester <?= $class['semester'] ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="phone_number" maxlength="10">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Year</label>
                            <input type="number" class="form-control" name="admission_year" value="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Batch Code</label>
                            <input type="text" class="form-control" name="batch_code" value="<?= date('Y') . '-' . (date('Y')+4) ?>">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Student will be created with Active status by default.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="window.studentManager.submitAddStudent()">
                    <i class="bi bi-check-circle"></i> Add Student
                </button>
            </div>
        </div>
    </div>
</div>