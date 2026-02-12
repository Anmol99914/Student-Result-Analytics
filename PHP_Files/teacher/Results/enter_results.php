<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

require_once '../../../config.php';

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
?>
<div class="container-fluid">
    <!-- Results Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i> Enter Results</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Select a class to enter marks for students. Marks will be sent for admin verification.</p>
                    
                    <!-- Classes Container -->
                    <div id="classes-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading your classes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- So,we're Passing PHP data to JavaScript:) -->
<script>
// ===== PASS TEACHER DATA =====
window.TEACHER_DATA = {
    id: <?php echo $teacher_id; ?>,
    name: '<?php echo addslashes($teacher_name); ?>'
};

console.log('Enter Results page loaded, TEACHER_DATA:', window.TEACHER_DATA);

// ===== INITIALIZE IMMEDIATELY =====
// Don't wait for DOMContentLoaded - it already fired
setTimeout(function() {
    console.log('Initializing results system...');
    
    if (typeof ResultsSystem !== 'undefined' && typeof ResultsSystem.init === 'function') {
        console.log('Calling ResultsSystem.init()');
        ResultsSystem.init();
    } else {
        console.error('ResultsSystem not found or init() not a function');
        console.log('ResultsSystem:', typeof ResultsSystem);
        console.log('ResultsSystem.init:', typeof ResultsSystem?.init);
        
        // Show error to user
        const container = document.getElementById('classes-container');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> JavaScript Error</h5>
                    <p>Results system not loaded properly. Please refresh the page.</p>
                    <button class="btn btn-sm btn-primary" onclick="window.location.reload()">
                        Refresh Page
                    </button>
                </div>
            `;
        }
    }
}, 100);
</script>

<!-- Load JavaScript files -->
<script src="/Student_Result_Analytics/js/results-main.js"></script>
<script src="/Student_Result_Analytics/js/results-marks.js"></script>