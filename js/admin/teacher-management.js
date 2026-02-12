// teacher-management.js - COMPLETE VERSION WITH ALL FEATURES
(function() {
    'use strict';
    
    console.log('Teacher Management module loading...');
    
    // Configuration
    const CONFIG = {
        // apiUrl: '../admin/api/get_teachers.php',
        apiUrl: '/Student_Result_Analytics/PHP_Files/admin/api/get_teachers.php',
        containerId: 'teachers-container'
    };
    
    // Check if already initialized
    if (window.teacherManager) {
        console.log('teacherManager already exists');
        if (typeof window.teacherManager.init === 'function') {
            window.teacherManager.init();
        }
        return;
    }
    
    // Teacher Manager - ALL METHODS IN ONE OBJECT
    const TeacherManager = {
        currentTab: 'active',
        searchTerm: '',
        
        // ========== INITIALIZATION ==========
        init: function() {
            console.log('TeacherManager.init() called');
            
            if (!this.validateEnvironment()) {
                return;
            }
            
            this.renderUI();
            this.loadTeachers();
        },
        
        validateEnvironment: function() {
            const container = document.getElementById(CONFIG.containerId);
            if (!container) {
                console.error('Container not found:', CONFIG.containerId);
                return false;
            }
            return true;
        },
        
        // ========== UI RENDERING ==========
        renderUI: function() {
            const container = document.getElementById(CONFIG.containerId);
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-people"></i> Teacher Management
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Search and Controls -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="teacherSearch" 
                                           placeholder="Search teachers by name or email...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-success" id="addTeacherBtn">
                                    <i class="bi bi-person-plus"></i> Add Teacher
                                </button>
                            </div>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="btn-group mb-4" role="group">
                            <button type="button" class="btn btn-primary active" data-tab="active">
                                Active Teachers
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-tab="inactive">
                                Inactive Teachers
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-tab="all">
                                All Teachers
                            </button>
                        </div>
                        
                        <!-- Table Container -->
                        <div id="teachers-table-container">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                                <p class="mt-2">Loading teachers...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add event listeners
            this.setupTabs();
            this.setupSearch();
            this.setupAddButton();
        },
        
        setupTabs: function() {
            document.querySelector('.btn-group').addEventListener('click', (e) => {
                const tabBtn = e.target.closest('[data-tab]');
                if (tabBtn) {
                    this.switchTab(tabBtn.dataset.tab);
                }
            });
        },
        
        setupSearch: function() {
            const searchInput = document.getElementById('teacherSearch');
            const clearBtn = document.getElementById('clearSearch');
            
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.searchTeachers(e.target.value);
                    }, 300);
                });
            }
            
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    document.getElementById('teacherSearch').value = '';
                    this.searchTerm = '';
                    this.loadTeachers();
                });
            }
        },
        
        setupAddButton: function() {
            document.getElementById('addTeacherBtn').addEventListener('click', () => {
                this.showAddTeacherForm();
            });
        },
        
        // ========== CORE FUNCTIONS ==========
        switchTab: function(tabName) {
            console.log('Switching to tab:', tabName);
            this.currentTab = tabName;
            
            // Update UI
            document.querySelectorAll('[data-tab]').forEach(btn => {
                if (btn.dataset.tab === tabName) {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-primary', 'active');
                } else {
                    btn.classList.remove('btn-primary', 'active');
                    btn.classList.add('btn-outline-primary');
                }
            });
            
            this.loadTeachers();
        },
        
        async loadTeachers() {
            const container = document.getElementById('teachers-table-container');
            if (!container) return;
            
            // Show loading
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading ${this.currentTab} teachers...</p>
                </div>
            `;
            
            try {
                const url = `${CONFIG.apiUrl}?status=${this.currentTab}`;
                console.log('Fetching from:', url);
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                console.log('API Response:', data);
                
                if (data.success) {
                    this.displayTeachers(data.teachers || []);
                    // Apply search filter if active
                    if (this.searchTerm) {
                        this.searchTeachers(this.searchTerm);
                    }
                } else {
                    throw new Error(data.error || 'API error');
                }
                
            } catch (error) {
                console.error('Error:', error);
                this.showError(error.message);
            }
        },
        
        displayTeachers: function(teachers) {
            const container = document.getElementById('teachers-table-container');
            
            if (!teachers || teachers.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No teachers found.
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            teachers.forEach(teacher => {
                const created = teacher.created_at 
                    ? new Date(teacher.created_at).toLocaleDateString() 
                    : 'N/A';
                    
                html += `
                    <tr>
                        <td><strong>#${teacher.teacher_id}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 36px; height: 36px;">
                                    ${teacher.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <strong>${teacher.name}</strong>
                                </div>
                            </div>
                        </td>
                        <td>${teacher.email}</td>
                        <td>
                            <span class="badge ${teacher.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                ${teacher.status}
                            </span>
                        </td>
                        <td><small class="text-muted">${created}</small></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" title="Edit" 
                                        onclick="window.teacherManager.editTeacher(${teacher.teacher_id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" title="View Details"
                                        onclick="window.teacherManager.viewTeacher(${teacher.teacher_id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="${teacher.status === 'active' ? 'Deactivate' : 'Activate'}"
                                        onclick="window.teacherManager.toggleStatus(${teacher.teacher_id}, '${teacher.status}')">
                                    <i class="bi bi-power"></i>
                                </button>
                                <button class="btn btn-outline-success btn-sm" 
                                        title="Assign Subjects"
                                        onclick="window.teacherManager.openAssignModal(${teacher.teacher_id}, '${teacher.name}')">
                                    <i class="bi bi-book"></i> Assign
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        },
        
        searchTeachers: function(searchTerm) {
            this.searchTerm = searchTerm;
            const rows = document.querySelectorAll('#teachers-table-container tbody tr');
            
            if (!searchTerm.trim()) {
                rows.forEach(row => row.style.display = '');
                return;
            }
            
            const term = searchTerm.toLowerCase();
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(term) || email.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update count display
            const countElement = document.querySelector('#teachers-table-container h5');
            if (countElement && visibleCount > 0) {
                countElement.textContent = `Teachers (${visibleCount} of ${rows.length} shown)`;
            }
        },
        
        showError: function(message) {
            const container = document.getElementById('teachers-table-container');
            
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error</h5>
                    <p>${message}</p>
                    <button class="btn btn-sm btn-danger" onclick="window.teacherManager.loadTeachers()">
                        Try Again
                    </button>
                </div>
            `;
        },
        
        // ========== TEACHER CRUD OPERATIONS ==========
        showAddTeacherForm: function() {
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="addTeacherModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-person-plus"></i> Add New Teacher
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addTeacherForm">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password *</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="submitAddTeacher">Add Teacher</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addTeacherModal'));
            modal.show();
            
            // Handle form submission
            document.getElementById('submitAddTeacher').addEventListener('click', () => {
                this.submitAddTeacherForm();
            });
            
            // Remove modal when hidden
            document.getElementById('addTeacherModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        },
        
        submitAddTeacherForm: async function() {
            const form = document.getElementById('addTeacherForm');
            const formData = new FormData(form);
            
            // Show loading
            const submitBtn = document.getElementById('submitAddTeacher');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../../PHP_Files/admin/api/add_teacher.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Teacher added successfully!');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTeacherModal'));
                    if (modal) modal.hide();
                    // Refresh list
                    this.loadTeachers();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            } catch (error) {
                alert('❌ Network error: ' + error.message);
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        },
        
        editTeacher: async function(teacherId) {
            console.log('Editing teacher #' + teacherId);
            
            try {
                // Fetch teacher details
                const response = await fetch(`../../PHP_Files/admin/api/get_teacher.php?teacher_id=${teacherId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load teacher details');
                }
                
                this.showEditTeacherForm(data.teacher);
                
            } catch (error) {
                console.error('Error loading teacher:', error);
                alert('❌ Error loading teacher details: ' + error.message);
            }
        },
        
        showEditTeacherForm: function(teacher) {
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="editTeacherModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-pencil"></i> Edit Teacher
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editTeacherForm">
                                    <input type="hidden" name="teacher_id" value="${teacher.teacher_id}">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name" 
                                               value="${teacher.name}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="${teacher.email}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">New Password (leave blank to keep current)</label>
                                        <input type="password" class="form-control" name="password">
                                        <small class="text-muted">Only enter if you want to change the password</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="active" ${teacher.status === 'active' ? 'selected' : ''}>Active</option>
                                            <option value="inactive" ${teacher.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                        </select>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        Teacher ID: <strong>#${teacher.teacher_id}</strong> | 
                                        Created: ${teacher.created_at ? new Date(teacher.created_at).toLocaleDateString() : 'N/A'}
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="submitEditTeacher">Update Teacher</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
            modal.show();
            
            // Handle form submission
            document.getElementById('submitEditTeacher').addEventListener('click', () => {
                this.submitEditTeacherForm();
            });
            
            // Remove modal when hidden
            document.getElementById('editTeacherModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        },
        
        submitEditTeacherForm: async function() {
            const form = document.getElementById('editTeacherForm');
            const formData = new FormData(form);
            
            // Show loading
            const submitBtn = document.getElementById('submitEditTeacher');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../../PHP_Files/admin/api/update_teacher.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Teacher updated successfully!');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTeacherModal'));
                    if (modal) modal.hide();
                    // Refresh list
                    this.loadTeachers();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            } catch (error) {
                alert('❌ Network error: ' + error.message);
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        },
        
        viewTeacher: async function(teacherId) {
            console.log('Viewing teacher #' + teacherId);
            
            try {
                // Fetch teacher details with stats
                const response = await fetch(`../admin/api/get_teacher.php?teacher_id=${teacherId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load teacher details');
                }
                
                this.showTeacherDetails(data.teacher, data.stats);
                
            } catch (error) {
                console.error('Error loading teacher:', error);
                alert('❌ Error loading teacher details: ' + error.message);
            }
        },
        
        showTeacherDetails: function(teacher, stats) {
            const createdDate = teacher.created_at 
                ? new Date(teacher.created_at).toLocaleDateString()
                : 'N/A';
            
            const modalHTML = `
                <div class="modal fade" id="viewTeacherModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-person-badge"></i> Teacher Details
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                             style="width: 100px; height: 100px; font-size: 36px;">
                                            ${teacher.name.charAt(0).toUpperCase()}
                                        </div>
                                        <span class="badge ${teacher.status === 'active' ? 'bg-success' : 'bg-secondary'} fs-6">
                                            ${teacher.status}
                                        </span>
                                    </div>
                                    
                                    <div class="col-md-9">
                                        <h4 class="mb-3">${teacher.name}</h4>
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title text-muted">Contact Information</h6>
                                                        <p class="mb-1"><i class="bi bi-envelope me-2"></i> ${teacher.email}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title text-muted">Account Information</h6>
                                                        <p class="mb-0"><i class="bi bi-calendar me-2"></i> Created: ${createdDate}</p>
                                                        <p class="mb-0"><i class="bi bi-tag me-2"></i> ID: #${teacher.teacher_id}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h2 class="text-primary">${stats?.subject_count || 0}</h2>
                                                        <p class="text-muted mb-0">Subjects Assigned</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h2 class="text-success">${stats?.class_count || 0}</h2>
                                                        <p class="text-muted mb-0">Classes Assigned</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- REMOVED QUICK ACTIONS SECTION -->
                                        <hr>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-info-circle"></i> 
                                            Teacher ID: #${teacher.teacher_id} | 
                                            Created: ${createdDate}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('viewTeacherModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewTeacherModal'));
            modal.show();
            
            // Clean up on hide
            document.getElementById('viewTeacherModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        },
        
        toggleStatus: async function(teacherId, currentStatus, fromModal = false) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = currentStatus === 'active' ? 'deactivate' : 'activate';
            
            if (!confirm(`Are you sure you want to ${action} this teacher?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('teacher_id', teacherId);
                formData.append('status', newStatus);
                
                const response = await fetch('../../PHP_Files/admin/api/update_teacher_status.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    this.showToast(`✅ Teacher ${action}d successfully!`, 'success');
                    
                    // 1. Refresh the main teachers table
                    this.loadTeachers();
                    
                    // 2. If called from modal, update modal UI
                    if (fromModal) {
                        this.updateModalAfterStatusChange(teacherId, newStatus);
                    }
                    
                    // 3. Update status in current table row (if visible)
                    this.updateRowStatus(teacherId, newStatus);
                    
                } else {
                    this.showToast(`❌ Error: ${data.error}`, 'error');
                }
            } catch (error) {
                this.showToast(`❌ Network error: ${error.message}`, 'error');
            }
        },
        
        // Helper method to update modal after status change
        updateModalAfterStatusChange: function(teacherId, newStatus) {
            const modal = document.getElementById('viewTeacherModal');
            if (!modal) return;
            
            // Update status badge in modal (near avatar)
            const statusBadge = modal.querySelector('.badge.fs-6');
            if (statusBadge) {
                statusBadge.className = newStatus === 'active' ? 'badge bg-success fs-6' : 'badge bg-secondary fs-6';
                statusBadge.textContent = newStatus;
            }
            
            // Update button text
            const toggleBtn = modal.querySelector('button.btn-warning');
            if (toggleBtn) {
                const action = newStatus === 'active' ? 'Deactivate' : 'Activate';
                toggleBtn.innerHTML = `<i class="bi bi-power"></i> ${action}`;
                toggleBtn.setAttribute('onclick', `window.teacherManager.toggleStatus(${teacherId}, '${newStatus}', true)`);
            }
            
            // Update teacher object status
            if (window.currentTeacherInModal) {
                window.currentTeacherInModal.status = newStatus;
            }
        },
        
        // Helper method to update table row
        updateRowStatus: function(teacherId, newStatus) {
            const rows = document.querySelectorAll('#teachers-table-container tbody tr');
            rows.forEach(row => {
                const idCell = row.querySelector('td:first-child strong');
                if (idCell && idCell.textContent.includes(`#${teacherId}`)) {
                    // Update status badge
                    const statusBadge = row.querySelector('.badge');
                    if (statusBadge) {
                        statusBadge.className = newStatus === 'active' ? 'badge bg-success' : 'badge bg-secondary';
                        statusBadge.textContent = newStatus;
                    }
                    
                    // Update toggle button tooltip
                    const toggleBtn = row.querySelector('button.btn-outline-warning');
                    if (toggleBtn) {
                        const action = newStatus === 'active' ? 'Deactivate' : 'Activate';
                        toggleBtn.title = action;
                        toggleBtn.setAttribute('onclick', `window.teacherManager.toggleStatus(${teacherId}, '${newStatus}')`);
                    }
                }
            });
        },
        
        // Toast notification helper
        showToast: function(message, type = 'info') {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.teacher-toast');
            existingToasts.forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `teacher-toast alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
            toast.innerHTML = `
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
            
            // Add CSS animation
            if (!document.querySelector('#toast-animation')) {
                const style = document.createElement('style');
                style.id = 'toast-animation';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }
        },

// Open Assign Subjects Modal
openAssignModal: function(teacherId, teacherName) {
    console.log('Opening assign modal for teacher:', teacherId);
    
    // Set modal title
    const modalTitle = document.querySelector('#assignSubjectsModal .modal-title');
    if (modalTitle) {
        modalTitle.innerHTML = `<i class="bi bi-book"></i> Assign Subjects - ${teacherName}`;
    }
    
    // Load content
    const modalBody = document.getElementById('assignSubjectsModalBody');
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status"></div>
            <p class="mt-2">Loading subjects...</p>
        </div>
    `;
    
    // Fetch the assign form
    fetch(`assign_teachers_modal.php?teacher_id=${teacherId}`)
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
            
            // === FIX: Attach save button event listener ===
            const saveBtn = document.getElementById('saveAssignmentsBtn');
            if (saveBtn) {
                // Remove any existing listeners
                const newSaveBtn = saveBtn.cloneNode(true);
                saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
                
                // Add new listener
                newSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    saveAssignmentsFromModal(teacherId);
                });
            }
            
            // Attach class select event listener
            const classSelect = document.getElementById('classSelect');
            if (classSelect) {
                classSelect.addEventListener('change', function() {
                    const classId = this.value;
                    if (classId) {
                        loadSubjectsDirectly(teacherId, classId);
                    } else {
                        document.getElementById('subjectListContainer').innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-arrow-up-circle" style="font-size: 2rem;"></i>
                                <p class="mt-2">Select a class to view subjects</p>
                            </div>
                        `;
                    }
                });
            }
        })
        .catch(error => {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error loading assign form: ${error.message}
                </div>
            `;
        });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('assignSubjectsModal'));
    modal.show();
},

// Initialize assign form
initAssignForm: function(teacherId) {
    // Class select change handler
    const classSelect = document.getElementById('classSelect');
    if (classSelect) {
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            if (classId) {
                loadSubjectsForClass(teacherId, classId);
            } else {
                document.getElementById('subjectListContainer').innerHTML = '';
            }
        });
    }
    
    // Save button handler
    const saveBtn = document.getElementById('saveAssignmentsBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveAssignments(teacherId);
        });
    }
}
    };
    
    // Export to window
    window.teacherManager = TeacherManager;
    
    console.log('TeacherManager module loaded');
    
    // Auto-initialize with multiple attempts
if (document.getElementById('teachers-container')) {
    console.log('Teachers container found, scheduling initialization...');
    
    function tryInit() {
        if (window.teacherManager) {
            console.log('Auto-initializing teacherManager...');
            window.teacherManager.init();
            return true;
        }
        return false;
    }
    
    // Try multiple times
    tryInit();
    setTimeout(tryInit, 100);
    setTimeout(tryInit, 300);
    setTimeout(tryInit, 500);
    setTimeout(tryInit, 1000);
}
})();

// ===== DIRECT SUBJECT LOADING FUNCTION =====
function loadSubjectsDirectly(teacherId, classId) {
    console.log('Loading subjects for teacher:', teacherId, 'class:', classId);
    
    const container = document.getElementById('subjectListContainer');
    if (!container) return;
    
    // Get class info from select
    const classSelect = document.getElementById('classSelect');
    const selectedOption = classSelect?.options[classSelect.selectedIndex];
    const faculty = selectedOption?.dataset.faculty || '';
    const semester = selectedOption?.dataset.semester || '';
    
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-success" role="status"></div>
            <p class="mt-2">Loading subjects for ${faculty} - Semester ${semester}...</p>
        </div>
    `;
    
    // Fetch subjects and assignments
    Promise.all([
        fetch(`api/get_class_subjects.php?class_id=${classId}`),
        fetch(`api/get_teacher_assignments.php?teacher_id=${teacherId}&class_id=${classId}`)
    ])
    .then(async ([subjectsRes, assignmentsRes]) => {
        if (!subjectsRes.ok) throw new Error('Failed to load subjects');
        if (!assignmentsRes.ok) throw new Error('Failed to load assignments');
        
        const subjects = await subjectsRes.json();
        const assignments = await assignmentsRes.json();
        
        if (subjects.success) {
            displaySubjectsInModal(subjects.data, assignments.assigned_ids || [], assignments.duplicates || {});
            document.getElementById('saveAssignmentsBtn').disabled = false;
        } else {
            throw new Error(subjects.error || 'Failed to load subjects');
        }
    })
    .catch(error => {
        container.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                Error: ${error.message}
                <button class="btn btn-sm btn-danger ms-2" onclick="window.location.reload()">Retry</button>
            </div>
        `;
    });
}

// ===== SAVE ASSIGNMENTS FROM MODAL =====
function saveAssignmentsFromModal(teacherId) {
    console.log('Saving assignments for teacher:', teacherId);
    
    const classSelect = document.getElementById('classSelect');
    const classId = classSelect?.value;
    
    if (!classId) {
        alert('❌ Please select a class');
        return;
    }
    
    // Get selected subjects
    const checkboxes = document.querySelectorAll('.subject-checkbox:checked:not(:disabled)');
    const subjectIds = Array.from(checkboxes).map(cb => cb.value);
    
    // Confirm if no subjects selected
    if (subjectIds.length === 0) {
        if (!confirm('No subjects selected. This will remove all assignments for this class. Continue?')) {
            return;
        }
    }
    
    // Show loading state
    const saveBtn = document.getElementById('saveAssignmentsBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    saveBtn.disabled = true;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('teacher_id', teacherId);
    formData.append('class_id', classId);
    subjectIds.forEach(id => formData.append('subject_ids[]', id));
    
    // Send request
    fetch('api/assign_teacher_subjects.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success message
            alert('✅ Subjects assigned successfully!');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignSubjectsModal'));
            if (modal) modal.hide();
            
            // Refresh teacher list to show updated assignments
            if (window.teacherManager && typeof window.teacherManager.loadTeachers === 'function') {
                window.teacherManager.loadTeachers();
            }
        } else {
            throw new Error(data.error || 'Failed to save assignments');
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Make it globally available
window.saveAssignmentsFromModal = saveAssignmentsFromModal;

function displaySubjectsInModal(subjects, assignedIds = [], duplicates = {}) {
    const container = document.getElementById('subjectListContainer');
    
    if (!subjects || subjects.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                No subjects found for this class.
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold">Select Subjects:</span>
            <span id="selectedCount" class="badge bg-primary">0 selected</span>
        </div>
        <div style="max-height: 300px; overflow-y: auto;" class="border rounded p-2">
    `;
    
    subjects.forEach(subject => {
        const isAssigned = assignedIds.includes(subject.subject_id);
        const duplicate = duplicates[subject.subject_id];
        
        html += `
            <div class="form-check py-1">
                <input class="form-check-input subject-checkbox" 
                       type="checkbox" 
                       value="${subject.subject_id}"
                       id="sub_${subject.subject_id}"
                       ${isAssigned ? 'checked' : ''}
                       ${duplicate ? 'disabled' : ''}
                       onchange="updateSubjectCount()">
                <label class="form-check-label" for="sub_${subject.subject_id}">
                    <strong>${escapeHtml(subject.subject_name)}</strong>
                    ${subject.subject_code ? `<small class="text-muted">(${subject.subject_code})</small>` : ''}
                    ${subject.credits ? `<span class="badge bg-secondary ms-1">${subject.credits} cr</span>` : ''}
                    ${isAssigned ? '<span class="badge bg-success ms-2">Assigned</span>' : ''}
                    ${duplicate ? `<span class="badge bg-warning ms-2">Assigned to ${escapeHtml(duplicate.teacher_name)}</span>` : ''}
                </label>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    updateSubjectCount();
    
    // Ensure checkboxes update count
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSubjectCount);
    });
}

// Global helper functions
window.selectAllSubjects = function() {
    document.querySelectorAll('.subject-checkbox:not(:disabled)').forEach(cb => cb.checked = true);
    updateSubjectCount();
};

window.deselectAllSubjects = function() {
    document.querySelectorAll('.subject-checkbox:not(:disabled)').forEach(cb => cb.checked = false);
    updateSubjectCount();
};

window.updateSubjectCount = function() {
    const count = document.querySelectorAll('.subject-checkbox:checked:not(:disabled)').length;
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.textContent = count + ' selected';
};

window.escapeHtml = function(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

// Make functions globally available
window.loadSubjectsDirectly = loadSubjectsDirectly;
window.displaySubjectsInModal = displaySubjectsInModal;

