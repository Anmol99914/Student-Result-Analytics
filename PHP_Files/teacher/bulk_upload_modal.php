<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Get teacher's assigned classes with subjects
$sql = "SELECT DISTINCT 
            c.class_id, 
            c.faculty, 
            c.semester,
            c.batch_year,
            s.subject_id,
            s.subject_name,
            s.subject_code
        FROM teacher_subject_assignment tsa
        JOIN class c ON tsa.class_id = c.class_id
        JOIN subject s ON tsa.subject_id = s.subject_id
        WHERE tsa.teacher_id = ?
        ORDER BY c.faculty, c.semester, s.subject_name";
        
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$assignments = $stmt->get_result();

// Group by class
$classes = [];
while ($row = $assignments->fetch_assoc()) {
    $class_id = $row['class_id'];
    if (!isset($classes[$class_id])) {
        $classes[$class_id] = [
            'class_name' => $row['faculty'] . ' - Semester ' . $row['semester'] . ' (' . $row['batch_year'] . ')',
            'subjects' => []
        ];
    }
    $classes[$class_id]['subjects'][] = [
        'id' => $row['subject_id'],
        'name' => $row['subject_name'],
        'code' => $row['subject_code']
    ];
}
?>

<div class="modal fade" id="bulkUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload"></i> Bulk Upload Marks
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($classes)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No classes assigned to you yet.
                    </div>
                <?php else: ?>
                    <!-- Step 1: Select Class -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Step 1: Select Your Class</h6>
                        <select class="form-select" id="bulkClassSelect">
                            <option value="">-- Choose a class --</option>
                            <?php foreach ($classes as $class_id => $class): ?>
                                <option value="<?php echo $class_id; ?>">
                                    <?php echo $class['class_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Step 2: Select Subject (enabled after class selection) -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Step 2: Select Subject</h6>
                        <select class="form-select" id="bulkSubjectSelect" disabled>
                            <option value="">-- First select a class --</option>
                        </select>
                    </div>

                    <!-- Step 3: Download Template (enabled after subject selection) -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Step 3: Download Template</h6>
                        <button class="btn btn-outline-primary" id="downloadTemplateBtn" disabled>
                            <i class="bi bi-download"></i> Download CSV Template
                        </button>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle"></i>
                            Fill in marks (0-100) for each student. Leave blank to skip.
                        </small>
                    </div>

                    <!-- Step 4: Upload File -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Step 4: Upload Completed File</h6>
                        <div class="input-group">
                            <input type="file" class="form-control" id="bulkFileInput" accept=".csv" disabled>
                            <button class="btn btn-success" id="uploadBtn" disabled>
                                <i class="bi bi-cloud-upload"></i> Upload & Save
                            </button>
                        </div>
                    </div>

                    <!-- Progress & Results -->
                    <div id="uploadProgress" style="display: none;">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 style="width: 0%" id="uploadProgressBar">0%</div>
                        </div>
                        <div id="uploadResult" class="small"></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
console.log('🔍 Bulk upload modal initialized');

// Store subjects data
const classSubjects = <?php 
    $subjects_json = [];
    foreach ($classes as $class_id => $class) {
        $subjects_json[$class_id] = $class['subjects'];
    }
    echo json_encode($subjects_json);
?>;
console.log('📚 Class subjects data:', classSubjects);

// Class selection change
const classSelect = document.getElementById('bulkClassSelect');
if (classSelect) {
    console.log('✅ Class select found');
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        console.log('📌 Class selected:', classId);
        
        const subjectSelect = document.getElementById('bulkSubjectSelect');
        const subjects = classSubjects[classId] || [];
        console.log('📚 Subjects for this class:', subjects);
        
        // Reset subject select
        subjectSelect.innerHTML = '<option value="">-- Select Subject --</option>';
        subjectSelect.disabled = !classId;
        
        // Add subjects
        subjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.name + ' (' + subject.code + ')';
            subjectSelect.appendChild(option);
            console.log('➕ Added subject:', subject.name);
        });
        
        // Disable next steps
        document.getElementById('downloadTemplateBtn').disabled = true;
        document.getElementById('bulkFileInput').disabled = true;
        document.getElementById('uploadBtn').disabled = true;
    });
} else {
    console.error('❌ Class select not found!');
}

// Subject selection change
const subjectSelect = document.getElementById('bulkSubjectSelect');
if (subjectSelect) {
    subjectSelect.addEventListener('change', function() {
        const classId = document.getElementById('bulkClassSelect').value;
        const subjectId = this.value;
        console.log('📌 Subject selected:', subjectId, 'for class:', classId);
        
        const enabled = subjectId && classId;
        document.getElementById('downloadTemplateBtn').disabled = !enabled;
        document.getElementById('bulkFileInput').disabled = !enabled;
        document.getElementById('uploadBtn').disabled = !enabled;
        
        console.log('🔘 Buttons enabled:', enabled);
    });
}

// Download template
const downloadBtn = document.getElementById('downloadTemplateBtn');
if (downloadBtn) {
    downloadBtn.addEventListener('click', function() {
        const classId = document.getElementById('bulkClassSelect').value;
        const subjectId = document.getElementById('bulkSubjectSelect').value;
        
        console.log('📥 Download clicked - Class:', classId, 'Subject:', subjectId);
        
        if (!classId || !subjectId) {
            alert('Please select class and subject first');
            return;
        }
        
        window.location.href = `download_template.php?class_id=${classId}&subject_id=${subjectId}`;
    });
}

// Upload file
const uploadBtn = document.getElementById('uploadBtn');
if (uploadBtn) {
    uploadBtn.addEventListener('click', function() {
        const fileInput = document.getElementById('bulkFileInput');
        const file = fileInput.files[0];
        
        console.log('📤 Upload clicked - File:', file ? file.name : 'No file');
        
        if (!file) {
            alert('Please select a file');
            return;
        }
        
        const classId = document.getElementById('bulkClassSelect').value;
        const subjectId = document.getElementById('bulkSubjectSelect').value;
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('class_id', classId);
        formData.append('subject_id', subjectId);
        formData.append('teacher_id', <?php echo $teacher_id; ?>);
        
        // Show progress
        document.getElementById('uploadProgress').style.display = 'block';
        document.getElementById('uploadProgressBar').style.width = '0%';
        document.getElementById('uploadProgressBar').textContent = '0%';
        
        fetch('process_bulk_upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('✅ Upload response:', data);
            document.getElementById('uploadProgressBar').style.width = '100%';
            document.getElementById('uploadProgressBar').textContent = '100%';
            
            if (data.success) {
                document.getElementById('uploadResult').innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Upload complete!<br>
                        ✅ ${data.inserted} new marks inserted<br>
                        ✅ ${data.updated} marks updated<br>
                        ⚠️ ${data.skipped} skipped
                    </div>
                `;
            } else {
                document.getElementById('uploadResult').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Error: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('❌ Upload error:', error);
            document.getElementById('uploadResult').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> Error: ${error.message}
                </div>
            `;
        });
    });
}

console.log('✅ All event listeners attached');
</script>