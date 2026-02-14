<?php
// File: PHP_Files/admin/pages/subject_management.php
// Purpose: Subject Management page - clean PHP only (no JS/CSS)

// Start session and check admin authentication
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Include database configuration from root folder
require_once '../../../config.php';

// Get active faculties for filter dropdown
$faculty_query = "SELECT faculty_id, faculty_name FROM faculty";
$faculty_result = mysqli_query($connection, $faculty_query);
$faculties = [];
while ($row = mysqli_fetch_assoc($faculty_result)) {
    $faculties[] = $row;
}

// Get semester options (1-8)
$semesters = range(1, 8);
?>

<!-- SUBJECT MANAGEMENT PAGE - Clean HTML with proper structure -->
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title mb-0">
                    <i class="fas fa-book-open me-2"></i>Subject Management
                </h1>
                <p class="text-muted mb-0">Manage subjects for different programs</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" id="addSubjectBtn">
                    <i class="fas fa-plus me-1"></i> Add New Subject
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <!-- <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                BCA Subjects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="bcaSubjectsStat">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-laptop-code fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div> -->
        <!-- </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                BBM Subjects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="bbmSubjectsStat">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                BIM Subjects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="bimSubjectsStat">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-network-wired fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Subjects</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalSubjectsStat">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filter Subjects
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="facultyFilter" class="form-label">Faculty</label>
                        <select class="form-control form-select" id="facultyFilter">
                            <option value="">All Faculties</option>
                            <?php foreach ($faculties as $faculty): ?>
                                <option value="<?php echo $faculty['faculty_id']; ?>">
                                    <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="semesterFilter" class="form-label">Semester</label>
                        <select class="form-control form-select" id="semesterFilter">
                            <option value="">All Semesters</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?php echo $sem; ?>">Semester <?php echo $sem; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-control form-select" id="statusFilter">
                            <!-- <option value="">All Status</option> -->
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="searchInput" class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" 
                                   placeholder="Search by name or code...">
                            <button class="btn btn-outline-secondary" type="button" id="clearFiltersBtn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Faculty Tabs -->
    <div class="mb-3">
        <ul class="nav nav-tabs" id="facultyTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-faculty="" type="button">
                    <i class="fas fa-th me-1"></i> All Subjects
                </button>
            </li>
            <?php foreach ($faculties as $index => $faculty): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-faculty="<?php echo $faculty['faculty_id']; ?>" type="button">
                        <?php 
                        $icons = ['fas fa-laptop-code', 'fas fa-chart-line', 'fas fa-network-wired'];
                        $icon = $icons[$index] ?? 'fas fa-book';
                        ?>
                        <i class="<?php echo $icon; ?> me-1"></i> 
                        <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Subjects Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="subjectsTable">
                    <thead class="thead-dark">
                        <tr>
                            <th width="50">#</th>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Faculty</th>
                            <th>Semester</th>
                            <th>Credits</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="loadingRow">
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading subjects...</span>
                                </div>
                                <p class="mt-2 mb-0">Loading subjects...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3" id="paginationSection">
                <div class="text-muted" id="paginationInfo">
                    Showing 0 to 0 of 0 subjects
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0" id="paginationControls">
                        <!-- Pagination will be loaded via JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- No Subjects Placeholder (Hidden by default) -->
<div class="text-center py-5" id="noSubjectsPlaceholder" style="display: none;">
    <div class="mb-3">
        <i class="fas fa-book-open fa-4x text-muted"></i>
    </div>
    <h3 class="text-muted mb-2">No Subjects Found</h3>
    <p class="text-muted mb-4">No subjects match your current filters. Try changing filters or add a new subject.</p>
    <button class="btn btn-primary" id="addFirstSubjectBtn">
        <i class="fas fa-plus me-1"></i> Add Your First Subject
    </button>
</div>

<!-- Add/Edit Subject Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-book me-1"></i> Add New Subject
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="subjectForm">
                    <input type="hidden" id="subjectId">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subjectName" class="form-label">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subjectName" 
                                   placeholder="e.g., Database Management System" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subjectCode" class="form-label">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subjectCode" 
                                   placeholder="e.g., BCA405" required>
                            <div class="form-text">Format: FACULTY+SEMESTER+CODE (BCA101, BBM201, BIM301)</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="facultyId" class="form-label">Faculty <span class="text-danger">*</span></label>
                            <select class="form-select" id="facultyId" required>
                                <option value="">Select Faculty</option>
                                <?php foreach ($faculties as $faculty): ?>
                                    <option value="<?php echo $faculty['faculty_id']; ?>">
                                        <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="semester" required>
                                <option value="">Select Semester</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?php echo $sem; ?>">Semester <?php echo $sem; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="credits" class="form-label">Credit Hours <span class="text-danger">*</span></label>
                            <select class="form-select" id="credits" required>
                                <option value="">Select Credits</option>
                                <option value="2">2 Credits</option>
                                <option value="3">3 Credits</option>
                                <option value="4">4 Credits</option>
                                <option value="6">6 Credits (Project/Internship)</option>
                                <option value="8">8 Credits (Comprehensive Project)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="isElective" class="form-label">Subject Type</label>
                            <select class="form-select" id="isElective" name = "is_elective" required>
                                <option value="0">Core Subject</option>
                                <option value="1">Elective Subject</option>
                            </select>
                            <label for="isActive" class="form-label">Status</label>
                            <select class="form-select" id="isActive">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" rows="3" 
                                  placeholder="Brief description of the subject..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveSubjectBtn">
                    <i class="fas fa-save me-1"></i> Save Subject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this subject?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. If this subject is assigned to teachers or students, it may cause issues.</p>
                <p>Subject: <strong id="deleteSubjectName"></strong></p>
                <p>Code: <strong id="deleteSubjectCode"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-1"></i> Delete Subject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add this CSS for the page -->
<style>
.stat-card {
    transition: transform 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
.border-left-success {
    border-left: 4px solid #1cc88a !important;
}
.border-left-info {
    border-left: 4px solid #36b9cc !important;
}
.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}
.table th {
    font-weight: 600;
}
#loadingRow td {
    background-color: #f8f9fa;
}
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>

<!-- Initialize Subject Manager -->
<!-- Load subject management JS -->
<script src="../../js/admin/subject-management.js"></script>

<!-- Simple initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Subject Management DOM ready');
    
    // Initialize immediately if SubjectManager exists
    if (typeof SubjectManager !== 'undefined') {
        console.log('SubjectManager found, initializing...');
        SubjectManager.init();
    } else {
        console.error('SubjectManager not found! Check if subject-management.js loaded.');
    }
});
</script>

<script>
// Add this after your table to test
$(document).ready(function() {
    console.log('=== DEBUG SUBJECT STATUS ===');
    $.get('../admin/api/get_subjects.php?limit=5', function(data) {
        console.log('Sample subjects:', data.data);
        data.data.forEach(subject => {
            console.log(`${subject.subject_code}: is_active = ${subject.is_active} (type: ${typeof subject.is_active})`);
        });
    });
});
</script>