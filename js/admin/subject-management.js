// File: js/admin/subject-management.js

// Add at the VERY TOP of your file
console.log('=== SCRIPT LOAD ORDER ===');
console.log('Current script loaded');

$(document).ready(function() {
    console.log('Subject Manager - Fixed Paths Loaded');
    
    // Configuration
    let currentPage = 1;
    let itemsPerPage = 10;
    let currentFaculty = '';
    let currentSemester = '';
    let currentStatus = '1';
    let searchQuery = '';
    
//    Absolute paths use gareko:)
    const API =  {
        getSubjects: '/Student_Result_Analytics/PHP_Files/admin/api/get_subjects.php',
        addSubject: '/Student_Result_Analytics/PHP_Files/admin/api/add_subject.php',
        editSubject: '/Student_Result_Analytics/PHP_Files/admin/api/edit_subject.php',
        deactivateSubject: '/Student_Result_Analytics/PHP_Files/admin/api/deactivate_subject.php',  
        activateSubject: '/Student_Result_Analytics/PHP_Files/admin/api/activate_subject.php',       
        hardDeleteSubject: '/Student_Result_Analytics/PHP_Files/admin/api/hard_delete_subject.php',
        getStats: '/Student_Result_Analytics/PHP_Files/admin/api/get_subject_stats.php'
    };

    // const API =  {
    //     getSubjects: '../admin/api/get_subjects.php',
    //     addSubject: '../admin/api/add_subject.php',
    //     editSubject: '../admin/api/edit_subject.php',
    //     // deleteSubject: '../admin/api/delete_subject.php',
    //     deactivateSubject: '../admin/api/deactivate_subject.php',  
    //     activateSubject: '../admin/api/activate_subject.php',       
    //     hardDeleteSubject: '../admin/api/hard_delete_subject.php',
    //     getStats: '../admin/api/get_subject_stats.php'
    // };
    console.log('API paths (relative to admin/pages/):', API);
    
    // ===== INITIALIZE =====
function init() {
    console.log('Initializing from:', window.location.pathname);
    
    // Fetch faculties and build mapping
    $.ajax({
        url: '/Student_Result_Analytics/PHP_Files/admin/get_faculties.php',
        method: 'GET',
        dataType: 'json',
        success: function(faculties) {
            window.facultyMap = {};
            faculties.forEach(f => {
                window.facultyMap[f.id] = f.faculty_code;
            });
            console.log('Faculty map loaded:', window.facultyMap);
        },
        async: false // Make it synchronous for simplicity
    });
    
    bindEvents();
    loadSubjects();
    loadStats();
}
    // ===== BIND EVENTS =====
    function bindEvents() {
        // Add subject button
        $('#addSubjectBtn, #addFirstSubjectBtn').on('click', showAddModal);
        
        // Filter changes
        $('#facultyFilter').on('change', function() {
            currentFaculty = $(this).val();
            console.log('Faculty filter:', currentFaculty);
            currentPage = 1;
            loadSubjects();
        });
        
        $('#semesterFilter').on('change', function() {
            currentSemester = $(this).val();
            console.log('Semester filter:', currentSemester);
            currentPage = 1;
            loadSubjects();
        });
        
        $('#statusFilter').on('change', function() {
            currentStatus = $(this).val();
            console.log('Status filter:', currentStatus);
            currentPage = 1;
            loadSubjects();
        });
        
        $('#searchInput').on('keyup', function() {
            searchQuery = $(this).val().trim();
            currentPage = 1;
            loadSubjects();
        });
        
        $('#clearFiltersBtn').on('click', function() {
            $('#facultyFilter, #semesterFilter, #statusFilter').val('');
            $('#searchInput').val('');
            currentFaculty = '';
            currentSemester = '';
            currentStatus = '';
            searchQuery = '';
            currentPage = 1;
            loadSubjects();
        });
        
        // Faculty tabs
        $('#facultyTabs .nav-link, #facultyTabs button').on('click', function() {
            $('#facultyTabs .nav-link, #facultyTabs button').removeClass('active');
            $(this).addClass('active');
            currentFaculty = $(this).data('faculty') || '';
            $('#facultyFilter').val(currentFaculty);
            currentPage = 1;
            loadSubjects();
        });
        
        // Save subject
        $('#saveSubjectBtn').on('click', saveSubject);

        // Modal close with cleanup
        $('#subjectModal').on('hidden.bs.modal', function() {
            console.log('🔄 MODAL CLOSED - Cleaning up');
            
            // Don't reset the form completely, just clear the ID
            $('#subjectId').val('');
            
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        });
    }
    
    // ===== LOAD SUBJECTS =====
    function loadSubjects() {
        console.log('Loading subjects with params:', {
            page: currentPage,
            faculty_id: currentFaculty,
            semester: currentSemester,
            status: currentStatus,
            search: searchQuery
        });
        
        showLoading();
        
        $.ajax({
            url: API.getSubjects,
            method: 'GET',
            data: {
                page: currentPage,
                limit: itemsPerPage,
                faculty_id: currentFaculty,
                semester: currentSemester,
                status: currentStatus,
                search: searchQuery
            },
            dataType: 'json',
            success: function(response) {
                console.log('API Response:', response);
                if (response.success) {
                    renderSubjects(response.data);
                    renderPagination(response.total, response.pages);
                    updateEmptyState(response.data.length === 0);
                } else {
                    alert('Error loading subjects: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText.substring(0, 200)
                });
                alert('Network error loading subjects. Check console.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    // ===== RENDER SUBJECTS =====
    function renderSubjects(subjects) {
        let html = '';
        
        if (subjects.length === 0) {
            html = '<tr><td colspan="8" class="text-center py-4 text-muted">No subjects found</td></tr>';
        } else {
            subjects.forEach((subject, index) => {
                const rowNumber = (currentPage - 1) * itemsPerPage + index + 1;
                const facultyName = getFacultyName(subject.faculty_id);
                const statusBadge = subject.is_active == 1 ? 
                    '<span class="badge bg-success">Active</span>' : 
                    '<span class="badge bg-danger">Inactive</span>';
                
                // 3-ACTION BUTTONS
                let actionButtons = '';
                
                if (subject.is_active == 1) {
                    // ACTIVE: Deactivate + Edit
                    actionButtons = `
                        <button class="btn btn-sm btn-warning deactivate-btn me-1">
                            <i class="fas fa-ban me-1"></i> Deactivate
                        </button>
                        <button class="btn btn-sm btn-primary edit-btn">
                            <i class="fas fa-edit me-1"></i> Edit
                        </button>
                    `;
                } else {
                    // INACTIVE: Activate + Delete
                    actionButtons = `
                        <button class="btn btn-sm btn-success activate-btn me-1">
                            <i class="fas fa-check me-1"></i> Activate
                        </button>
                        <button class="btn btn-sm btn-danger delete-btn">
                            <i class="fas fa-trash me-1"></i> Delete
                        </button>
                    `;
                }
                
                html += `
                    <tr data-id="${subject.subject_id}">
                        <td>${rowNumber}</td>
                        <td><strong class="text-primary">${escapeHtml(subject.subject_code)}</strong></td>
                        <td>${escapeHtml(subject.subject_name)}</td>
                        <td>${facultyName}</td>
                        <td>Semester ${subject.semester}</td>
                        <td><span class="badge bg-info">${subject.credits} Credits</span></td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                ${actionButtons}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        
        $('#subjectsTable tbody').html(html);
        bindRowActions();
    }
    
    // ===== BIND ROW ACTIONS =====
    function bindRowActions() {
        // Edit button
        $('.edit-btn').on('click', function() {
            const subjectId = $(this).closest('tr').data('id');
            editSubject(subjectId);
        });
        
        // Deactivate button
        $('.deactivate-btn').on('click', function() {
            const subjectId = $(this).closest('tr').data('id');
            const subjectName = $(this).closest('tr').find('td:nth-child(3)').text();
            const subjectCode = $(this).closest('tr').find('td:nth-child(2) strong').text();
            
            if (confirm(`Deactivate "${subjectCode} - ${subjectName}"?`)) {
                deactivateSubject(subjectId);
            }
        });
        
        // Activate button
        $('.activate-btn').on('click', function() {
            const subjectId = $(this).closest('tr').data('id');
            const subjectName = $(this).closest('tr').find('td:nth-child(3)').text();
            const subjectCode = $(this).closest('tr').find('td:nth-child(2) strong').text();
            
            if (confirm(`Activate "${subjectCode} - ${subjectName}"?`)) {
                activateSubject(subjectId);
            }
        });
        
        // Delete button
        $('.delete-btn').on('click', function() {
            const subjectId = $(this).closest('tr').data('id');
            const subjectName = $(this).closest('tr').find('td:nth-child(3)').text();
            const subjectCode = $(this).closest('tr').find('td:nth-child(2) strong').text();
            
            if (confirm(`PERMANENTLY delete "${subjectCode} - ${subjectName}"?\n\nThis cannot be undone!`)) {
                const confirmText = prompt('Type "DELETE" to confirm:');
                if (confirmText === "DELETE") {
                    deleteSubject(subjectId);
                }
            }
        });
    }
    
    // ===== 3 ACTION FUNCTIONS =====
    function deactivateSubject(subjectId) {
        console.log('Deactivating subject:', subjectId);
        $.ajax({
            url: API.deactivateSubject,
            method: 'POST',
            data: { subject_id: subjectId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Subject deactivated!');
                    loadSubjects();
                    loadStats();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Network error deactivating subject');
            }
        });
    }
    
    function activateSubject(subjectId) {
        console.log('Activating subject:', subjectId);
        $.ajax({
            url: API.activateSubject,
            method: 'POST',
            data: { subject_id: subjectId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Subject activated!');
                    loadSubjects();
                    loadStats();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Network error activating subject');
            }
        });
    }
    
    function deleteSubject(subjectId) {
        console.log('Deleting subject:', subjectId);
        $.ajax({
            url: API.hardDeleteSubject,
            method: 'POST',
            data: { subject_id: subjectId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Subject permanently deleted!');
                    loadSubjects();
                    loadStats();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Network error deleting subject');
            }
        });
    }
    
    // ===== EDIT/SAVE FUNCTIONS =====
    function showAddModal() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        
        $('#modalTitle').html('<i class="fas fa-book me-1"></i> Add New Subject');
        $('#subjectForm')[0].reset();
        $('#subjectId').val('');
        $('#isActive').val('1');
        
        // Show modal with proper cleanup
        const modal = new bootstrap.Modal(document.getElementById('subjectModal'));
        modal.show();
    }
    
    function editSubject(subjectId) {
        console.log('Editing subject:', subjectId);
        
        // CLEANUP BEFORE OPENING
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        
        $.ajax({
            url: API.getSubjects,
            method: 'GET',
            data: { 
                id: subjectId,
                status: ''  // ← EMPTY STRING = NO STATUS FILTER
            },
            dataType: 'json',
            success: function(response) {
                console.log('Edit response:', response);
                if (response.success && response.data.length > 0) {
                    const subject = response.data[0];
                    console.log('Subject data for edit:', subject);
                    populateEditForm(subject);
                } else {
                    alert('Subject not found. It might be inactive.');
                }
            },
            error: function(xhr) {
                console.error('Edit error:', xhr.responseText);
                alert('Failed to load subject data');
            }
        });
    }
    
    function populateEditForm(subject) {
        console.log('populateEditForm for:', subject.subject_code);
        console.log('is_elective:', subject.is_elective, 'type:', typeof subject.is_elective);
        
        // DON'T reset the form! Just populate
        
        // Set modal title and hidden ID
        $('#modalTitle').html('<i class="fas fa-edit me-1"></i> Edit Subject');
        $('#subjectId').val(subject.subject_id);
        
        // Set visible fields
        $('#subjectName').val(subject.subject_name);
        $('#subjectCode').val(subject.subject_code);
        $('#facultyId').val(subject.faculty_id);
        $('#semester').val(subject.semester);
        $('#credits').val(subject.credits);
        $('#isActive').val(subject.is_active);
        $('#description').val(subject.description || '');
        
        // ✅ CRITICAL: Set dropdown with PROPER debugging
        const electiveValue = String(subject.is_elective);
        console.log('🎯 Setting #isElective to:', electiveValue);
        
        // Method 1: Direct DOM (most reliable)
        const domElement = document.getElementById('isElective');
        if (domElement) {
            console.log('🎯 Before DOM set:', domElement.value);
            domElement.value = electiveValue;
            console.log('🎯 After DOM set:', domElement.value);
        }
        
        // Method 2: Force with jQuery
        $('#isElective').val(electiveValue);
        console.log('🎯 After jQuery set:', $('#isElective').val());
        
        // Verify
        console.log('🎯 FINAL CHECK:');
        console.log('- DOM value:', domElement?.value);
        console.log('- jQuery value:', $('#isElective').val());
        console.log('- Selected text:', $('#isElective option:selected').text());
        
        // Show modal
        new bootstrap.Modal('#subjectModal').show();
        
        // Check 100ms after modal shows
        setTimeout(() => {
            console.log('⏱️ After modal shown:');
            console.log('Dropdown value:', $('#isElective').val());
            console.log('Selected text:', $('#isElective option:selected').text());
        }, 100);
    }
    
    function saveSubject() {
        console.log('Saving subject...');
        
        // Get ALL values separately for debugging
        const subjectId = $('#subjectId').val();
        const subjectName = $('#subjectName').val().trim();
        const subjectCode = $('#subjectCode').val().trim().toUpperCase();
        const facultyId = $('#facultyId').val();
        const semester = $('#semester').val();
        const credits = $('#credits').val();
        const isElective = $('#isElective').val();
        const isActive = $('#isActive').val();
        const description = $('#description').val().trim();
        
        console.log('DEBUG FORM VALUES:');
        console.log('subjectId:', subjectId);
        console.log('isElective VALUE:', isElective);
        console.log('isElective ELEMENT:', $('#isElective'));
        console.log('Selected option text:', $('#isElective option:selected').text());
        console.log('All options:');
        $('#isElective option').each(function(i, opt) {
            console.log(`  Option ${i}: value="${opt.value}", text="${opt.text}"`);
        });
        
        const formData = {
            subject_id: subjectId,
            subject_name: subjectName,
            subject_code: subjectCode,
            faculty_id: facultyId,
            semester: semester,
            credits: credits,
            is_elective: isElective,
            is_active: isActive,
            description: description
        };
        
        console.log('COMPLETE Form data:', formData);
        console.log('JSON being sent:', JSON.stringify(formData, null, 2));
        
        // Validation
        if (!subjectName) {
            alert('Subject name is required');
            return;
        }
        
        const isEdit = subjectId !== '';
        const apiUrl = isEdit ? API.editSubject : API.addSubject;
        
        const saveBtn = $('#saveSubjectBtn');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        
        $.ajax({
            url: apiUrl,
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    alert('Nice!' + (response.message || 'Subject saved successfully!'));
                    
                    // PROPERLY CLOSE MODAL
                    $('#subjectModal').modal('hide');
                    
                    // LOAD DATA AFTER MODAL IS FULLY CLOSED
                    setTimeout(() => {
                        loadSubjects();
                        loadStats();
                    }, 300);
                    
                } else {
                    alert('✗ ' + (response.message || 'Failed to save subject'));
                }
            },
            error: function(xhr) {
                console.error('Save error:', xhr.responseText);
                // Don't show alert for expected errors (like duplicate code)
                if (!xhr.responseText.includes('already exists')) {
                    alert('Network error. Check console for details.');
                }
            },
            complete: function() {
                saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Subject');
            }
        });
    }
    
    // ===== UTILITY FUNCTIONS =====
    function loadStats() {
        $.ajax({
            url: API.getStats,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateStats(response.data);
                }
            }
        });
    }
    
    function updateStats(stats) {
        if (stats.bca !== undefined) $('#bcaSubjectsStat').text(stats.bca);
        if (stats.bbm !== undefined) $('#bbmSubjectsStat').text(stats.bbm);
        if (stats.bim !== undefined) $('#bimSubjectsStat').text(stats.bim);
        if (stats.total !== undefined) $('#totalSubjectsStat').text(stats.total);
    }
    
    function renderPagination(total, totalPages) {
        const start = ((currentPage - 1) * itemsPerPage) + 1;
        const end = Math.min(currentPage * itemsPerPage, total);
        $('#paginationInfo').text(`Showing ${start} to ${end} of ${total} subjects`);
        
        $('#paginationControls').empty();
        
        if (totalPages <= 1) return;
        
        // Previous
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        $('#paginationControls').append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `);
        
        // Pages
        for (let i = 1; i <= totalPages; i++) {
            const active = i === currentPage ? 'active' : '';
            $('#paginationControls').append(`
                <li class="page-item ${active}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        // Next
        const nextDisabled = currentPage === totalPages ? 'disabled' : '';
        $('#paginationControls').append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `);
        
        $('.page-link').on('click', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage) {
                currentPage = page;
                loadSubjects();
            }
        });
    }
    
    function updateEmptyState(isEmpty) {
        if (isEmpty) {
            $('#subjectsTable').hide();
            $('#paginationSection').hide();
            $('#noSubjectsPlaceholder').show();
        } else {
            $('#subjectsTable').show();
            $('#paginationSection').show();
            $('#noSubjectsPlaceholder').hide();
        }
    }
    
    function showLoading() {
        $('#loadingRow').show();
    }
    
    function hideLoading() {
        $('#loadingRow').hide();
    }
    
    function getFacultyName(facultyId) {
        return window.facultyMap && window.facultyMap[facultyId] 
            ? window.facultyMap[facultyId] 
            : 'Unknown';
    }
    
    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return {
                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                '"': '&quot;', "'": '&#39;'
            }[m];
        });
    }
    
    // ===== START EVERYTHING =====
    init();
});