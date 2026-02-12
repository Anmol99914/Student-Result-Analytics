// assign-teachers.js - Pure JavaScript, no inline scripts
class AssignTeacherSubjects {
    constructor() {
        this.teacherId = document.getElementById('teacherId')?.value;
        this.teacherName = document.getElementById('teacherName')?.value;
        this.currentClassId = '';
        this.currentFaculty = '';
        this.currentSemester = '';
        this.assignedSubjects = [];
        
        this.init();
    }
    
    init() {
        console.log('AssignTeacherSubjects initialized');
        this.bindEvents();
    }
    
    bindEvents() {
        // Class selection change
        const classSelect = document.getElementById('classSelect');
        if (classSelect) {
            classSelect.addEventListener('change', (e) => this.handleClassChange(e));
        }
        
        // Save button click
        const saveBtn = document.getElementById('saveAssignmentsBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => this.saveAssignments(e));
        }
    }
    
    handleClassChange(event) {
        const select = event.target;
        const classId = select.value;
        
        if (!classId) {
            document.getElementById('subjectListContainer').innerHTML = '';
            document.getElementById('saveAssignmentsBtn').disabled = true;
            return;
        }
        
        // Get class data
        const selectedOption = select.options[select.selectedIndex];
        this.currentClassId = classId;
        this.currentFaculty = selectedOption.dataset.faculty;
        this.currentSemester = selectedOption.dataset.semester;
        
        // Load subjects
        this.loadSubjects(classId);
    }
    
    loadSubjects(classId) {
        const container = document.getElementById('subjectListContainer');
        
        // Show loading
        container.innerHTML = `
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Loading subjects for ${this.currentFaculty} - Semester ${this.currentSemester}...</p>
            </div>
        `;
        
        // Fetch subjects and assignments
        Promise.all([
            fetch(`api/get_class_subjects.php?class_id=${classId}`),
            fetch(`api/get_teacher_assignments.php?teacher_id=${this.teacherId}&class_id=${classId}`)
        ])
        .then(async ([subjectsRes, assignmentsRes]) => {
            // Check if responses are OK
            if (!subjectsRes.ok) throw new Error(`Subjects API: ${subjectsRes.status}`);
            if (!assignmentsRes.ok) throw new Error(`Assignments API: ${assignmentsRes.status}`);
            
            // Get response text first to debug
            const subjectsText = await subjectsRes.text();
            const assignmentsText = await assignmentsRes.text();
            
            console.log('Subjects raw:', subjectsText.substring(0, 100));
            console.log('Assignments raw:', assignmentsText.substring(0, 100));
            
            // Parse JSON
            const subjects = JSON.parse(subjectsText);
            const assignments = JSON.parse(assignmentsText);
            
            if (subjects.success) {
                this.assignedSubjects = assignments.success ? assignments.assigned_ids : [];
                this.duplicateSubjects = assignments.duplicates || {};
                this.renderSubjectList(subjects.data, this.assignedSubjects, this.duplicateSubjects);
                document.getElementById('saveAssignmentsBtn').disabled = false;
            } else {
                throw new Error(subjects.error || 'Failed to load subjects');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="error-message">
                    <strong>Error loading subjects:</strong> ${error.message}
                    <br>
                    <button onclick="window.location.reload()" class="btn btn-sm btn-primary mt-2">
                        Try Again
                    </button>
                </div>
            `;
            document.getElementById('saveAssignmentsBtn').disabled = true;
        });
    }
    
    renderSubjectList(subjects, assignedIds = [], duplicates = {}) {
        const container = document.getElementById('subjectListContainer');
        
        if (!subjects || subjects.length === 0) {
            container.innerHTML = `
                <div class="no-subjects">
                    <i class="bi bi-book" style="font-size: 48px; opacity: 0.5;"></i>
                    <h5 style="margin: 15px 0 5px;">No Subjects Found</h5>
                    <p style="color: #666;">No subjects available for ${this.currentFaculty} - Semester ${this.currentSemester}</p>
                </div>
            `;
            return;
        }
        
        let html = `
            <div class="selection-summary">
                <span>
                    <i class="bi bi-info-circle"></i> 
                    ${this.currentFaculty} - Semester ${this.currentSemester}
                </span>
                <span id="selectedCount">0 subjects selected</span>
            </div>
            <div style="margin-bottom: 15px;">
                <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-check-all"></i> Select All
                </button>
                <button type="button" id="deselectAllBtn" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i> Deselect All
                </button>
            </div>
            <div class="subject-list">
        `;
        
        subjects.forEach(subject => {
            const isAssigned = assignedIds.includes(subject.subject_id);
            const duplicate = duplicates[subject.subject_id];
            
            html += `
                <div class="subject-item" data-subject-id="${subject.subject_id}">
                    <label class="subject-label">
                        <div style="display: flex; align-items: center; width: 100%;">
                            <input type="checkbox" 
                                   class="subject-checkbox" 
                                   value="${subject.subject_id}"
                                   ${isAssigned ? 'checked' : ''}
                                   ${duplicate ? 'disabled' : ''}
                                   onchange="window.assignTeacherSubjects.updateSelectionSummary()">
                            <span style="flex: 1;">
                                <span class="subject-name">${this.escapeHtml(subject.subject_name)}</span>
                                ${subject.subject_code ? 
                                    `<span class="subject-code">(${this.escapeHtml(subject.subject_code)})</span>` : ''}
                                ${subject.credits ? 
                                    `<span class="subject-credits">${subject.credits} cr</span>` : ''}
                                ${duplicate ? 
                                    `<span class="assigned-badge">Assigned to ${this.escapeHtml(duplicate.teacher_name)}</span>` : ''}
                            </span>
                        </div>
                    </label>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
        
        // Re-attach event listeners
        document.getElementById('selectAllBtn')?.addEventListener('click', () => this.selectAll());
        document.getElementById('deselectAllBtn')?.addEventListener('click', () => this.deselectAll());
        
        this.updateSelectionSummary();
    }
    
    checkDuplicateAssignments(subjects, assignedIds) {
        // This will be enhanced with actual duplicate check from server
        const duplicates = {};
        // For now, just mark subjects already assigned to this teacher/class
        assignedIds.forEach(id => {
            duplicates[id] = true;
        });
        return duplicates;
    }
    
    updateSelectionSummary() {
        const checkboxes = document.querySelectorAll('.subject-checkbox:enabled');
        const checked = document.querySelectorAll('.subject-checkbox:enabled:checked');
        const count = checked.length;
        
        const summary = document.getElementById('selectedCount');
        if (summary) {
            summary.textContent = `${count} of ${checkboxes.length} subjects selected`;
        }
    }
    
    selectAll() {
        document.querySelectorAll('.subject-checkbox:enabled').forEach(cb => {
            cb.checked = true;
        });
        this.updateSelectionSummary();
    }
    
    deselectAll() {
        document.querySelectorAll('.subject-checkbox:enabled').forEach(cb => {
            cb.checked = false;
        });
        this.updateSelectionSummary();
    }
    
    saveAssignments() {
        const checkboxes = document.querySelectorAll('.subject-checkbox:checked');
        const subjectIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (!this.currentClassId) {
            alert('❌ Please select a class');
            return;
        }
        
        // Confirm duplicate assignments
        const duplicateSubjects = document.querySelectorAll('.subject-checkbox:checked[disabled]');
        if (duplicateSubjects.length > 0) {
            if (!confirm(`⚠️ ${duplicateSubjects.length} subject(s) are already assigned to another teacher in this class. Continue anyway?`)) {
                return;
            }
        }
        
        const saveBtn = document.getElementById('saveAssignmentsBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        saveBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('teacher_id', this.teacherId);
        formData.append('class_id', this.currentClassId);
        subjectIds.forEach(id => formData.append('subject_ids[]', id));
        
        fetch('api/assign_teacher_subjects.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Subjects assigned successfully!');
                
                // ===== FIXED: Use AJAX instead of page reload =====
                if (window.parent && typeof window.parent.loadPage === 'function') {
                    // If inside admin panel iframe/window
                    window.parent.loadPage('pages/teacher_management.php');
                    
                    // Check if this is in a modal/popup
                    if (window.opener) {
                        window.close();
                    } else {
                        // Go back in history
                        window.history.back();
                    }
                } else if (typeof window.loadPage === 'function') {
                    // If loadPage is available in current window
                    window.loadPage('pages/teacher_management.php');
                } else {
                    // Fallback to normal redirect
                    window.location.href = 'pages/teacher_management.php';
                }
            } else {
                throw new Error(data.error || 'Failed to save');
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.assignTeacherSubjects = new AssignTeacherSubjects();
});

// Handle cancel button
document.getElementById('cancelBtn')?.addEventListener('click', function() {
    if (window.parent && typeof window.parent.loadPage === 'function') {
        window.parent.loadPage('pages/teacher_management.php');
    } else if (typeof window.loadPage === 'function') {
        window.loadPage('pages/teacher_management.php');
    } else {
        window.location.href = 'pages/teacher_management.php';
    }
});