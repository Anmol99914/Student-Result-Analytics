<?php
// teacher_management.php - SIMPLE VERSION
?>
<div class="container-fluid p-4">
    <div class="teacher-management-page">
        <div id="teachers-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading teacher management system...</p>
            </div>
        </div>
    </div>
    <!-- Assign Subjects Modal -->
<div class="modal fade" id="assignSubjectsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-book"></i> Assign Subjects
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignSubjectsModalBody">
                <!-- Content loaded via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Load teacher management JS - FORCE LOAD EVEN IF EXISTS -->
<script>
// Remove any existing teacherManager
window.teacherManager = null;
</script>

<script src="/Student_Result_Analytics/js/admin/teacher-management.js?v=<?php echo time(); ?>"></script>

<script>
// Wait for script to load
console.log('Waiting for teacherManager...');

function waitForTeacherManager() {
    if (window.teacherManager) {
        console.log('✅ teacherManager loaded, initializing...');
        window.teacherManager.init();
        return true;
    }
    console.log('⏳ Still waiting for teacherManager...');
    return false;
}

// Check every 100ms for 2 seconds
let attempts = 0;
const interval = setInterval(function() {
    attempts++;
    if (window.teacherManager) {
        clearInterval(interval);
        window.teacherManager.init();
    } else if (attempts > 20) { // 2 seconds timeout
        clearInterval(interval);
        console.error('❌ teacherManager failed to load');
        document.getElementById('teachers-container').innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                Failed to load teacher management. 
                <button class="btn btn-sm btn-danger ms-2" onclick="location.reload()">
                    Refresh Page
                </button>
            </div>
        `;
    }
}, 100);
</script>