/**
 * Results Main System - Handles classes and subjects navigation
 * File: js/results-main.js
 */

const ResultsSystem = (function() {
    'use strict';
    
    // Private variables
    let teacherId = 0;
    let teacherName = '';
    
    // Private methods
    function log(message, data = null) {
        console.log(`[ResultsSystem] ${message}`, data || '');
    }
    
    function showAlert(type, message, container = null) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" clfass="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
        } else {
            const cardBody = document.querySelector('.card-body');
            if (cardBody) {
                cardBody.insertBefore(alertDiv, cardBody.firstChild);
            }
        }
    }
    
    // Public methods
    return {
        // Initialize results system
        init: function() {
            log('Initializing results system...');
            
            // Get teacher data from window
            teacherId = window.TEACHER_DATA?.id || 0;
            teacherName = window.TEACHER_DATA?.name || '';
            
            log('Teacher:', { id: teacherId, name: teacherName });
            
            if (!teacherId) {
                showAlert('danger', 'Teacher ID not found. Please login again.');
                return;
            }
            
            this.loadClasses();
        },
        
        // Load classes for teacher
        loadClasses: function() {
            log('Loading classes for teacher:', teacherId);
            
            const container = document.getElementById('classes-container');
            if (!container) {
                log('Error: classes-container not found');
                return;
            }
            
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading your classes...</p>
                </div>
            `;
            
            fetch(`Results/get_classes_for_results.php?teacher_id=${teacherId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    this.bindClassEvents();
                })
                .catch(error => {
                    log('Error loading classes:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Classes</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-sm btn-primary" onclick="ResultsSystem.loadClasses()">
                                Try Again
                            </button>
                        </div>
                    `;
                });
        },
        
        // Bind click events to class buttons
        bindClassEvents: function() {
            const buttons = document.querySelectorAll('.select-class-btn');
            log('Binding events to', buttons.length, 'class buttons');
            
            buttons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const classId = button.getAttribute('data-class-id');
                    const faculty = button.getAttribute('data-faculty');
                    const semester = button.getAttribute('data-semester');
                    
                    log('Class selected:', { classId, faculty, semester });
                    this.loadClassSubjects(classId, faculty, semester);
                });
            });
        },
        
        // Load subjects for selected class
        loadClassSubjects: function(classId, faculty, semester) {
            log('Loading subjects for class:', { classId, faculty, semester });
            
            const container = document.getElementById('classes-container');
            if (!container) return;
            
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subjects for ${faculty} - Semester ${semester}...</p>
                </div>
            `;
            
            fetch(`Results/get_subjects_for_class.php?class_id=${classId}&faculty=${encodeURIComponent(faculty)}&semester=${semester}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    this.bindSubjectEvents();
                })
                .catch(error => {
                    log('Error loading subjects:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Subjects</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-sm btn-primary" onclick="ResultsSystem.loadClassSubjects(${classId}, '${faculty}', ${semester})">
                                Try Again
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="ResultsSystem.loadClasses()">
                                Back to Classes
                            </button>
                        </div>
                    `;
                });
        },
        
        // Bind click events to subject buttons
bindSubjectEvents: function() {
    // Single button for all actions
    document.querySelectorAll('.enter-marks-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const classId = button.getAttribute('data-class-id');
            const subjectId = button.getAttribute('data-subject-id');
            const subjectName = button.getAttribute('data-subject-name');
            const faculty = button.getAttribute('data-faculty');
            const semester = button.getAttribute('data-semester');
            
            // Check button text to see if marks exist
            const buttonText = button.textContent;
            const hasExistingMarks = buttonText.includes('View/Edit');
            
            log(`${hasExistingMarks ? 'View/Edit' : 'Enter'} marks for:`, { subjectName });
            this.loadMarksEntryForm(classId, subjectId, subjectName, faculty, semester, hasExistingMarks);
        });
    });
    
    log('Bound events to', document.querySelectorAll('.enter-marks-btn').length, 'subject buttons');
},
        
        // Load marks entry form
        loadMarksEntryForm: function(classId, subjectId, subjectName, faculty, semester, isViewMode = false) {
            log('Loading marks form for:', { classId, subjectId, subjectName, isViewMode });
            
            const container = document.getElementById('classes-container');
            if (!container) return;
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-pencil-square"></i> ${isViewMode ? 'View/Edit' : 'Enter'} Marks
                            </h5>
                            <small class="text-muted">${subjectName} - ${faculty} (Sem ${semester})</small>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="ResultsSystem.loadClassSubjects(${classId}, '${faculty}', ${semester})">
                            <i class="bi bi-arrow-left"></i> Back to Subjects
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="marks-entry-container">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading student list...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            fetch(`Results/get_students_for_marks.php?class_id=${classId}&subject_id=${subjectId}&subject_name=${encodeURIComponent(subjectName)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    const marksContainer = document.getElementById('marks-entry-container');
                    if (marksContainer) {
                        marksContainer.innerHTML = html;
                        
                        // Initialize marks system after content loads
                        setTimeout(() => {
                            if (typeof ResultsMarks !== 'undefined' && typeof ResultsMarks.init === 'function') {
                                ResultsMarks.init();
                            }
                        }, 100);
                    }
                })
                .catch(error => {
                    log('Error loading marks form:', error);
                    const marksContainer = document.getElementById('marks-entry-container');
                    if (marksContainer) {
                        marksContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Students</h5>
                                <p>${error.message}</p>
                                <button class="btn btn-sm btn-primary" onclick="ResultsSystem.loadMarksEntryForm(${classId}, ${subjectId}, '${subjectName}', '${faculty}', ${semester}, ${isViewMode})">
                                    Try Again
                                </button>
                            </div>
                        `;
                    }
                });
        },

        // Add this to the ResultsSystem object:
        manualInit: function() {
            console.log('ResultsSystem.manualInit called');
            this.init();
        },
        
        // Test function
        test: function() {
            alert('ResultsSystem is working!\nTeacher: ' + teacherName);
        }
    };
})();

// Initialize when DOM is ready and we're on results page
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, checking for results page...');
    
    if (document.getElementById('classes-container')) {
        console.log('Initializing ResultsSystem...');
        ResultsSystem.init();
    }
});

// Export to window
window.ResultsSystem = ResultsSystem;

// Add at the end of results-main.js:
// Global initialization trigger
window.initializeResultsSystem = function() {
    console.log('Manual initialization triggered');
    if (typeof ResultsSystem !== 'undefined' && typeof ResultsSystem.init === 'function') {
        ResultsSystem.init();
    } else {
        console.error('Cannot initialize: ResultsSystem not found');
    }
};