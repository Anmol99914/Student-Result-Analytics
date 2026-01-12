<?php
// class_management.php - Class Management Page
// include('../../../config.php');
// include(__DIR__ . '/../../../config.php');
require_once '../../../config.php';


?>
<div class="class-management-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-mortarboard-fill me-2"></i>Class Management</h2>
            <p class="text-muted mb-0">Manage classes across all faculties</p>
        </div>
        <div>
            <button class="btn btn-primary" id="addClassBtn">
                <i class="bi bi-plus-circle"></i> Create New Class
            </button>
        </div>
    </div>
    
    <!-- Faculty Filter -->
    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-select" id="facultyFilter">
                <option value="">All Faculties</option>
                <option value="BCA">BCA</option>
                <option value="BBM">BBM</option>
                <option value="BIM">BIM</option>
                <option value="BBA">BBA</option>
                <option value="BIT">BIT</option>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="semesterFilter">
                <option value="">All Semesters</option>
                <?php for($i = 1; $i <= 8; $i++): ?>
                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100" id="resetFiltersBtn">
                <i class="bi bi-arrow-clockwise"></i> Reset Filters
            </button>
        </div>
    </div>
    
    <!-- Classes Container -->
    <div id="classes-container">
        <!-- Content loaded via JavaScript -->
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading classes...</p>
        </div>
    </div>
</div>

<!-- Load the separate JavaScript file -->
<script src="../../js/admin/class-management.js"></script>
<script>
// Simple check to ensure initialization
setTimeout(function() {
    if (typeof ClassManager !== 'undefined' && !window.classManager) {
        console.log('Direct initialization from class_management.php');
        window.classManager = new ClassManager();
    }
}, 500);
</script>