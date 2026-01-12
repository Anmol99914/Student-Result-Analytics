/**
 * Teacher Dashboard JavaScript
 * File: js/teacher_dashboard.js
 */

// Global variables
const TeacherDashboard = {
    teacherId: 0,
    teacherName: '',
    currentPage: 'home',
    
    // Initialize dashboard
    init: function() {
        console.log('Teacher Dashboard initialized');
        
        // Set teacher ID (from PHP)
        this.teacherId = window.TEACHER_ID || 0;
        this.teacherName = window.TEACHER_NAME || '';
        
        // Initialize event listeners
        this.initEventListeners();
        
        // Set active link
        this.setActiveLink('home');
        
        // Prevent back button
        this.preventBackButton();
    },
    
    // Initialize event listeners
    initEventListeners: function() {
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('[data-bs-toggle="offcanvas"]');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', this.toggleMobileMenu);
        }
        
        // Close mobile menu when clicking overlay
        const mobileOverlay = document.querySelector('.mobile-overlay');
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', this.closeMobileMenu);
        }
        
        // Logout button confirmation
        const logoutBtn = document.querySelector('form[action="teacher_logout.php"] button');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        }
    },
    
    // Navigation functions
    showHome: function() {
        this.setActiveLink('home');
        this.currentPage = 'home';
        
        const mainContent = document.getElementById('main-content');
        if (!mainContent) return;
        
        mainContent.innerHTML = `
            <div class="text-center py-5">
                <h1 class="display-5 mb-4">Welcome, ${this.teacherName}! 👨‍🏫</h1>
                <p class="lead text-muted mb-5">Manage your classes, students, and results from this dashboard.</p>
                
                <div class="row justify-content-center g-4">
                    <div class="col-md-4">
                        <div class="card border-primary shadow-sm dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-table display-4 text-primary mb-3"></i>
                                <h5 class="card-title">My Classes</h5>
                                <p class="card-text">View classes assigned to you</p>
                                <button class="btn btn-outline-primary" onclick="TeacherDashboard.showMyClasses()">
                                    View Classes
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-success shadow-sm dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-person-plus display-4 text-success mb-3"></i>
                                <h5 class="card-title">Add Student</h5>
                                <p class="card-text">Register new students to your class</p>
                                <button class="btn btn-outline-success" onclick="TeacherDashboard.showAddStudentForm()">
                                    Add Student
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-warning shadow-sm dashboard-card">
                            <div class="card-body text-center">
                                <i class="bi bi-trophy display-4 text-warning mb-3"></i>
                                <h5 class="card-title">Enter Results</h5>
                                <p class="card-text">Enter marks for your students</p>
                                <button class="btn btn-outline-warning" onclick="TeacherDashboard.showAddResultForm()">
                                    Enter Results
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },
    
    showMyClasses: function() {
        this.setActiveLink('classes');
        this.currentPage = 'classes';
        
        const mainContent = document.getElementById('main-content');
        if (!mainContent) return;
        
        mainContent.innerHTML = `
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-table me-2"></i> My Assigned Classes</h5>
                    <button class="btn btn-sm btn-light" onclick="TeacherDashboard.showHome()">
                        <i class="bi bi-house"></i> Dashboard
                    </button>
                </div>
                <div class="card-body">
                    <div id="classes-container">
                        <div class="text-center py-5 loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading your classes...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        this.loadTeacherClasses();
    },
    
    loadTeacherClasses: function() {
        const container = document.getElementById('classes-container');
        if (!container) return;
         // Add cache-busting
        const cacheBuster = 't=' + new Date().getTime();
        
        fetch('get_teacher_classes.php'+ cacheBuster, {
            headers: {
                'Cache-Control': 'no-cache'
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log('Classes data:', data);
                
                // Handle error response
                if (data.error) {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Error: ${data.error}
                            ${data.message ? `<br><small>${data.message}</small>` : ''}
                        </div>
                    `;
                    return;
                }
                
                // Get classes array from response
                let classesArray = [];
                
                if (Array.isArray(data)) {
                    classesArray = data;
                } else if (data.classes && Array.isArray(data.classes)) {
                    classesArray = data.classes;
                } else if (data.success && data.classes && Array.isArray(data.classes)) {
                    classesArray = data.classes;
                } else if (data.data && Array.isArray(data.data)) {
                    classesArray = data.data;
                }
                
                // Check if we have classes
                if (!classesArray || classesArray.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No classes assigned to you yet.
                            <br><small>Contact admin to get classes assigned.</small>
                        </div>
                    `;
                    return;
                }
                
                // Build table
                let html = `
                    <div class="table-responsive data-table">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Class ID</th>
                                    <th>Faculty</th>
                                    <th>Semester</th>
                                    <th>Students</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                classesArray.forEach(cls => {
                    const statusBadge = cls.status === 'active' 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-secondary">Inactive</span>';
                    
                    const createdDate = cls.created_at 
                        ? new Date(cls.created_at).toLocaleDateString()
                        : 'N/A';
                    
                    html += `
                        <tr>
                            <td>${cls.class_id || 'N/A'}</td>
                            <td><strong>${cls.faculty || 'N/A'}</strong></td>
                            <td>Semester ${cls.semester || 'N/A'}</td>
                            <td>
                                <span class="badge bg-primary">${cls.student_count || 0} students</span>
                            </td>
                            <td>${statusBadge}</td>
                            <td>${createdDate}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="TeacherDashboard.viewClassStudents(${cls.class_id})">
                                    <i class="bi bi-people"></i> View Students
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading classes:', error);
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Error loading classes. Please try again.
                        <br><small>${error.message}</small>
                    </div>
                `;
            });
    },
    
    viewClassStudents: function(classId) {
        alert('View students of class ID: ' + classId + '\nThis feature will be implemented.');
    },
    
    showAddStudentForm: function() {
        this.setActiveLink('addStudent');
        this.currentPage = 'addStudent';
        
        this.loadContent('Students/add_student.php', 'Add New Student', 'bg-success', 'person-plus');
    },
    
    showMyStudents: function() {
        this.setActiveLink('students');
        this.currentPage = 'students';
        
        this.loadContent('Students/my_students.php', 'My Students', 'bg-info', 'people');
    },
    
    showAddResultForm: function() {
        this.setActiveLink('results');
        this.currentPage = 'results';
        
         // Force fresh load with timestamp
    const timestamp = new Date().getTime();
    this.loadContent('Results/get_classes_for_results.php?t=' + timestamp, 
                     'Enter Results', 
                     'bg-warning text-dark', 
                     'trophy');
},
    
    showProfile: function() {
        this.setActiveLink('profile');
        this.currentPage = 'profile';
        
        this.loadContent('profile.php', 'My Profile', 'bg-secondary', 'person');
    },
    
    // Helper function to load content
    loadContent: function(url, title, headerClass, icon) {
        const mainContent = document.getElementById('main-content');
        if (!mainContent) return;

        // Add timestamp to prevent caching
        const cacheBuster = 't=' + new Date().getTime();
        const separator = url.includes('?') ? '&' : '?';
        const fullUrl = url + separator + cacheBuster;
        
        // Show loading
        mainContent.innerHTML = `
            <div class="card shadow">
                <div class="card-header ${headerClass} d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-${icon} me-2"></i> ${title}</h5>
                    <button class="btn btn-sm btn-light" onclick="TeacherDashboard.showHome()">
                        <i class="bi bi-house"></i> Dashboard
                    </button>
                </div>
                <div class="card-body">
                    <div class="text-center py-5 loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading ${title.toLowerCase()}...</p>
                    </div>
                </div>
            </div>
        `;
        
        // Fetch content
        fetch(fullUrl, {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Get the main content
                const formContainer = tempDiv.querySelector('.form-container');
                const content = formContainer ? formContainer.outerHTML : tempDiv.innerHTML;
                
                mainContent.innerHTML = `
                    <div class="card shadow">
                        <div class="card-header ${headerClass} d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-${icon} me-2"></i> ${title}</h5>
                            <button class="btn btn-sm btn-light" onclick="TeacherDashboard.showHome()">
                                <i class="bi bi-house"></i> Dashboard
                            </button>
                        </div>
                        <div class="card-body">
                            ${content}
                        </div>
                    </div>
                `;
                
                // Execute scripts in loaded content
                this.executeScripts(tempDiv);
            })
            .catch(error => {
                console.error(`Error loading ${title}:`, error);
                mainContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="bi bi-exclamation-triangle"></i> Error Loading ${title}</h5>
                        <p>${error.message}</p>
                        <button class="btn btn-primary mt-2" onclick="TeacherDashboard.showHome()">
                            <i class="bi bi-house"></i> Back to Dashboard
                        </button>
                    </div>
                `;
            });
    },
    
    // Execute scripts from loaded HTML
    executeScripts: function(tempDiv) {
        const scripts = tempDiv.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            document.body.appendChild(newScript);
            setTimeout(() => {
                if (newScript.parentNode) {
                    document.body.removeChild(newScript);
                }
            }, 100);
        });
    },
    
    // Set active link in sidebar
    setActiveLink: function(linkName) {
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const activeLink = document.querySelector(`.sidebar .nav-link[onclick*="${linkName}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    },
    
    // Mobile menu functions
    toggleMobileMenu: function() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    },
    
    closeMobileMenu: function() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        
        if (sidebar && overlay) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    },
    
    // Prevent back button
    preventBackButton: function() {
        window.history.forward();
        window.onload = function() { window.history.forward(); };
    }
};

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    TeacherDashboard.init();
});

// Make dashboard functions available globally
window.TeacherDashboard = TeacherDashboard;

// Shortcut functions for onclick handlers
window.showHome = () => TeacherDashboard.showHome();
window.showMyClasses = () => TeacherDashboard.showMyClasses();
window.showAddStudentForm = () => TeacherDashboard.showAddStudentForm();
window.showMyStudents = () => TeacherDashboard.showMyStudents();
window.showAddResultForm = () => TeacherDashboard.showAddResultForm();
window.showProfile = () => TeacherDashboard.showProfile();