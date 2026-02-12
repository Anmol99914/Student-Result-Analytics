console.log('=== student-management.js START ===');

// DELETE any existing StudentManager to avoid conflicts
if (window.StudentManager) {
    console.log('Clearing existing StudentManager...');
    delete window.StudentManager;
}

// DEFINE StudentManager IMMEDIATELY
window.StudentManager = class StudentManager {
    constructor() {
        console.log('🎯 StudentManager CONSTRUCTOR called');
        this.init();
    }
    
    init() {
        console.log('📊 StudentManager INIT called');
        
        // Load data immediately
        this.loadStudents();
        
        // Setup events
        setTimeout(() => this.setupEventListeners(), 100);
    }
    
    setupEventListeners() {
        console.log('🔗 Setting up event listeners');
        
        // Add Student button
        document.getElementById('addStudentBtn')?.addEventListener('click', () => {
            this.openAddStudentModal();
        });
        
        // Search button
        document.getElementById('searchBtn')?.addEventListener('click', () => {
            this.loadStudents();
        });
        
        // Enter key in search
        document.getElementById('searchInput')?.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') this.loadStudents();
        });
        
        // Filters
        document.getElementById('facultyFilter')?.addEventListener('change', () => this.loadStudents());
        document.getElementById('semesterFilter')?.addEventListener('change', () => this.loadStudents());
        document.getElementById('statusFilter')?.addEventListener('change', () => this.loadStudents());
        
        // Reset button
        document.getElementById('resetFiltersBtn')?.addEventListener('click', () => {
            document.getElementById('facultyFilter').value = '';
            document.getElementById('semesterFilter').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('searchInput').value = '';
            this.loadStudents();
        });
    }
    
    async loadStudents() {
        console.log('🔄 Loading students...');
        
        const container = document.getElementById('studentsContainer');
        if (!container) {
            console.error('❌ studentsContainer not found!');
            return;
        }
        
        // Show loading
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Loading student data...</p>
            </div>
        `;
        
        try {
            const path = '/Student_Result_Analytics/PHP_Files/admin/students/get_students.php';
            
            // Build query string
            const faculty = document.getElementById('facultyFilter')?.value || '';
            const semester = document.getElementById('semesterFilter')?.value || '';
            const status = document.getElementById('statusFilter')?.value || '';
            const search = document.getElementById('searchInput')?.value || '';
            
            const params = new URLSearchParams();
            if (faculty) params.append('faculty', faculty);
            if (semester) params.append('semester', semester);
            if (status) params.append('status', status);
            if (search) params.append('search', search);
            
            const url = params.toString() ? path + '?' + params.toString() : path;
            console.log(`📡 Fetching from: ${url}`);
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const students = await response.json();
            console.log(`✅ Found ${students.length} students`);
            
            this.renderStudents(students);
            this.updateStats(students); // Use this ONE function for stats
            
        } catch (error) {
            console.error('❌ Error loading students:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error loading students: ${error.message}
                    <button class="btn btn-sm btn-danger mt-2" onclick="window.studentManager.loadStudents()">
                        Retry
                    </button>
                </div>
            `;
        }
    }
    
    // For stats
    updateStats(students) {
        console.log('📊 Updating stats from', students.length, 'students');
        
        const total = students.length;
        const active = students.filter(s => s.is_active == 1).length;
        
        // Count students with due_amount > 0 OR status = 'Partial' OR 'Unpaid'
        const pending = students.filter(s => {
            const status = (s.payment_status || '').toLowerCase();
            return s.due_amount > 0 || status === 'partial' || status === 'unpaid' || status === '';
        }).length;
        
        // Recent payments (last 7 days)
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
        
        const recent = students.filter(s => {
            if (!s.payment_date) return false;
            const paymentDate = new Date(s.payment_date);
            return paymentDate >= sevenDaysAgo;
        }).length;
        
        // Update stat cards
        document.getElementById('totalStudents').textContent = total;
        document.getElementById('activeStudents').textContent = active;
        document.getElementById('pendingStudents').textContent = pending;
        document.getElementById('recentStudents').textContent = recent;
        
        console.log(`✅ Stats - Total: ${total}, Active: ${active}, Pending: ${pending}, Recent: ${recent}`);
    }
    
    renderStudents(students) {
        const container = document.getElementById('studentsContainer');
        if (!container) return;
        
        console.log(`🎨 Rendering ${students.length} students`);
        
        if (students.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No students found</div>';
            return;
        }
        
        let html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Faculty</th>
                            <th>Fee Status</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        students.forEach(s => {
            // Payment badge color
            let paymentBadge = 'secondary';
            let paymentText = s.payment_status || 'Unpaid';
            
            if (paymentText.toLowerCase() === 'paid') paymentBadge = 'success';
            else if (paymentText.toLowerCase() === 'partial') paymentBadge = 'warning';
            else if (paymentText.toLowerCase() === 'unpaid') paymentBadge = 'danger';
            
            // Format due amount
            const dueAmount = s.due_amount || 0;
            const dueText = dueAmount > 0 ? `Rs. ${dueAmount.toLocaleString()}` : '-';
            
            html += `<tr>
                <td><strong>${s.student_id}</strong></td>
                <td>${s.student_name}</td>
                <td>${s.email}</td>
                <td><span class="badge bg-primary">${s.faculty || 'N/A'}</span></td>
                <td>
                    <span class="badge bg-${paymentBadge}">
                        ${paymentText}
                    </span>
                </td>
                <td>${dueText}</td>
                <td>
                    <span class="badge bg-${s.is_active == 1 ? 'success' : 'danger'}">
                        ${s.is_active == 1 ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-info" onclick="window.studentManager.viewStudent('${s.student_id}')" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="window.studentManager.editStudent('${s.student_id}')" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="window.studentManager.toggleStudentStatus('${s.student_id}', ${s.is_active})" title="${s.is_active == 1 ? 'Deactivate' : 'Activate'}">
                        <i class="bi bi-power"></i>
                    </button>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        container.innerHTML = html;
    }
    
    // ===== MODAL FUNCTIONS =====
   
    openAddStudentModal() {
        console.log('Opening add student modal...');
        
        const existingModal = document.getElementById('addStudentModal');
        if (existingModal) existingModal.remove();
        
        // ✅ FIXED PATH - lowercase 'students'
        fetch('/Student_Result_Analytics/PHP_Files/admin/students/add_student_modal.php')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.text();
            })
            .then(html => {
                document.body.insertAdjacentHTML('beforeend', html);
                const modal = new bootstrap.Modal(document.getElementById('addStudentModal'));
                modal.show();
                
                // Clean up
                document.getElementById('addStudentModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            })
            .catch(error => {
                console.error('Error loading modal:', error);
                alert('Error loading add student form: ' + error.message);
            });
    }
    
    submitAddStudent() {
        const form = document.getElementById('addStudentForm');
        if (!form) {
            alert('Form not found');
            return;
        }
        
        const formData = new FormData(form);
        
        const submitBtn = document.querySelector('[onclick*="submitAddStudent"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
        submitBtn.disabled = true;
        
        // ✅ FIXED PATH - lowercase 'students'
        fetch('/Student_Result_Analytics/PHP_Files/admin/students/add_student.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
                if (modal) modal.hide();
                this.loadStudents();
            } else {
                alert('❌ Error: ' + data.error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
    
    // Placeholder functions
    viewStudent(id) {
        alert(`View student: ${id} - Coming soon`);
    }
    
    editStudent(id) {
        alert(`Edit student: ${id} - Coming soon`);
    }
    
    toggleStudentStatus(id, currentStatus) {
        const action = currentStatus == 1 ? 'deactivate' : 'activate';
        if (confirm(`Are you sure you want to ${action} this student?`)) {
            alert(`Toggle status for: ${id} - Coming soon`);
        }
    }

    // ===== VIEW STUDENT =====
viewStudent(studentId) {
    console.log('Viewing student:', studentId);
    
    const existingModal = document.getElementById('viewStudentModal');
    if (existingModal) existingModal.remove();
    
    fetch(`/Student_Result_Analytics/PHP_Files/admin/students/view_student_modal.php?student_id=${studentId}`)
        .then(response => response.text())
        .then(html => {
            document.body.insertAdjacentHTML('beforeend', html);
            const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
            modal.show();
            
            // Clean up on hide
            document.getElementById('viewStudentModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading student details');
        });
}

// ===== EDIT STUDENT =====
editStudent(studentId) {
    console.log('Editing student:', studentId);
    
    const existingModal = document.getElementById('editStudentModal');
    if (existingModal) existingModal.remove();
    
    fetch(`/Student_Result_Analytics/PHP_Files/admin/students/edit_student_modal.php?student_id=${studentId}`)
        .then(response => response.text())
        .then(html => {
            document.body.insertAdjacentHTML('beforeend', html);
            const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
            modal.show();
            
            // Clean up on hide
            document.getElementById('editStudentModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading edit form');
        });
}

// ===== SUBMIT EDIT STUDENT =====
submitEditStudent() {
    const form = document.getElementById('editStudentForm');
    const formData = new FormData(form);
    
    const submitBtn = document.querySelector('[onclick*="submitEditStudent"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
    submitBtn.disabled = true;
    
    fetch('/Student_Result_Analytics/PHP_Files/admin/students/update_student.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
            if (modal) modal.hide();
            this.loadStudents(); // Refresh list
        } else {
            alert('❌ Error: ' + data.error);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// ===== TOGGLE STUDENT STATUS =====
toggleStudentStatus(studentId, currentStatus) {
    const action = currentStatus == 1 ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} this student?`)) return;
    
    fetch('/Student_Result_Analytics/PHP_Files/admin/students/toggle_student_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({student_id: studentId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            this.loadStudents(); // Refresh list
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
}

// ===== TOGGLE PAYMENT STATUS =====
togglePaymentStatus(studentId) {
    fetch('/Student_Result_Analytics/PHP_Files/admin/students/update_payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({student_id: studentId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            this.loadStudents(); // Refresh list
            
            // Close view modal if open
            const viewModal = document.getElementById('viewStudentModal');
            if (viewModal) {
                bootstrap.Modal.getInstance(viewModal)?.hide();
            }
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });
}
};

console.log('✅ StudentManager class defined');

// ===== SINGLE INITIALIZATION WITH FLAG =====
if (!window.studentManagerInitialized) {
    window.studentManagerInitialized = false;
}

function initializeStudentManager() {
    // Check if already initialized
    if (window.studentManagerInitialized) {
        console.log('✅ StudentManager already initialized, skipping');
        return true;
    }
    
    if (!document.getElementById('studentsContainer')) {
        console.log('⏳ studentsContainer not found, will retry...');
        return false;
    }
    
    if (typeof StudentManager === 'undefined') {
        console.error('❌ StudentManager not defined');
        return false;
    }
    
    console.log('🚀 Creating StudentManager instance');
    window.studentManager = new StudentManager();
    window.studentManagerInitialized = true;
    return true;
}

// Try to initialize now
if (!initializeStudentManager()) {
    // Retry a few times
    let attempts = 0;
    const interval = setInterval(() => {
        attempts++;
        if (initializeStudentManager() || attempts >= 10) {
            clearInterval(interval);
        }
    }, 200);
}

// Listen for page loads via AJAX
window.addEventListener('pageLoaded', function(e) {
    if (e.detail.url.includes('manage_students.php') || e.detail.url.includes('student_management')) {
        console.log('📄 Student management page loaded via AJAX');
        
        // Don't create new instance, just reload data
        if (window.studentManager && typeof window.studentManager.loadStudents === 'function') {
            setTimeout(() => {
                console.log('🔄 Reloading student data...');
                window.studentManager.loadStudents();
            }, 100);
        }
    }
});