// class-management.js - Class management JavaScript

// Check if already loaded to prevent duplicate declaration
if (typeof window.ClassManager !== 'undefined') {
    console.log('ClassManager already loaded, reinitializing...');
    if (window.classManager && typeof window.classManager.init === 'function') {
        window.classManager.init();
    }
} else {
    console.log('Loading ClassManager for the first time...');
    
    // Define the class
    class ClassManager {
        constructor() {
            console.log('ClassManager constructor called');
            this.init();
        }
        
        init() {
            console.log('ClassManager.init() called');
            this.loadClasses();
            this.setupEventListeners();
        }
        
        setupEventListeners() {
            console.log('Setting up event listeners...');
            
            // Filter event listeners
            const facultyFilter = document.getElementById('facultyFilter');
            const semesterFilter = document.getElementById('semesterFilter');
            const statusFilter = document.getElementById('statusFilter');
            
            if (facultyFilter) {
                facultyFilter.addEventListener('change', () => this.filterClasses());
            }
            
            if (semesterFilter) {
                semesterFilter.addEventListener('change', () => this.filterClasses());
            }
            
            if (statusFilter) {
                statusFilter.addEventListener('change', () => this.filterClasses());
            }
            
            // Reset filter button
            const resetBtn = document.getElementById('resetFiltersBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.resetClassFilters();
                });
            }
            
            // Add class button
            const addClassBtn = document.getElementById('addClassBtn');
            if (addClassBtn) {
                addClassBtn.addEventListener('click', (e) => {
                    e.preventDefault(); // Prevent any default action
                    e.stopPropagation();
                    this.showAddClassForm();
                });
            }
            
            console.log('Event listeners setup complete');
        }
        
        loadClasses() {
            console.log('Loading classes...');
            const container = document.getElementById('classes-container');
            
            if (!container) {
                console.error('Classes container not found!');
                return;
            }
            
            // Show loading
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading classes...</p>
                </div>
            `;
            
            // Get filter values
            const faculty = document.getElementById('facultyFilter')?.value || '';
            const semester = document.getElementById('semesterFilter')?.value || '';
            const status = document.getElementById('statusFilter')?.value || '';
            
            // Build query string
            let url = '/Student_Result_Analytics/PHP_Files/admin/get_classes.php';
            const params = new URLSearchParams();
            
            if (faculty) params.append('faculty', faculty);
            if (semester) params.append('semester', semester);
            if (status) params.append('status', status);
            
            if (params.toString()) {
                url += '?' + params.toString();
            }
            
            console.log('Fetching from:', url);
            
            // Load via AJAX
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(classes => {
                    console.log('Classes loaded:', classes ? classes.length : 0);
                    this.renderClassesTable(classes);
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Error loading classes: ${error.message}
                            <button onclick="window.classManager.loadClasses()" class="btn btn-sm btn-danger ms-2">Retry</button>
                        </div>
                    `;
                });
        }
        
        renderClassesTable(classes) {
            const container = document.getElementById('classes-container');
            
            if (!container) {
                console.error('Container not found for rendering');
                return;
            }
            
            if (!classes || classes.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        No classes found. 
                        <button class="btn btn-link alert-link" onclick="window.classManager.showAddClassForm()">
                            Create your first class!
                        </button>
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Class ID</th>
                                <th>Faculty</th>
                                <th>Semester</th>
                                <th>Batch Year</th>
                                <th>Status</th>
                                <th>Students</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            classes.forEach(cls => {
                const statusBadge = cls.status === 'active' 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-secondary">Inactive</span>';
                
                const studentCount = cls.student_count || 0;
                
                html += `
                    <tr data-class-id="${cls.class_id}" data-faculty="${cls.faculty}" data-semester="${cls.semester}" data-status="${cls.status}">
                        <td><strong>${cls.class_id}</strong></td>
                        <td>
                            <span class="badge bg-primary">${cls.faculty}</span>
                        </td>
                        <td>Semester ${cls.semester}</td>
                        <td>${cls.batch_year || 'N/A'}</td>
                        <td>${statusBadge}</td>
                        <td>${studentCount} students</td>
                        <td>${cls.created_at ? new Date(cls.created_at).toLocaleDateString() : 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="window.classManager.viewClassDetails(${cls.class_id})" title="View">
                                <i class="bi bi-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="window.classManager.toggleClassStatus(${cls.class_id}, '${cls.status}')" 
                                    title="${cls.status === 'active' ? 'Deactivate' : 'Activate'}">
                                <i class="bi bi-power"></i> ${cls.status === 'active' ? 'Deactivate' : 'Activate'}
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
            console.log('Classes table rendered with', classes.length, 'rows');
        }
        
        filterClasses() {
            console.log('Filtering classes...');
            // Get filter values
            const faculty = document.getElementById('facultyFilter')?.value || '';
            const semester = document.getElementById('semesterFilter')?.value || '';
            const status = document.getElementById('statusFilter')?.value || '';
            
            const rows = document.querySelectorAll('#classes-container tbody tr');
            console.log('Found', rows.length, 'rows to filter');
            
            rows.forEach(row => {
                const rowFaculty = row.getAttribute('data-faculty');
                const rowSemester = row.getAttribute('data-semester');
                const rowStatus = row.getAttribute('data-status');
                
                let show = true;
                
                if (faculty && rowFaculty !== faculty) show = false;
                if (semester && rowSemester !== semester) show = false;
                if (status && rowStatus !== status) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }
        
        resetClassFilters() {
            console.log('Resetting filters...');
            const facultyFilter = document.getElementById('facultyFilter');
            const semesterFilter = document.getElementById('semesterFilter');
            const statusFilter = document.getElementById('statusFilter');
            
            if (facultyFilter) facultyFilter.value = '';
            if (semesterFilter) semesterFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            
            this.loadClasses();
        }
        
        showAddClassForm() {
            console.log('Showing add class form...');
            
            // Create a modal for adding class
            this.showAddClassModal();
        }
        
        showAddClassModal() {
            console.log('Creating add class modal...');
            
            // Remove any existing modals and backdrops first
            const existingModal = document.getElementById('addClassModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Remove any stuck backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // Remove modal-open class from body
            document.body.classList.remove('modal-open');
            
            // Fetch active faculties from database
            fetch('/Student_Result_Analytics/PHP_Files/admin/get_faculties.php')
                .then(response => response.json())
                .then(faculties => {
                    // Build faculty options
                    let facultyOptions = '<option value="">-- Select Faculty --</option>';
                    
                    if (faculties && faculties.length > 0) {
                        faculties.forEach(f => {
                            facultyOptions += `<option value="${f.faculty_code}">${f.faculty_name} (${f.faculty_code})</option>`;
                        });
                    }
                    
                    // Create modal HTML with dynamic faculty options
                    const modalHTML = `
                        <div class="modal fade" id="addClassModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="bi bi-plus-circle me-2"></i>Create New Class
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="addClassForm">
                                            <div class="mb-3">
                                                <label class="form-label">Faculty <span class="text-danger">*</span></label>
                                                <select name="faculty" class="form-select" required>
                                                    ${facultyOptions}
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                                <select name="semester" class="form-select" required>
                                                    <option value="">-- Select Semester --</option>
                                                    <option value="1">Semester 1</option>
                                                    <option value="2">Semester 2</option>
                                                    <option value="3">Semester 3</option>
                                                    <option value="4">Semester 4</option>
                                                    <option value="5">Semester 5</option>
                                                    <option value="6">Semester 6</option>
                                                    <option value="7">Semester 7</option>
                                                    <option value="8">Semester 8</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Batch Year</label>
                                                <input type="number" name="batch_year" class="form-control" 
                                                       value="2026" min="2020" max="2030">
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" onclick="window.classManager.submitAddClassForm()">
                                            Create Class
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add modal to body
                    document.body.insertAdjacentHTML('beforeend', modalHTML);
                    
                    // Show modal
                    const modalElement = document.getElementById('addClassModal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    
                    // Clean up properly when modal is hidden
                    modalElement.addEventListener('hidden.bs.modal', function() {
                        console.log('Modal hidden - cleaning up');
                        // Remove the modal from DOM
                        setTimeout(() => {
                            if (modalElement.parentNode) {
                                modalElement.remove();
                            }
                        }, 300);
                        
                        // Force remove any remaining backdrops
                        setTimeout(() => {
                            const backdrops = document.querySelectorAll('.modal-backdrop');
                            backdrops.forEach(backdrop => backdrop.remove());
                            document.body.classList.remove('modal-open');
                        }, 400);
                    });
                })
                .catch(error => {
                    console.error('Error loading faculties:', error);
                    alert('Could not load faculties. Please refresh and try again.');
                });
        }
        
        submitAddClassForm() {
            console.log('Submitting add class form...');
            
            const form = document.getElementById('addClassForm');
            if (!form) {
                console.error('Add class form not found');
                return;
            }
            
            // Validate form
            const faculty = form.querySelector('[name="faculty"]').value;
            const semester = form.querySelector('[name="semester"]').value;
            
            if (!faculty || !semester) {
                showAlert('warning', 'Please select faculty and semester');
                return;
            }
            
            // Show loading
            const submitBtn = document.querySelector('[onclick*="submitAddClassForm"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';
            submitBtn.disabled = true;
            
            // Prepare form data
            const formData = new FormData(form);
            formData.append('status', 'active');
            
            // Submit via AJAX
            fetch('add_class.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Add class response:', data);
                
                if (data.status === 'success') {
                    showAlert('success', data.message);
                    
                    // Close modal
                    const modalElement = document.getElementById('addClassModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                    }
                    
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;

                    setTimeout(() => {
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(backdrop => backdrop.remove());
                        document.body.classList.remove('modal-open');
                    }, 500);
                    
                    // Reload classes after short delay
                    setTimeout(() => {
                        this.loadClasses();
                    }, 500);
                    
                } else {
                    showAlert('danger', data.message || 'Error creating class');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showAlert('danger', 'Network error: ' + error.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        viewClassDetails(classId) {
            console.log('Viewing class details:', classId);
            
            // Show class details in a simple modal
            const classRow = document.querySelector(`tr[data-class-id="${classId}"]`);
            if (!classRow) {
                alert('Class details not found');
                return;
            }
            
            // Get class data from the row - FIXED: Use textContent instead of innerText
            const faculty = classRow.querySelector('td:nth-child(2) .badge')?.textContent?.trim() || 'N/A';
            const semester = classRow.querySelector('td:nth-child(3)')?.textContent?.trim() || 'N/A';
            const batchYear = classRow.querySelector('td:nth-child(4)')?.textContent?.trim() || 'N/A';
            const status = classRow.querySelector('td:nth-child(5) .badge')?.textContent?.trim() || 'active';
            const students = classRow.querySelector('td:nth-child(6)')?.textContent?.trim() || '0 students';
            const created = classRow.querySelector('td:nth-child(7)')?.textContent?.trim() || 'N/A';
            
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="viewClassModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-building"></i> Class Details (ID: #${classId})
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 35%">Class ID</th>
                                        <td><strong>#${classId}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Faculty</th>
                                        <td><span class="badge bg-primary">${faculty}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Semester</th>
                                        <td>${semester}</td>
                                    </tr>
                                    <tr>
                                        <th>Batch Year</th>
                                        <td>${batchYear}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><span class="badge bg-${status.toLowerCase().includes('active') ? 'success' : 'secondary'}">${status}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Students</th>
                                        <td>${students}</td>
                                    </tr>
                                    <tr>
                                        <th>Created Date</th>
                                        <td>${created}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('viewClassModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewClassModal'));
            modal.show();
            
            // Clean up on hide
            document.getElementById('viewClassModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
        
        
        toggleClassStatus(classId, currentStatus) {
            console.log('Toggling class status:', classId, currentStatus);
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const confirmMsg = currentStatus === 'active' 
                ? 'Are you sure you want to deactivate this class?' 
                : 'Are you sure you want to activate this class?';
            
            if (!confirm(confirmMsg)) return;
            
            fetch('update_class_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    class_id: classId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showAlert('success', data.message);
                    this.loadClasses(); // Reload classes
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Error updating class status');
            });
        }
    }
    
    // Make available globally
    window.ClassManager = ClassManager;
}

// Initialize when appropriate
function initializeClassManager() {
    console.log('initializeClassManager called');
    
    // Check if we're on the class management page
    const isClassPage = document.querySelector('.class-management-container') !== null;
    
    if (isClassPage) {
        console.log('On class management page, initializing...');
        
        if (typeof ClassManager !== 'undefined' && !window.classManager) {
            console.log('Creating new ClassManager instance');
            window.classManager = new ClassManager();
        } else if (window.classManager) {
            console.log('Reinitializing existing classManager');
            window.classManager.init();
        } else {
            console.error('ClassManager not available');
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeClassManager);

// Also initialize when page loads via AJAX
window.addEventListener('pageLoaded', function(event) {
    console.log('pageLoaded event:', event.detail.url);
    if (event.detail.url.includes('class_management.php')) {
        // Small delay to ensure DOM is ready
        setTimeout(initializeClassManager, 100);
    }
});


