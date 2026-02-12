// teacher-main.js - Core navigation and functions for teacher panel

// ===== GLOBAL VARIABLES =====
let TEACHER_ID = 0;
let TEACHER_NAME = '';

// ===== CORE FUNCTIONS =====


// Load any page via AJAX
function loadPage(url) {
    console.log('Loading page:', url);
    const mainContent = document.getElementById('main-content');
    
    if (!mainContent) {
        console.error('Main content container not found!');
        return false;
    }
    
    // Show loading
    mainContent.innerHTML = `
        <div class="text-center py-5 loading-spinner">
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
            if (typeof initBootstrapComponents === 'function') {
                initBootstrapComponents();
            }
            
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
                        <button onclick="location.reload()" class="btn btn-sm btn-secondary">Reload Page</button>
                    </div>
                </div>
            `;
        });
}

// Execute scripts in dynamically loaded content
function executeScriptsInContent(container) {
    console.log('Executing scripts in loaded content...');
    
    const scripts = container.querySelectorAll('script');
    scripts.forEach(script => {
        // If it's an external script
        if (script.src) {
            // SKIP results.js - it's already loaded globally
            if (script.src.includes('results.js')) {
                console.log('Skipping duplicate results.js');
                return;
            }
            
            const newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.async = false;
            
            // Copy all attributes
            Array.from(script.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            
            // Replace old script with new one
            script.parentNode.replaceChild(newScript, script);
            
        } else {
            // If it's inline script
            try {
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                document.body.appendChild(newScript);
                document.body.removeChild(newScript);
                console.log('Inline script executed');
            } catch (error) {
                console.error('Error executing inline script:', error);
            }
        }
    });
}
// Set active link in sidebar
function setActiveLink(pageName) {
    // Remove active class from all links
    document.querySelectorAll('.teacher-sidebar .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    document.querySelectorAll('.offcanvas-body .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Add active class to current page
    const selector = `.teacher-sidebar .nav-link[onclick*="${pageName}"]`;
    const mobileSelector = `.offcanvas-body .nav-link[onclick*="${pageName}"]`;
    
    document.querySelectorAll(selector).forEach(link => {
        link.classList.add('active');
    });
    
    document.querySelectorAll(mobileSelector).forEach(link => {
        link.classList.add('active');
    });
}

// Close mobile offcanvas
function closeOffcanvas() {
    const offcanvas = document.getElementById('offcanvasSidebar');
    if (offcanvas) {
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
        if (bsOffcanvas) {
            bsOffcanvas.hide();
        }
    }
}

// ===== PAGE LOADING FUNCTIONS =====

// Load dashboard/home
function loadDashboard() {
    console.log('Loading dashboard');
    setActiveLink('Home');
    loadHomeContent();
}

// Load My Classes 
function loadMyClasses() {
    console.log('Loading my classes');
    setActiveLink('MyClasses');
    
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <div class="content-card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-table me-2"></i> My Classes</h5>
                <button class="btn btn-sm btn-light" onclick="loadDashboard()">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </button>
            </div>
            <div class="card-body p-0">
                <div class="loading-container" id="classes-container">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Loading your classes...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Load classes data
    fetch('get_teacher_classes.php')
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById('classes-container');
            if (container) {
                container.innerHTML = html;
                container.classList.remove('loading-container');
            }
        })
        .catch(error => {
            const container = document.getElementById('classes-container');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Connection Error</h6>
                                <p class="mb-0">Failed to load classes: ${error.message}</p>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadMyClasses()">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Retry
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.classList.remove('loading-container');
            }
        });
}

// Load Add Student Form
function loadAddStudentForm() {
    console.log('Loading add student form');
    setActiveLink('AddStudentForm');
    loadPage('Students/add_student.php');
}

// Load My Students
function loadMyStudents() {
    console.log('Loading my students');
    setActiveLink('MyStudents');
    loadPage('Students/my_students.php');
}

// Load Add Result Form
function loadAddResultForm() {
    console.log('Loading add result form');
    setActiveLink('AddResultForm');
    
    // Load enter_results.php into main content
    loadPage('Results/enter_results.php');
}


// Load Profile
function loadProfile() {
    console.log('Loading profile');
    setActiveLink('Profile');
    loadPage('teacher_profile.php');
}

// ===== DEFAULT CONTENT FUNCTIONS =====

// Load home content (default dashboard)
function loadHomeContent() {
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="display-5 mb-3">Welcome, ${TEACHER_NAME}! 👨‍🏫</h1>
            <p class="lead mb-0">Manage your classes, students, and results from this dashboard.</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card dashboard-card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-table display-4 text-primary mb-3"></i>
                        <h5 class="card-title">My Classes</h5>
                        <p class="card-text">View classes assigned to you</p>
                        <button class="btn btn-outline-primary" onclick="loadMyClasses()">
                            View Classes
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-person-plus display-4 text-success mb-3"></i>
                        <h5 class="card-title">Add Student</h5>
                        <p class="card-text">Register new students to your class</p>
                        <button class="btn btn-outline-success" onclick="loadAddStudentForm()">
                            Add Student
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card dashboard-card border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-trophy display-4 text-warning mb-3"></i>
                        <h5 class="card-title">Enter Results</h5>
                        <p class="card-text">Enter marks for your students</p>
                        <button class="btn btn-outline-warning" onclick="loadAddResultForm()">
                            Enter Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// ===== TEACHER ACTION FUNCTIONS =====

// View students in a class
function viewClassStudents(classId) {
    console.log('Loading students for class:', classId);
    
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <div class="content-card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i> Class Students</h5>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="loadMyClasses()">
                        <i class="bi bi-arrow-left me-1"></i> Back to Classes
                    </button>
                    <button class="btn btn-sm btn-light" onclick="loadDashboard()">
                        <i class="bi bi-house"></i> Dashboard
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="loading-container" id="students-container">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Loading students for class ${classId}...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Load students for this class
    fetch(`Students/get_class_students.php?class_id=${classId}`)
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById('students-container');
            if (container) {
                container.innerHTML = html;
                container.classList.remove('loading-container');
            }
        })
        .catch(error => {
            const container = document.getElementById('students-container');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Error Loading Students</h6>
                                <p class="mb-0">${error.message}</p>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="viewClassStudents(${classId})">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.classList.remove('loading-container');
            }
        });
}

// View class details
function viewClassDetails(classId) {
    console.log('Loading class details:', classId);
    
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <div class="content-card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Class Details</h5>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="loadMyClasses()">
                        <i class="bi bi-arrow-left me-1"></i> Back to Classes
                    </button>
                    <button class="btn btn-sm btn-light" onclick="loadDashboard()">
                        <i class="bi bi-house"></i> Dashboard
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="loading-container" id="class-details-container">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Loading class details...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Load class details
    fetch(`get_class_details.php?class_id=${classId}`)
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById('class-details-container');
            if (container) {
                container.innerHTML = html;
                container.classList.remove('loading-container');
            }
        })
        .catch(error => {
            const container = document.getElementById('class-details-container');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Error Loading Class Details</h6>
                                <p class="mb-0">${error.message}</p>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="viewClassDetails(${classId})">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.classList.remove('loading-container');
            }
        });
}

// ===== CLASS PERFORMANCE MODAL =====
function viewClassPerformanceModal(classId, className) {
    console.log('Loading performance modal for class:', classId, className);
    
    // Remove existing modal if any
    const existingModal = document.getElementById('performanceModal');
    if (existingModal) existingModal.remove();
    
    // Show loading in modal
    const modalHTML = `
        <div class="modal fade" id="performanceModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-bar-chart-fill me-2"></i> Class Performance: ${className}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center py-5">
                            <div class="spinner-border text-info mb-3" role="status"></div>
                            <p>Loading performance data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('performanceModal'));
    modal.show();
    
    // Fetch performance data
    fetch(`class_performance_modal.php?class_id=${classId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            const modalBody = document.querySelector('#performanceModal .modal-body');
            if (modalBody) {
                modalBody.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error loading performance data:', error);
            const modalBody = document.querySelector('#performanceModal .modal-body');
            if (modalBody) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Error loading performance data:</strong> ${error.message}
                        <div class="mt-3">
                            <button class="btn btn-sm btn-primary" onclick="window.location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Page
                            </button>
                        </div>
                    </div>
                `;
            }
        });
}

// View individual student details
function viewStudentDetails(studentId) {
    console.log('Loading student details:', studentId);
    
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <div class="content-card fade-in">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i> Student Details</h5>
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-2" onclick="history.back()">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </button>
                    <button class="btn btn-sm btn-light" onclick="loadDashboard()">
                        <i class="bi bi-house"></i> Dashboard
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="loading-container" id="student-details-container">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Loading student details...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Load student details page
    fetch(`Students/view_student.php?id=${studentId}`)
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById('student-details-container');
            if (container) {
                container.innerHTML = html;
                container.classList.remove('loading-container');
            }
        })
        .catch(error => {
            const container = document.getElementById('student-details-container');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle me-2 fs-4"></i>
                            <div>
                                <h6 class="mb-1">Error Loading Student Details</h6>
                                <p class="mb-0">${error.message}</p>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="viewStudentDetails('${studentId}')">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.classList.remove('loading-container');
            }
        });
}

// Edit student
function editStudent(studentId) {
    console.log('Editing student:', studentId);
    
    if (typeof showAlert === 'function') {
        showAlert('info', `Edit student ID: ${studentId}. This feature will be implemented.`);
    } else {
        alert(`Edit feature for student ${studentId} will be implemented soon.`);
    }
}

// Delete student (with confirmation)
function deleteStudent(studentId, studentName) {
    if (!confirm(`Are you sure you want to delete student:\n\n${studentName} (ID: ${studentId})\n\nThis action cannot be undone!`)) {
        return;
    }
    
    console.log('Deleting student:', studentId);
    
    if (typeof showAlert === 'function') {
        showAlert('warning', `Deleting student ${studentName}. This is just a demo - actual delete will be implemented.`);
    } else {
        alert(`Student ${studentName} would be deleted. This is just a demo.`);
    }
}

// Enter marks for a specific subject
function enterSubjectMarks(classId, subjectId) {
    console.log('Enter marks for class:', classId, 'subject:', subjectId);
    
    if (typeof showAlert === 'function') {
        showAlert('info', `Redirecting to marks entry for subject ID: ${subjectId}`);
    }
    
    // For now, just redirect to the results entry page
    // You'll implement this fully when building the results system
    loadAddResultForm();
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Teacher panel initialized');
    
    // Get teacher data from global variables
    TEACHER_ID = window.TEACHER_ID || 0;
    TEACHER_NAME = window.TEACHER_NAME || '';
    
    // Setup sidebar click events
    document.querySelectorAll('.teacher-sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                
                // Get page from onclick
                const onclick = this.getAttribute('onclick') || '';
                
                if (onclick.includes('loadMyClasses')) {
                    loadMyClasses();
                } else if (onclick.includes('loadAddStudentForm')) {
                    loadAddStudentForm();
                } else if (onclick.includes('loadMyStudents')) {
                    loadMyStudents();
                } else if (onclick.includes('loadAddResultForm')) {
                    loadAddResultForm();
                } else if (onclick.includes('loadProfile')) {
                    loadProfile();
                } else if (onclick.includes('loadDashboard') || onclick.includes('showHome')) {
                    loadDashboard();
                }
            }
        });
    });
    
    // Initialize Bootstrap components
    if (typeof initBootstrapComponents === 'function') {
        initBootstrapComponents();
    }
    
    // Load dashboard by default
    loadDashboard();
    
    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
        if (typeof showAlert === 'function') {
            showAlert('danger', 'JavaScript Error: ' + e.message);
        }
    });
    
    // Unhandled promise rejection
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled promise rejection:', e.reason);
        if (typeof showAlert === 'function') {
            showAlert('danger', 'Async Error: ' + e.reason.message);
        }
    });
});

// Export functions to global scope
window.loadPage = loadPage;
window.loadDashboard = loadDashboard;
window.loadMyClasses = loadMyClasses;
window.loadAddStudentForm = loadAddStudentForm;
window.loadMyStudents = loadMyStudents;
window.loadAddResultForm = loadAddResultForm;
window.loadProfile = loadProfile;
window.setActiveLink = setActiveLink;
window.closeOffcanvas = closeOffcanvas;

// ===== GLOBAL EXPORTS =====
// Make sure all functions are available globally
window.viewClassPerformanceModal = viewClassPerformanceModal;
window.viewClassStudents = viewClassStudents;
window.viewClassDetails = viewClassDetails;
window.viewStudentDetails = viewStudentDetails;
window.editStudent = editStudent;
window.deleteStudent = deleteStudent;