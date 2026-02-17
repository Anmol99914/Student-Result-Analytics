/**
 * Results Marks Management System
 * File: js/results-marks.js
 */



// Prevent ANY form submission in the marks system
document.addEventListener('submit', function(event) {
    if (event.target.id === 'marksForm' || event.target.closest('#marksContainer')) {
        console.log('Form submission blocked by global handler');
        event.preventDefault();
        event.stopPropagation();
        return false;
    }
});

// Also prevent default on any button with type="submit"
document.addEventListener('click', function(event) {
    if (event.target.type === 'submit' || event.target.closest('[type="submit"]')) {
        if (event.target.closest('#marksContainer')) {
            console.log('Submit button click blocked');
            event.preventDefault();
            event.stopPropagation();
        }
    }
});

var ResultsMarks = (function() {
    'use strict';
    
    // Private variables
    let currentClassId = 0;
    let currentSubjectId = 0;
    let currentSubjectName = '';
    let currentTeacherId = 0;
    let isFirstSave = true;
    let savedStudentCount = 0;
    
    // Private methods
    function log(message, data = null) {
        console.log(`[ResultsMarks] ${message}`, data || '');
    }
    
    function getGradeFromMarks(marks) {
        if (marks >= 90) return 'A+';
        if (marks >= 80) return 'A';
        if (marks >= 70) return 'B+';
        if (marks >= 60) return 'B';
        if (marks >= 50) return 'C+';
        if (marks >= 40) return 'C';
        return 'F';
    }
    
    // Public methods
    return {
        // Initialize marks system
        init: function() {
            log('Initializing marks system...');
            this.isFirstSave = true;
            this.savedStudentCount = 0;
            
            // Get current data from hidden inputs
            const classIdInput = document.getElementById('currentClassId');
            const subjectIdInput = document.getElementById('currentSubjectId');
            const subjectNameInput = document.getElementById('currentSubjectName');
            const teacherIdInput = document.getElementById('currentTeacherId');
            
            if (classIdInput) currentClassId = classIdInput.value;
            if (subjectIdInput) currentSubjectId = subjectIdInput.value;
            if (subjectNameInput) currentSubjectName = subjectNameInput.value;
            if (teacherIdInput) currentTeacherId = teacherIdInput.value;
            
            log('Current data:', { currentClassId, currentSubjectId, currentSubjectName, currentTeacherId });
            
            // Add event listeners to marks inputs
            // this.bindMarksInputEvents();
        },
        
        // Bind events to marks inputs
        // bindMarksInputEvents: function() {
        //     document.querySelectorAll('.marks-input').forEach(input => {
        //         input.addEventListener('blur', (e) => {
        //             this.calculateGrade(e.target);
        //         });
        //     });
        // },

        showAlert: function(type, message, container = null) {
            // Remove any existing alerts of same type
            document.querySelectorAll(`.alert-${type}`).forEach(alert => {
                setTimeout(() => alert.remove(), 100);
            });
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3 alert-${type}`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const targetContainer = container || document.querySelector('.card-body');
            if (targetContainer) {
                targetContainer.insertBefore(alertDiv, targetContainer.firstChild);
                
                // Auto-remove after 5 seconds for non-success messages
                if (type !== 'success') {
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);
                }
            }
        },
        
        // showSuccessMessage:
showSuccessMessage: function(studentCount, status, isUpdate = false) {
    const action = isUpdate ? 'Updated' : 'Saved';
    const icon = isUpdate ? 'bi-arrow-clockwise' : 'bi-check-circle-fill';
    const title = isUpdate ? 'Marks Updated!' : 'Marks Saved Successfully!';
    
    const successHtml = `
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <div class="d-flex align-items-center">
                <i class="bi ${icon} fs-4 me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="mb-1">${title}</h5>
                    <p class="mb-0">
                        ${action} marks for <strong>${studentCount || 0}</strong> students. 
                        Status: <span class="badge bg-warning">Pending</span>
                    </p>
                    <small class="text-muted">Awaiting admin verification</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove any existing success alerts
    document.querySelectorAll('.alert-success').forEach(alert => alert.remove());
    
    // Insert at the top of the card
    const cardHeader = document.querySelector('.card-header');
    if (cardHeader && cardHeader.nextElementSibling) {
        cardHeader.insertAdjacentHTML('afterend', successHtml);
    }
    
    // Store that we've saved at least once
    if (!isUpdate) {
        this.isFirstSave = false;
        this.savedStudentCount = studentCount || 0;
    }
},
        
        updateStatusToPending: function() {
            // Update all "Not Entered" status badges to "Pending"
            document.querySelectorAll('tbody tr').forEach(row => {
                const statusCell = row.cells[5]; // 6th column (0-indexed)
                if (statusCell) {
                    const currentStatus = statusCell.textContent.trim();
                    if (currentStatus === 'Not Entered' || currentStatus === '') {
                        statusCell.innerHTML = '<span class="badge bg-warning">Pending</span>';
                    }
                }
            });
            
            log('Updated status badges to Pending');
        },

        updateSaveCounter: function(isUpdate = false) {
            const counter = document.getElementById('save-counter');
            if (counter) {
                if (isUpdate) {
                    counter.innerHTML = '<i class="bi bi-arrow-clockwise text-warning"></i> Updated';
                    counter.className = 'text-warning';
                } else {
                    counter.innerHTML = '<i class="bi bi-check-circle text-success"></i> Saved';
                    counter.className = 'text-success';
                }
                
                setTimeout(() => {
                    if (counter) {
                        counter.innerHTML = '<i class="bi bi-pencil text-primary"></i> Ready to update';
                        counter.className = 'text-primary';
                    }
                }, 5000);
            }
        },

        // Add this function - it only updates grade badges
        updateGradeBadgesAfterSave: function() {
            console.log('=== UPDATING GRADE BADGES - NEW METHOD ===');
            
            // Get all table rows
            const rows = document.querySelectorAll('tbody tr');
            console.log('Found', rows.length, 'table rows');
            
            rows.forEach((row, index) => {
                // Find the marks input in this row
                const marksInput = row.querySelector('.marks-input');
                if (!marksInput) return;
                
                const marks = parseFloat(marksInput.value);
                console.log(`Row ${index + 1}: marks =`, marks);
                
                if (!isNaN(marks) && marks >= 0 && marks <= 100) {
                    const grade = getGradeFromMarks(marks);
                    console.log(`Row ${index + 1}: calculated grade =`, grade);
                    
                    // Grade is in the 5th column (0-indexed, so cells[4])
                    // Status is in the 6th column (cells[5])
                    const gradeCell = row.cells[4]; // 5th column is Grade
                    
                    if (gradeCell) {
                        // Find the badge inside the grade cell
                        const gradeBadge = gradeCell.querySelector('.badge');
                        if (gradeBadge) {
                            console.log(`Row ${index + 1}: updating badge from "${gradeBadge.textContent}" to "${grade}"`);
                            gradeBadge.textContent = grade;
                            
                            // Also update the title/aria-label if it exists
                            gradeBadge.title = grade;
                            gradeBadge.setAttribute('aria-label', grade);
                        } else {
                            // If no badge, create one
                            console.log(`Row ${index + 1}: creating new badge`);
                            const newBadge = document.createElement('span');
                            newBadge.className = 'badge bg-secondary';
                            newBadge.textContent = grade;
                            gradeCell.innerHTML = '';
                            gradeCell.appendChild(newBadge);
                        }
                    }
                }
            });
            
            console.log('=== GRADE UPDATE COMPLETE ===');
            return true;
        },

        
        resetSaveButton: function(isUpdate = false) {
            const saveBtn = document.getElementById('saveBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                
                if (isUpdate) {
                    saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Update Again';
                    saveBtn.className = 'btn btn-warning';
                    saveBtn.title = 'Update marks again';
                } else {
                    saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Marks';
                    saveBtn.className = 'btn btn-success';
                    saveBtn.title = 'Save marks for verification';
                }
            }
        },
        
        updateMarks: function() {
            // Same as saveAllMarks but with different messaging
            log('Updating marks...');
            this.saveAllMarks();
        },
        
        // For quick toast messages
showToast: function(message, type = 'success', duration = 3000) {
    // Create toast container if not exists
    let toastContainer = document.getElementById('marks-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'marks-toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 250px;
        `;
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast show" role="alert">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">
                    <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-info-circle'}"></i>
                    ${type === 'success' ? 'Success' : 'Info'}
                </strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('afterbegin', toastHtml);
    
    // Auto-remove after duration
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.remove();
        }
    }, duration);
},
        goBackToSubjects: function() {
            log('Going back to subjects...');
            
            // Get current class info from hidden inputs
            const classId = document.getElementById('currentClassId')?.value;
            const faculty = document.getElementById('currentFaculty')?.value;
            const semester = document.getElementById('currentSemester')?.value;
            
            console.log('Navigation data:', { classId, faculty, semester });
            
            if (classId && faculty && semester) {
                if (typeof ResultsSystem !== 'undefined' && typeof ResultsSystem.loadClassSubjects === 'function') {
                    ResultsSystem.loadClassSubjects(classId, faculty, semester);
                } else {
                    this.showAlert('warning', 'Navigation system not available. Please use browser back button.');
                }
            } else {
                this.showAlert('warning', 'Missing navigation data. Please use browser back button.');
            }
        },
        
        // Calculate grade for a specific input
        // calculateGrade: function(input) {
        //     const marks = parseFloat(input.value);
        //     const studentId = input.getAttribute('data-student-id');
            
        //     log('Calculating grade for student:', studentId, 'marks:', marks);
            
        //     if (isNaN(marks) || marks < 0 || marks > 100) {
        //         input.classList.add('is-invalid');
        //         return;
        //     }
            
        //     input.classList.remove('is-invalid');
        //     const grade = getGradeFromMarks(marks);
            
        //     // Update grade display
        //     const gradeBadge = document.getElementById('grade-' + studentId);
        //     if (gradeBadge) {
        //         gradeBadge.textContent = grade;
        //         gradeBadge.className = 'badge grade-badge';
        //         const gradeClass = 'grade-' + grade.replace('+', 'plus');
        //         gradeBadge.classList.add(gradeClass);
        //         log('Updated grade for', studentId, 'to', grade);
        //     }
        // },
        
        // Calculate all grades from current input values
calculateAllGrades: function() {
    log('Calculating all grades after save...');
    
    document.querySelectorAll('.marks-input').forEach(input => {
        const marks = parseFloat(input.value);
        const studentId = input.getAttribute('data-student-id');
        
        if (!isNaN(marks) && marks >= 0 && marks <= 100) {
            const grade = getGradeFromMarks(marks);
            
            // Update grade display
            const gradeBadge = document.getElementById('grade-' + studentId);
            if (gradeBadge) {
                gradeBadge.textContent = grade;
                gradeBadge.className = 'badge grade-badge';
                const gradeClass = 'grade-' + grade.replace('+', 'plus');
                gradeBadge.classList.add(gradeClass);
                log('Updated grade for', studentId, 'to', grade);
                
                // Add animation
                gradeBadge.style.transition = 'all 0.3s';
                gradeBadge.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    gradeBadge.style.transform = 'scale(1)';
                }, 300);
            }
        }
    });
    
    this.showToast('Grades updated!', 'info', 1500);
},
        // Helper function for instructions:)
        viewInstructions: function() {
            const instructions = `
                <h5>Marks Entry Instructions</h5>
                <ol>
                    <li>Enter marks (0-100) for each student</li>
                    <li>Grade is calculated automatically</li>
                    <li>Click "Save Marks" to submit for verification</li>
                    <li>Status changes to "Pending" after saving</li>
                    <li>Admin will verify/reject your marks</li>
                    <li>You can update marks until they are verified</li>
                </ol>
                <p><strong>Note:</strong> Once admin verifies, you cannot change marks.</p>
            `;
            
            this.showAlert('info', instructions);
        },


    // Save all marks 
    saveAllMarks: function() {
        log('Saving all marks...');
        
        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        
        // Disable button and show loading
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';
        
        // Collect marks data
        const marksData = {};
        let isValid = true;
        
        document.querySelectorAll('.marks-input:not([readonly])').forEach(input => {
            const studentId = input.dataset.studentId;
            const marks = parseFloat(input.value);
            
            if (isNaN(marks) || marks < 0 || marks > 100) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
                marksData[studentId] = marks;
            }
        });
        
        if (!isValid) {
            this.showToast('Some marks are invalid (must be 0-100)', 'warning', 5000);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
            return false;
        }
        
        if (Object.keys(marksData).length === 0) {
            this.showToast('No marks entered. Please enter marks before saving.', 'warning', 5000);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
            return false;
        }
        
        // Prepare data for AJAX
        const formData = new FormData();
        formData.append('class_id', currentClassId);
        formData.append('subject_id', currentSubjectId);
        formData.append('teacher_id', currentTeacherId);
        
        // Add marks
        for (const [studentId, marks] of Object.entries(marksData)) {
            formData.append(`marks[${studentId}]`, marks);
        }
        
        log('Sending data for', Object.keys(marksData).length, 'students');
        
        // Submit via AJAX
        fetch('Results/save_marks.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                log('Save successful:', data);
                
                // Calculate values
                const inserted = data.summary?.inserted || 0;
                const updated = data.summary?.updated || 0;
                const skipped = data.summary?.skipped || 0;
                const verifiedSkipped = data.summary?.verified_skipped || 0;
                const savedCount = inserted + updated;
                
                // Show success toast
                this.showToast(
                    `Saved marks for ${savedCount} student(s)`,
                    'success',
                    3000
                );
                
                this.refreshTableData();

                // Update status badges
                this.updateStatusToPending();
                
                // Calculate grades
                this.calculateAllGrades();
                
                // Reset save button
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-circle"></i> Save Marks';
                
                // Show success message (ONCE!)
                const message = data.message || `Saved marks for ${savedCount} student(s)`;
                
                // Remove any existing success messages
                const existingAlerts = document.querySelectorAll('.alert-success');
                existingAlerts.forEach(alert => alert.remove());
                
                // Create new success message
                let successHtml = `
                    <div class="alert alert-success alert-dismissible fade show mt-3">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        ${message}
                `;
                
                if (skipped > 0 || verifiedSkipped > 0) {
                    successHtml += `<br><small class="text-muted">Error: ${skipped} student(s) were skipped (verified or empty)</small>`;
                }
                
                successHtml += `<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                
                const cardBody = document.querySelector('.card-body');
                if (cardBody) {
                    cardBody.insertAdjacentHTML('afterbegin', successHtml);
                }
                
                
                
            } else {
                this.showToast('Error: ' + (data.message || 'Failed to save'), 'danger', 5000);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            log('Save error:', error);
            this.showToast('Network error: ' + error.message, 'danger', 5000);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
        
        return false;
    },

    // To refresh the table data
    // To refresh the table data
refreshTableData: function() {
    log('Refreshing table data...');
    
    const classId = currentClassId;
    const subjectId = currentSubjectId;
    const subjectName = currentSubjectName;
    
    // Show loading on table
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        tableContainer.style.opacity = '0.5';
    }
    
    // Fetch fresh data
    fetch(`Results/get_students_for_marks.php?class_id=${classId}&subject_id=${subjectId}&subject_name=${encodeURIComponent(subjectName)}`)
        .then(response => response.text())
        .then(html => {
            // Parse the HTML to get just the table
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('table');
            const newMarksContainer = doc.getElementById('marksContainer');
            
            if (newTable && newMarksContainer) {
                // Replace the table
                const oldTable = document.querySelector('table');
                if (oldTable) {
                    oldTable.innerHTML = newTable.innerHTML;
                }
                
                // Just recalculate grades - no need to reattach listeners
                // because the marks inputs are read-only now
                this.calculateAllGrades();
                
                log('Table refreshed successfully');
            }
            
            // Restore opacity
            if (tableContainer) {
                tableContainer.style.opacity = '1';
            }
        })
        .catch(error => {
            log('Error refreshing table:', error);
            if (tableContainer) {
                tableContainer.style.opacity = '1';
            }
        });
},
        
        // Test function
        test: function() {
            alert('ResultsMarks system is working!\n\nCurrent Subject: ' + currentSubjectName);
        }
    };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready for marks system');
    
    // Check if we're on marks entry page
    if (document.getElementById('marksContainer')) {
        console.log('Initializing ResultsMarks...');
        ResultsMarks.init();
    }
});

// Export to window
// window.ResultsMarks = ResultsMarks;