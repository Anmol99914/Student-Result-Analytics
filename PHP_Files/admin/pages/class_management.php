<?php

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
            <?php
            // Fetch all active faculties from database
            $faculty_query = "SELECT faculty_code, faculty_name FROM faculty WHERE status = 'active' ORDER BY faculty_code";
            $faculty_result = $connection->query($faculty_query);

            if (!$faculty_result) {
                echo "<!-- ERROR: " . $connection->error . " -->";
            } else {
                echo "<!-- DEBUG: Found " . $faculty_result->num_rows . " faculties -->";
            }
            
            if ($faculty_result && $faculty_result->num_rows > 0) {
                while ($faculty = $faculty_result->fetch_assoc()) {
                    $faculty_code = htmlspecialchars($faculty['faculty_code']);
                    $faculty_name = htmlspecialchars($faculty['faculty_name']);
                    echo "<option value=\"$faculty_code\">$faculty_name ($faculty_code)</option>";
                }
            } 
            // else {
            //     // Fallback if no faculties in database
            //     echo '<option value="BCA">BCA - Bachelor of Computer Applications</option>';
            //     echo '<option value="BBM">BBM - Bachelor of Business Management</option>';
            //     echo '<option value="BIM">BIM - Bachelor of Information Management</option>';
            // }
            ?>
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