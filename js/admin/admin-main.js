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
        
        // SKIP external scripts that pages include themselves
        if (script.src && (
            script.src.includes('class-management.js') || 
            script.src.includes('student-management.js') ||
            script.src.includes('teacher-management.js')
        )) {
            console.log('Skipping external script - page includes it:', script.src);
            return; // Skip this script
        }
        
        // If it's an external script (other than management scripts)
        if (script.src) {
            const newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.async = false;
            document.head.appendChild(newScript);

            
            // Copy all attributes
            // Array.from(script.attributes).forEach(attr => {
            //     newScript.setAttribute(attr.name, attr.value);
            // });
            
            // // Replace old script with new one (this triggers execution)
            // script.parentNode.replaceChild(newScript, script);
            
        } else {
            // If it's inline script
            try {
                console.log('Executing inline script...');
                // Use eval() - this is the key fix!
                eval(script.textContent);
                console.log('✅ Inline script executed successfully');
            } catch (error) {
                console.error('❌ Error executing script:', error);
                // Fallback: create script element
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                document.body.appendChild(newScript);

                // Remove after execution
                setTimeout(() => {
                    if (newScript.parentNode) {
                        document.body.removeChild(newScript);
                    }
                }, 100);            
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
            console.error('Failed to load script:', src, error);
            reject(error);
        };
        
        document.head.appendChild(script);
    });
}

// Initialize Bootstrap components
function initBootstrapComponents() {
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
        .catch(error => console.warn('Pending count error:', error));
}

// just checking
// ===== VERIFICATION FUNCTIONS =====
// These will be available globally

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

function verifyResult(resultId) {
    console.log('✅ Verifying result:', resultId);
    
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
                alert('✅ Result verified successfully!');
                
                // Remove the row
                const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
                if (row) {
                    // Animate removal
                    row.style.transition = 'all 0.3s';
                    row.style.opacity = '0';
                    row.style.height = '0';
                    row.style.padding = '0';
                    row.style.margin = '0';
                    row.style.overflow = 'hidden';
                    
                    setTimeout(() => {
                        row.remove();
                        
                        // Update pending count
                        updatePendingCount();
                        
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
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (btn) {
                btn.innerHTML = '<i class="bi bi-check"></i>';
                btn.disabled = false;
            }
            alert('Failed to verify result');
        });
    }
}

function rejectResult(resultId) {
    console.log('❌ Rejecting result:', resultId);
    
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
                alert('✅ Result rejected!');
                
                // Remove the row
                const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
                if (row) row.remove();
                
                // Update pending count
                updatePendingCount();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (btn) {
                btn.innerHTML = '<i class="bi bi-x"></i>';
                btn.disabled = false;
            }
            alert('Failed to reject result');
        });
    }
}

// Make them global
window.viewResultDetails = viewResultDetails;
window.verifyResult = verifyResult;
window.rejectResult = rejectResult;

console.log('✅ Verification functions loaded in admin-main.js');
// Load verified results page via AJAX
function loadVerifiedResults() {
    console.log('Loading verified results...');
    loadPage('Results/view_verified.php');
}

// Make globally available
window.loadVerifiedResults = loadVerifiedResults;

// Load rejected results page via AJAX
function loadRejectedResults() {
    console.log('Loading rejected results...');
    loadPage('Results/view_rejected.php');
}

// Make functions globally available
window.loadRejectedResults = loadRejectedResults;
// just checking
// function viewResultDetails(resultId) {
//     console.log('📋 Viewing result:', resultId);
    
//     fetch('Results/get_result_details.php', {
//         method: 'POST',
//         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
//         body: 'result_id=' + resultId
//     })
//     .then(response => response.text())
//     .then(html => {
//         // Create modal
//         const modal = document.createElement('div');
//         modal.className = 'modal fade';
//         modal.innerHTML = html;
//         document.body.appendChild(modal);
        
//         // Show modal
//         const modalInstance = new bootstrap.Modal(modal);
//         modalInstance.show();
        
//         // Remove modal after hiding
//         modal.addEventListener('hidden.bs.modal', function() {
//             modal.remove();
//         });
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         alert('Error loading details: ' + error.message);
//     });
// }

// Verify result
// function verifyResult(resultId) {
//     console.log('✅ Verifying result:', resultId);
    
//     if (confirm('Verify this result? Once verified, teacher cannot edit it.')) {
//         updateVerificationStatus(resultId, 'verified');
//     }
// }

// Reject result
// function rejectResult(resultId) {
//     console.log('❌ Rejecting result:', resultId);
    
//     const reason = prompt('Enter rejection reason (optional):');
//     if (reason !== null) {
//         updateVerificationStatus(resultId, 'rejected', reason);
//     }
// }

// Update verification status
// function updateVerificationStatus(resultId, status, reason = '') {
//     console.log('🔄 Updating result', resultId, 'to', status);
    
//     fetch('Results/update_verification.php', {
//         method: 'POST',
//         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
//         body: 'result_id=' + resultId + '&status=' + status + '&reason=' + encodeURIComponent(reason)
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             alert('Result ' + status + ' successfully!');
//             // Refresh the page
//             loadResultsVerification();
//         } else {
//             alert('Error: ' + data.message);
//         }
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         alert('Failed to update verification status');
//     });
// }

// Filter pending results
// function filterPendingResults() {
//     const faculty = document.getElementById('filter-faculty').value;
//     const semester = document.getElementById('filter-semester').value;
    
//     // Reload with filters
//     loadResultsVerification();
// }

// ===== MAKE FUNCTIONS GLOBALLY AVAILABLE =====
// These lines make the verification functions available globally
// window.viewResultDetails = viewResultDetails;
// window.verifyResult = verifyResult;
// window.rejectResult = rejectResult;
// window.updateVerificationStatus = updateVerificationStatus;
// window.filterPendingResults = filterPendingResults;

// console.log('✅ Results verification system ready');

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
        loadDashboard(); // Load dashboard on initial load
    }
    
    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
        showAlert('danger', 'JavaScript Error: ' + e.message);
    });
    
    // Unhandled promise rejection
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled promise rejection:', e.reason);
        showAlert('danger', 'Async Error: ' + e.reason.message);
    });
});

// Handle verification button clicks
document.addEventListener('click', function(event) {
    // Check if clicked on verification buttons
    if (event.target.closest('.verification-actions')) {
        const btn = event.target.closest('button');
        if (!btn) return;
        
        // Get result ID
        let resultId = btn.dataset.resultId;
        if (!resultId) {
            // Try to parse from onclick
            const onclick = btn.getAttribute('onclick') || '';
            const match = onclick.match(/\((\d+)\)/);
            if (match) resultId = match[1];
        }
        
        if (!resultId) return;
        
        // Prevent default and stop propagation
        event.preventDefault();
        event.stopPropagation();
        
        // Call appropriate function
        if (btn.classList.contains('btn-view')) {
            console.log('View clicked:', resultId);
            if (typeof viewResultDetails === 'function') {
                viewResultDetails(resultId);
            }
        } 
        else if (btn.classList.contains('btn-verify')) {
            console.log('Verify clicked:', resultId);
            if (typeof verifyResult === 'function') {
                verifyResult(resultId);
            }
        }
        else if (btn.classList.contains('btn-reject')) {
            console.log('Reject clicked:', resultId);
            if (typeof rejectResult === 'function') {
                rejectResult(resultId);
            }
        }
        
        return false;
    }
});