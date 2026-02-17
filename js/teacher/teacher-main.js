// teacher-main.js - Core navigation and functions for teacher panel

// ===== GLOBAL VARIABLES =====
let TEACHER_ID = 0;
let TEACHER_NAME = '';

// ===== LOAD PAGE - SIMPLE FETCH VERSION =====
function loadPage(url) {
    console.log('Loading page:', url);
    const mainContent = document.getElementById('main-content');
    
    if (!mainContent) {
        console.error('Main content container not found!');
        return false;
    }
    
    // Show loading
    mainContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading...</p>
        </div>
    `;

    // Use FETCH - simple and easy!
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Set the HTML
            mainContent.innerHTML = html;
            console.log('✅ Page loaded:', url);
            
            // Execute ALL scripts - simple approach
            const scripts = mainContent.querySelectorAll('script');
            scripts.forEach(script => {
                if (script.src) {
                    // External script
                    const newScript = document.createElement('script');
                    newScript.src = script.src;
                    newScript.async = false;
                    document.head.appendChild(newScript);
                    console.log('Loaded script:', script.src);
                } else {
                    // Inline script - use eval (works!)
                    try {
                        eval(script.textContent);
                        console.log('✅ Executed inline script');
                    } catch (e) {
                        console.error('Script error:', e);
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            mainContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Error: ${error.message}
                    <button onclick="location.reload()" class="btn btn-sm btn-secondary ms-2">
                        Reload
                    </button>
                </div>
            `;
        });
}


// Function to execute scripts in dynamically loaded content
function executeScriptsInContent(container) {
    console.log('Executing scripts in loaded content...');
    
    // Separate scripts into external and inline
    const externalScripts = [];
    const inlineScripts = [];
    
    container.querySelectorAll('script').forEach(script => {
        if (script.src) {
            externalScripts.push(script.src);
        } else {
            inlineScripts.push(script.textContent);
        }
    });
    
    // Load external scripts first (like Highcharts)
    let loadedCount = 0;
    
    function loadNextExternal() {
        if (loadedCount >= externalScripts.length) {
            // All external scripts loaded, now run inline scripts
            inlineScripts.forEach(scriptContent => {
                try {
                    eval(scriptContent);
                    console.log('✅ Inline script executed');
                } catch (e) {
                    console.error('Inline script error:', e);
                }
            });
            return;
        }
        
        const src = externalScripts[loadedCount];
        console.log('Loading external script:', src);
        
        const script = document.createElement('script');
        script.src = src;
        script.async = false;
        
        script.onload = () => {
            console.log('✅ Loaded:', src);
            loadedCount++;
            loadNextExternal(); // Load next script
        };
        
        script.onerror = (e) => {
            console.error('Failed to load:', src, e);
            loadedCount++;
            loadNextExternal(); // Continue anyway
        };
        
        document.head.appendChild(script);
    }
    
    // Start loading external scripts
    if (externalScripts.length > 0) {
        loadNextExternal();
    } else {
        // No external scripts, just run inline
        inlineScripts.forEach(scriptContent => {
            try {
                eval(scriptContent);
                console.log('✅ Inline script executed');
            } catch (e) {
                console.error('Inline script error:', e);
            }
        });
    }
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

// ===== CLASS PERFORMANCE MODAL - ULTIMATE FIX =====
// ===== CLASS PERFORMANCE MODAL - ULTIMATE FIX =====
function viewClassPerformanceModal(classId, className) {
    console.log('Opening performance modal for:', classId, className);
    
    // Remove existing modal if any
    const existingModal = document.getElementById('performanceModal');
    if (existingModal) existingModal.remove();
    
    // Get absolute URL
    const baseUrl = window.location.origin + '/Student_Result_Analytics';
    const performanceUrl = `${baseUrl}/PHP_Files/teacher/class_performance_modal.php?class_id=${classId}`;
    
    // Create modal with iframe
    const modalHTML = `
        <div class="modal fade" id="performanceModal" tabindex="-1">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-bar-chart-fill me-2"></i> Class Performance: ${className}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe src="${performanceUrl}" 
                                style="width: 100%; height: 85vh; border: none;">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('performanceModal'));
    modal.show();
    
    // Clean up
    document.getElementById('performanceModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
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

// ===== SIMPLE PERFORMANCE PAGE LOADER =====
function loadPerformancePage(classId, className) {
    console.log('Loading performance page for class:', classId);
    
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    // Show loading
    mainContent.innerHTML = `
        <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart-fill me-2"></i> 
                    Class Performance: ${className}
                </h5>
                <button class="btn btn-sm btn-light" onclick="loadMyClasses()">
                    <i class="bi bi-arrow-left me-1"></i> Back to Classes
                </button>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Loading performance data...</p>
                </div>
            </div>
        </div>
    `;
    setTimeout(function() {
        // Force reload of results.js if needed
        if (window.location.href.includes('results.php')) {
            const script = document.createElement('script');
            script.src = '/Student_Result_Analytics/js/student/results.js?t=' + Date.now();
            document.head.appendChild(script);
        }
    }, 200);
    
    // Fetch the performance page directly
    fetch(`/Student_Result_Analytics/PHP_Files/teacher/class_performance_modal.php?class_id=${classId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Extract just the content we need (remove any extra HTML/BODY tags)
            let contentHtml = html;
            
            // If there's body tag, extract its content
            if (html.includes('<body>')) {
                const bodyMatch = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
                if (bodyMatch && bodyMatch[1]) {
                    contentHtml = bodyMatch[1];
                }
            }
            
            // Update main content with the performance page
            mainContent.innerHTML = `
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-bar-chart-fill me-2"></i> 
                             ${className}
                        </h5>
                        <button class="btn btn-sm btn-light" onclick="loadMyClasses()">
                            <i class="bi bi-arrow-left me-1"></i> Back to Classes
                        </button>
                    </div>
                    <div class="card-body">
                        ${contentHtml}
                    </div>
                </div>
            `;
            
            // IMPORTANT: Execute all scripts in the correct order
            setTimeout(() => {
                // First, execute any inline scripts in the content
                const inlineScripts = mainContent.querySelectorAll('script:not([src])');
                inlineScripts.forEach(script => {
                    try {
                        eval(script.textContent);
                        console.log('✅ Executed inline script');
                    } catch (e) {
                        console.error('Inline script error:', e);
                    }
                });
                
                // Then load external scripts
                const externalScripts = mainContent.querySelectorAll('script[src]');
                externalScripts.forEach(script => {
                    const src = script.src;
                    console.log('Loading external script:', src);
                    
                    // Check if Highcharts is already loaded
                    if (src.includes('highcharts.js') && typeof Highcharts !== 'undefined') {
                        console.log('Highcharts already loaded');
                        return;
                    }
                    
                    const newScript = document.createElement('script');
                    newScript.src = src;
                    newScript.onload = () => console.log('✅ Loaded:', src);
                    newScript.onerror = (e) => console.error('Failed to load:', src, e);
                    document.head.appendChild(newScript);
                });
                
                // Manually trigger performance.js if it didn't run
                if (typeof window.classData !== 'undefined' && window.classData.students) {
                    console.log('Manually initializing performance chart...');
                    setTimeout(() => {
                        if (typeof initializePerformanceChart === 'function') {
                            initializePerformanceChart();
                        } else if (document.querySelector('#chart')) {
                            // Fallback: create chart manually
                            createChartManually();
                        }
                    }, 500);
                }
            }, 100);
            
            console.log('✅ Performance page loaded');
        })
        .catch(error => {
            console.error('Error:', error);
            mainContent.innerHTML = `
                <div class="content-card">
                    <div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i> 
                            Error Loading Performance
                        </h5>
                        <button class="btn btn-sm btn-light" onclick="loadMyClasses()">
                            <i class="bi bi-arrow-left me-1"></i> Back to Classes
                        </button>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="bi bi-emoji-frown display-1 text-muted mb-3"></i>
                        <h4 class="text-danger">Failed to Load Performance Data</h4>
                        <p class="text-muted mb-4">${error.message}</p>
                        <button class="btn btn-primary" onclick="loadPerformancePage(${classId}, '${className}')">
                            <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                        </button>
                        <button class="btn btn-outline-secondary ms-2" onclick="loadMyClasses()">
                            <i class="bi bi-arrow-left me-2"></i>Go Back
                        </button>
                    </div>
                </div>
            `;
        });
}

// Fallback function to create chart manually
function createChartManually() {
    if (!window.classData || !window.classData.students || window.classData.students.length === 0) {
        console.log('No student data available');
        return;
    }
    
    const data = window.classData;
    const chartElement = document.getElementById('chart');
    
    if (!chartElement || typeof Highcharts === 'undefined') {
        console.log('Chart element or Highcharts not found');
        return;
    }
    
    const marks = data.students.map(s => s.marks);
    
    // Colors for chart
    const colors = data.students.map(s => {
        if (s.marks >= 80) return '#28a745';
        if (s.marks >= 60) return '#ffc107';
        if (s.marks >= 40) return '#fd7e14';
        if (s.marks > 0) return '#dc3545';
        return '#6c757d';
    });
    
    Highcharts.chart('chart', {
        chart: { type: 'column' },
        title: { text: 'Student Performance' },
        xAxis: { 
            categories: data.students.map(s => s.name),
            labels: { rotation: -30, style: { fontSize: '12px' } }
        },
        yAxis: {
            min: 0,
            max: 100,
            title: { text: 'Marks' },
            plotLines: [{
                value: 40,
                color: '#ffc107',
                dashStyle: 'dash',
                width: 2,
                label: { text: 'Passing Mark', style: { color: '#666' } }
            }]
        },
        series: [{
            name: 'Marks',
            data: data.students.map(s => s.marks),
            colorByPoint: true,
            colors: colors
        }],
        credits: { enabled: false }
    });
    
    console.log('Chart created manually');
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
window.loadPerformancePage = loadPerformancePage;
window.viewClassStudents = viewClassStudents;
window.viewClassDetails = viewClassDetails;
window.viewStudentDetails = viewStudentDetails;
window.editStudent = editStudent;
window.deleteStudent = deleteStudent;

