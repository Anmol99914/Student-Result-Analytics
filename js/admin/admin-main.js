// ===== SILENCE HARMLESS SCRIPT ERRORS =====
window.addEventListener('error', function(e) {
    // Ignore generic script errors (they're harmless)
    if (e.message === 'Script error.' || e.message === 'Script error' || !e.message) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
    return true;
}, true);

window.addEventListener('unhandledrejection', function(e) {
    // Silently ignore unhandled rejections
    e.preventDefault();
    return false;
});

// Store original console methods
const originalError = console.error;
const originalWarn = console.warn;

// Filter console errors
console.error = function() {
    const args = Array.from(arguments);
    const msg = args.join(' ');
    
    // Ignore common harmless errors
    if (msg.includes('Script error') || 
        msg.includes('ResizeObserver') ||
        msg.includes('Highcharts') ||
        msg.includes('accessibility')) {
        return;
    }
    originalError.apply(console, arguments);
};

console.warn = function() {
    const args = Array.from(arguments);
    const msg = args.join(' ');
    
    // Ignore Highcharts warnings
    if (msg.includes('Highcharts') || msg.includes('accessibility')) {
        return;
    }
    originalWarn.apply(console, arguments);
};

// File: js/admin/admin-main.js
// Purpose: Core navigation and utility functions for admin panel

// ===== CORE FUNCTIONS =====:)

// Global function to load any page via AJAX
function loadPage(url) {
    console.log('Loading page:', url);
    const mainContent = document.getElementById('main-content');
    
    if (!mainContent) {
        console.error('Main content container not found!');
        window.location.href = url; // Fallback to normal navigation
        return;
    }
    
    // Remove existing management scripts to prevent duplicate
    const scriptsToRemove = [
        'script[src*="class-management.js"]',
        'script[src*="teacher-management.js"]',
        'script[src*="assign-teachers.js"]',
        'script[src*="subject-management.js"]',
        'script[src*="student-management.js"]' 
    ];
    
    scriptsToRemove.forEach(selector => {
        const existingScript = document.querySelector(selector);
        if (existingScript) {
            existingScript.remove();
            console.log('Removed duplicate script:', selector);
        }
    });
    
    // Show loading
    mainContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(html => {
            // Set HTML first
            mainContent.innerHTML = html;
            console.log('Page loaded successfully:', url);
            
            // Execute scripts in the loaded content
            executeScriptsInContent(mainContent);
            
            // Initialize Bootstrap components
            initBootstrapComponents();
            
            // Dispatch page loaded event
            window.dispatchEvent(new CustomEvent('pageLoaded', { 
                detail: { url, content: html } 
            }));
        })
        .catch(error => {
            // Don't show error for script errors
            if (error.message && error.message.includes('Script')) {
                console.debug('Script load ignored');
                return;
            }
            console.error('Error loading page:', error);
            mainContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Error loading content: ${error.message}
                    <div class="mt-2">
                        <a href="${url}" class="btn btn-sm btn-danger">Try Again</a>
                        <button onclick="location.reload()" class="btn btn-sm btn-secondary">Reload Page</button>
                    </div>
                </div>
            `;
        });
}

// Function to execute scripts in dynamically loaded content
function executeScriptsInContent(container) {
    console.log('Executing scripts in loaded content...');
    
    // Find all script tags in the container
    const scripts = container.querySelectorAll('script');
    
    scripts.forEach(script => {
        console.log('Found script:', script.src || 'inline script');
        
        if (script.src) {
            console.log('Loading external script:', script.src);
            const newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.async = false;
            newScript.onerror = function() {
                // Silently ignore script load errors
                console.debug('Script load ignored:', script.src);
            };
            document.head.appendChild(newScript);
        } else {
            // Inline script
            try {
                console.log('Executing inline script...');
                eval(script.textContent);
                console.log('✅ Inline script executed successfully');
            } catch (error) {
                // Silently ignore eval errors
                console.debug('Inline script execution ignored');
            }
        }
    });
    // Re-initialize Bootstrap for any new components
    setTimeout(initBootstrapComponents, 100);
}

// Helper function to load external scripts
function loadExternalScript(src) {
    return new Promise((resolve, reject) => {
        // Check if already loaded
        const existing = Array.from(document.querySelectorAll('script'))
            .find(s => s.src === src);
        
        if (existing) {
            console.log('Script already loaded:', src);
            resolve();
            return;
        }
        
        const script = document.createElement('script');
        script.src = src;
        script.async = false;
        
        script.onload = () => {
            console.log('Script loaded successfully:', src);
            resolve();
        };
        
        script.onerror = (error) => {
            console.debug('Script load failed (ignored):', src);
            resolve(); // Resolve anyway to not break flow
        };
        
        document.head.appendChild(script);
    });
}

// Initialize Bootstrap components
function initBootstrapComponents() {
    try {
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.id && !bootstrap.Modal.getInstance(modal)) {
                new bootstrap.Modal(modal);
            }
        });
        
        // Dropdowns
        const dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(dropdown => {
            if (!bootstrap.Dropdown.getInstance(dropdown)) {
                new bootstrap.Dropdown(dropdown);
            }
        });
    } catch (e) {
        // Ignore Bootstrap initialization errors
    }
}

// Show alert message
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-toast');
    existingAlerts.forEach(alert => alert.remove());
    
    // Set icon and color
    let icon, alertClass;
    switch(type) {
        case 'success':
            icon = 'check-circle';
            alertClass = 'alert-success';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            alertClass = 'alert-warning';
            break;
        case 'danger':
            icon = 'x-circle';
            alertClass = 'alert-danger';
            break;
        default:
            icon = 'info-circle';
            alertClass = 'alert-info';
    }
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-toast alert-dismissible fade show" 
             role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-toast');
        if (alert) alert.remove();
    }, 3000);
}

// ===== PAGE LOADING FUNCTIONS =====

// Load teacher management
function loadTeacherManagement() {
    console.log('Loading teacher management:)');
    loadPage('pages/teacher_management.php');
}

// Load class management
function loadClassManagement() {
    console.log('Loading class management:)');
    loadPage('pages/class_management.php');
}

// Function to load Student Management
function loadStudentManagement() {
    console.log('Loading student management:)');
    loadPage('pages/manage_students.php');
}

// Load dashboard/home
function loadDashboard() {
    console.log('Loading dashboard:)');
    loadPage('pages/home.php');
}

// Load subject management
function loadSubjectManagement() {
    console.log('Loading subject management:)');
    loadPage('pages/subject_management.php');
}

// ===== RESULTS VERIFICATION SYSTEM =====

// Load Results Verification
function loadResultsVerification() {
    console.log('Loading Results Verification...');
    loadPage('Results/pending_results.php');
    
    // Update pending count after a delay
    setTimeout(() => {
        updatePendingCount();
    }, 1000);
}

// Load rejected results
function loadRejectedResults() {
    console.log('Loading rejected results...');
    loadPage('Results/view_rejected.php');
}
window.loadRejectedResults = loadRejectedResults;

// Pending count function
function updatePendingCount() {
    fetch('Results/get_pending_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('pending-count');
                if (badge) {
                    badge.textContent = data.count;
                    badge.className = `badge ${data.count > 0 ? 'bg-danger' : 'bg-success'}`;
                }
            }
        })
        .catch(error => console.debug('Pending count error (ignored)'));
}

// ===== VERIFICATION FUNCTIONS =====

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
        console.debug('View result error (ignored)');
    });
}

function verifyResult(resultId) {
    console.log('Verifying result:', resultId);
    
    if (confirm('Verify this result? Once verified, teacher cannot edit it.')) {
        // Find and disable the button
        const btn = document.querySelector(`button[onclick*="verifyResult(${resultId})"]`);
        if (btn) {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;
        }
        
        fetch('Results/update_verification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'result_id=' + resultId + '&status=verified'
        })
        .then(response => response.json())
        .then(data => {
            // Restore button
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.disabled = false;
            }
            
            if (data.success) {
                alert('Result verified successfully!');
                
                // Remove the row
                const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
                if (row) {
                    row.remove();
                    updatePendingCount();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.debug('Verify error (ignored)');
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.disabled = false;
            }
        });
    }
}

function rejectResult(resultId) {
    console.log('Rejecting result:', resultId);
    
    const reason = prompt('Enter rejection reason (optional):');
    if (reason !== null) {
        // Find and disable the button
        const btn = document.querySelector(`button[onclick*="rejectResult(${resultId})"]`);
        if (btn) {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;
        }
        
        fetch('Results/update_verification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'result_id=' + resultId + '&status=rejected&reason=' + encodeURIComponent(reason)
        })
        .then(response => response.json())
        .then(data => {
            // Restore button
            if (btn) {
                btn.innerHTML = '<i class="bi bi-x"></i>';
                btn.disabled = false;
            }
            
            if (data.success) {
                alert('Result rejected!');
                const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
                if (row) row.remove();
                updatePendingCount();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.debug('Reject error (ignored)');
            if (btn) {
                btn.innerHTML = '<i class="bi bi-x"></i>';
                btn.disabled = false;
            }
        });
    }
}

// Make them global
window.viewResultDetails = viewResultDetails;
window.verifyResult = verifyResult;
window.rejectResult = rejectResult;

console.log('Verification functions loaded in admin-main.js');

// Load verified results page via AJAX
function loadVerifiedResults() {
    console.log('Loading verified results...');
    loadPage('Results/view_verified.php');
}
window.loadVerifiedResults = loadVerifiedResults;

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel initialized');
    
    // Update pending count every 5 minutes
    updatePendingCount();
    setInterval(updatePendingCount, 300000);
    
    // Setup sidebar click events
    document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                
                // Update active menu
                document.querySelectorAll('.admin-sidebar .nav-link').forEach(l => {
                    l.classList.remove('active');
                });
                this.classList.add('active');
                
                // Get page from onclick
                const onclick = this.getAttribute('onclick') || '';
                
                if (onclick.includes('loadTeacherManagement')) {
                    loadTeacherManagement();
                } else if (onclick.includes('loadClassManagement')) {
                    loadClassManagement();
                } else if (onclick.includes('loadStudentManagement')) { 
                    loadStudentManagement();
                } else if (onclick.includes('loadDashboard')) {
                    loadDashboard();
                } else if (onclick.includes('loadSubjectManagement')) {
                    loadSubjectManagement();
                } else if (onclick.includes('loadResultsVerification')) {
                    loadResultsVerification();
                }
            }
        });
    });
    
    // Initialize Bootstrap
    initBootstrapComponents();
    
    // Set home as active by default
    const homeLink = document.querySelector('.admin-sidebar .nav-link[onclick*="loadDashboard"]');
    if (homeLink) {
        homeLink.classList.add('active');
        loadDashboard();
    }
    
    // Global error handler (silent)
    window.addEventListener('error', function(e) {
        if (e.message === 'Script error.' || e.message === 'Script error' || !e.message) {
            e.preventDefault();
            return false;
        }
        console.debug('Ignored error:', e.message);
        e.preventDefault();
        return false;
    });
    
    window.addEventListener('unhandledrejection', function(e) {
        e.preventDefault();
        return false;
    });
});

// Handle verification button clicks
document.addEventListener('click', function(event) {
    if (event.target.closest('.verification-actions')) {
        const btn = event.target.closest('button');
        if (!btn) return;
        
        let resultId = btn.dataset.resultId;
        if (!resultId) {
            const onclick = btn.getAttribute('onclick') || '';
            const match = onclick.match(/\((\d+)\)/);
            if (match) resultId = match[1];
        }
        
        if (!resultId) return;
        
        event.preventDefault();
        event.stopPropagation();
        
        if (btn.classList.contains('btn-view')) {
            viewResultDetails(resultId);
        } 
        else if (btn.classList.contains('btn-verify')) {
            verifyResult(resultId);
        }
        else if (btn.classList.contains('btn-reject')) {
            rejectResult(resultId);
        }
        
        return false;
    }
});