<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Admin access required</div>';
    exit();
}

// Get pending results count for badge
$pending_sql = "SELECT COUNT(*) as count FROM result WHERE verification_status = 'pending'";
$pending_result = $connection->query($pending_sql);
$pending_count = $pending_result->fetch_assoc()['count'];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-primary">
                <i class="bi bi-shield-check me-2"></i> Results Verification
            </h1>
            <p class="text-muted mb-0">Review and verify marks submitted by teachers</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <div class="d-flex justify-content-between mb-3">
            <div>
            <button onclick="loadVerifiedResults()" class="btn btn-success">
                <i class="bi bi-check-circle"></i> View Verified Results
            </button>
                <button onclick="loadRejectedResults()" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> View Rejected Results
                </button>
            </div>
        </div>
        </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <!-- <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Pending Verification</h6>
                            <h3 class="mb-0"><?php echo $pending_count; ?></h3>
                        </div>
                        <div class="text-warning fs-1">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <small class="text-muted">Awaiting admin approval</small>
                </div>
            </div>
        </div> -->
        
        <!-- <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Verified Today</h6>
                            <h3 class="mb-0" id="verified-today">0</h3>
                        </div>
                        <div class="text-success fs-1">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <small class="text-muted">Approved today</small>
                </div>
            </div>
        </div>
         -->
        <!-- <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger mb-1">Rejected</h6>
                            <h3 class="mb-0" id="rejected-today">0</h3>
                        </div>
                        <div class="text-danger fs-1">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                    <small class="text-muted">Sent back for correction</small>
                </div>
            </div>
        </div> -->
<!--         
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-info mb-1">Total Verified</h6>
                            <h3 class="mb-0" id="total-verified">0</h3>
                        </div>
                        <div class="text-info fs-1">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                    <small class="text-muted">All time verified</small>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Pending Results Table -->
    <div class="card border-light shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i> Pending Results
                        <!-- <span class="badge bg-warning ms-2"><?php echo $pending_count; ?></span> -->
                    </h5>
                </div>
                <div class="d-flex gap-2">
                <select class="form-select form-select-sm" style="width: 150px;" id="filter-faculty" onchange="if(typeof filterPendingResults === 'function') { filterPendingResults(); }">
                    <option value="">All Faculties</option>
                    <option value="BCA">BCA</option>
                    <option value="BBM">BBM</option>
                    <option value="BIM">BIM</option>
                </select>
                <select class="form-select form-select-sm" style="width: 150px;" id="filter-semester" onchange="if(typeof filterPendingResults === 'function') { filterPendingResults(); }">
                    <option value="">All Semesters</option>
                    <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div id="pending-results-container">
                <?php include 'get_pending_results.php'; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Add at the TOP of your script
let updateInProgress = false;
let lastUpdateTime = 0;
const UPDATE_COOLDOWN = 1000; // 1 second cooldown
// ===== VERIFICATION FUNCTIONS - MUST BE HERE =====
// These need to be defined IN THIS PAGE since admin-main.js loads async

console.log('🔧 Defining verification functions in page scope...');

// ===== CORE VERIFICATION FUNCTIONS =====

// View result details
function viewResultDetails(resultId) {
    console.log('📋 Viewing result:', resultId);
    
    fetch('Results/get_result_details.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'result_id=' + resultId
    })
    .then(response => response.text())
    .then(html => {
        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = html;
        document.body.appendChild(modal);
        
        // Show modal
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
        
        // Remove modal after hiding
        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading details: ' + error.message);
    });
}

// Verify result
function verifyResult(resultId) {
    console.log('✅ Verifying result:', resultId);
    
    if (confirm('Verify this result? Once verified, teacher cannot edit it.')) {
        updateVerificationStatus(resultId, 'verified');
    }
}

// Reject result
// Reject result - FIXED VERSION
function rejectResult(resultId) {
    console.log('❌ Rejecting result:', resultId);
    
    const reason = prompt('Enter rejection reason (optional):');
    
    // ✅ If user cancels prompt, do nothing
    if (reason === null) {
        return;
    }
    
    // Show loading on button
    const btn = document.querySelector(`[onclick*="rejectResult(${resultId})"]`);
    if (btn) {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        
        // Store original HTML to restore later
        btn.dataset.originalHtml = originalHTML;
    }
    
    fetch('Results/update_verification.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'result_id=' + resultId + '&status=rejected&reason=' + encodeURIComponent(reason)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Reject response:', data);
        
        // Restore button
        if (btn) {
            btn.innerHTML = btn.dataset.originalHtml || '<i class="bi bi-x"></i>';
            btn.disabled = false;
        }
        
        if (data.success) {
            // Show ONE alert - this is the only one!
            alert('Result rejected!');
            
            // Remove the row
            const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
            if (row) {
                row.style.transition = 'all 0.3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    
                    // Update counters
                    if (typeof updateCounters === 'function') {
                        updateCounters();
                    }
                    
                    // Check if table is empty
                    const tbody = document.querySelector('#pending-results-table tbody');
                    if (tbody && tbody.children.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-check-circle display-4 text-success"></i>
                                    <h5 class="mt-3">All pending results verified!</h5>
                                </td>
                            </tr>
                        `;
                    }
                }, 300);
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to reject result');
        
        // Restore button on error
        if (btn) {
            btn.innerHTML = btn.dataset.originalHtml || '<i class="bi bi-x"></i>';
            btn.disabled = false;
        }
    });
}

// ===== MAIN UPDATE FUNCTION =====
// Updated updateVerificationStatus function
function updateVerificationStatus(resultId, status, reason = '') {
    console.log('🔄 Updating result', resultId, 'to', status);
    
    // Show loading on buttons
    const buttons = document.querySelectorAll(`button[onclick*="${resultId}"]`);
    const originalContents = [];
    
    buttons.forEach((btn, index) => {
        originalContents[index] = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
    });
    
    fetch('Results/update_verification.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'result_id=' + resultId + '&status=' + status + '&reason=' + encodeURIComponent(reason)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        
        // Restore buttons
        buttons.forEach((btn, index) => {
            btn.innerHTML = originalContents[index];
            btn.disabled = false;
        });
        
        if (data.success) {
            // Show success message
            showToast('success', `Result ${status} successfully!`);
            
            // Remove the row
            const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
            if (row) {
                // Fade out animation
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '0';
                row.style.maxHeight = '0';
                row.style.overflow = 'hidden';
                row.style.paddingTop = '0';
                row.style.paddingBottom = '0';
                row.style.margin = '0';
                row.style.border = 'none';
                
                setTimeout(() => {
                    row.remove();
                    
                    // 🔥 CRITICAL: Update ALL counters immediately
                    updateAllCounters();
                    
                    // Check if table is empty
                    const tbody = document.querySelector('#pending-results-table tbody');
                    if (tbody && tbody.children.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="py-4">
                                        <i class="bi bi-check-circle display-1 text-success opacity-50"></i>
                                        <h4 class="mt-3 text-success">All Caught Up!</h4>
                                        <p class="text-muted">No pending results to verify.</p>
                                        <button onclick="loadPendingResults()" class="btn btn-outline-success btn-sm mt-2">
                                            <i class="bi bi-arrow-clockwise"></i> Refresh
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                }, 500);
            } else {
                // If row not found, refresh the whole table
                setTimeout(() => {
                    loadPendingResults();
                    updateAllCounters();
                }, 1000);
            }
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Failed to update verification status');
        
        // Restore buttons on error
        buttons.forEach((btn, index) => {
            btn.innerHTML = originalContents[index];
            btn.disabled = false;
        });
    });
}

// NEW FUNCTION: Update ALL counters
function updateAllCounters() {
    console.log('🔄 Updating all counters...');
    
    // 1. Update pending count (sidebar and page)
    updatePendingCount();
    
    // 2. Update stats (verified today, rejected today, total verified)
    updateStats();
    
    // 3. Also update any other counters
    updatePageCounters();
}

// NEW FUNCTION: Update page-specific counters
function updatePageCounters() {
    // Count remaining rows in table
    const rows = document.querySelectorAll('#pending-results-table tbody tr[data-result-id]');
    const pendingOnPage = rows.length;
    
    // Update the "Showing X results" text if it exists
    const showingText = document.querySelector('.showing-results');
    if (showingText) {
        showingText.textContent = `Showing ${pendingOnPage} pending result${pendingOnPage !== 1 ? 's' : ''}`;
    }
    
    console.log(`📊 Page counters: ${pendingOnPage} rows remaining`);
}

// ===== COUNTER FUNCTIONS =====

// Update pending count (FIXED - without 's')
function updatePendingCount() {
    const now = Date.now();
    
    // Prevent rapid successive calls
    if (updateInProgress || (now - lastUpdateTime) < UPDATE_COOLDOWN) {
        console.log('⏳ Update skipped - too soon or in progress');
        return;
    }
    
    updateInProgress = true;
    lastUpdateTime = now;
    
    console.log('🔄 Updating pending count...');
    
    fetch('Results/get_pending_count.php', {
        method: 'GET',
        headers: {'Cache-Control': 'no-cache'}
    })
    .then(response => response.json())
    .then(data => {
        console.log('📊 Pending count:', data.count);
        
        if (data.success) {
            // Update sidebar badge
            try {
                const badge = document.getElementById('pending-count');
                if (badge) {
                    badge.textContent = data.count;
                    badge.className = `badge ${data.count > 0 ? 'bg-danger' : 'bg-success'}`;
                }
            } catch(e) {
                console.log('Parent access error:', e.message);
            }
            
            // Update page display
            const display = document.getElementById('pending-count-display');
            if (display) {
                display.textContent = data.count;
            }
        }
        
        updateInProgress = false;
    })
    .catch(error => {
        console.error('Count error:', error);
        updateInProgress = false;
    });
}

// Update stats (including verified/rejected counts)
function updateStats() {
    console.log('📊 Updating stats...');
    
    fetch('Results/get_verification_stats.php', {
        method: 'GET',
        headers: {'Cache-Control': 'no-cache'}
    })
    .then(response => response.json())
    .then(data => {
        console.log('📊 Stats received:', data);
        
        if (data.success) {
            // Update all counters
            ['verified-today', 'rejected-today', 'total-verified', 'pending-count-display']
                .forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const value = data[id.replace('-', '_')] || data[id] || 0;
                        element.textContent = value;
                    }
                });
        }
    })
    .catch(error => console.error('Stats error:', error));
}
// ===== TABLE FUNCTIONS =====

// Load pending results with filters
function loadPendingResults() {
    const faculty = document.getElementById('filter-faculty')?.value || '';
    const semester = document.getElementById('filter-semester')?.value || '';
    const container = document.getElementById('pending-results-container');
    
    if (!container) return;
    
    console.log('📋 Loading results with filters:', faculty, semester);
    
    // Show loading
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Loading...</div>';
    
    fetch('Results/get_pending_results.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `faculty=${faculty}&semester=${semester}`
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
        // Update stats after loading new results
        updateStats();
    })
    .catch(error => {
        container.innerHTML = '<div class="alert alert-danger m-3">Failed to load results</div>';
    });
}

// Filter pending results
function filterPendingResults() {
    console.log('🔍 Filtering results...');
    loadPendingResults();
}

// ===== UI FUNCTIONS =====

// Toast notification
function showToast(type, message) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.verification-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    const toastHtml = `
        <div class="verification-toast position-fixed" style="top: 80px; right: 20px; z-index: 9999;">
            <div class="toast show" role="alert">
                <div class="toast-header bg-${type} text-white">
                    <i class="bi bi-${icons[type] || 'info-circle'} me-2"></i>
                    <strong class="me-auto">Verification</strong>
                    <button type="button" class="btn-close btn-close-white" onclick="this.closest('.verification-toast').remove()"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const toast = document.querySelector('.verification-toast');
        if (toast) toast.remove();
    }, 3000);
}

// ===== INITIALIZATION =====

// Make functions available globally
window.viewResultDetails = viewResultDetails;
window.verifyResult = verifyResult;
window.rejectResult = rejectResult;
window.updateVerificationStatus = updateVerificationStatus;
window.updatePendingCount = updatePendingCount;  // Fix: Added this line
window.updateStats = updateStats;
window.loadPendingResults = loadPendingResults;
window.filterPendingResults = filterPendingResults;
window.showToast = showToast;

console.log('Verification functions ready!');
console.log('Available functions:', {
    viewResultDetails: typeof viewResultDetails,
    verifyResult: typeof verifyResult,
    rejectResult: typeof rejectResult,
    updatePendingCount: typeof updatePendingCount,
    updateStats: typeof updateStats
});

// Load stats on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 Verification page loaded');
     // Initial load - only once
     updatePendingCount();
    updateStats();
    
    // Setup filter events
    const facultyFilter = document.getElementById('filter-faculty');
    const semesterFilter = document.getElementById('filter-semester');
    
    if (facultyFilter) {
        facultyFilter.addEventListener('change', loadPendingResults);
    }
    if (semesterFilter) {
        semesterFilter.addEventListener('change', loadPendingResults);
    }
});

</script>


<style>
.result-row:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}
.verification-actions {
    opacity: 0.7;
    transition: opacity 0.2s;
}
.result-row:hover .verification-actions {
    opacity: 1;
}
</style>