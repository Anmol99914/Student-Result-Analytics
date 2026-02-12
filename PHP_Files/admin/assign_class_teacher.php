<?php
// assign_class_teacher.php - FOR ASSIGNING TEACHERS TO CLASSES
session_start();
require_once '../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$class_id = $_GET['class_id'] ?? 0;
if (!$class_id) {
    echo '<div class="alert alert-warning">No class selected</div>';
    exit();
}

// Get class info
$class_query = "SELECT * FROM class WHERE class_id = ?";
$stmt = $connection->prepare($class_query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

// Get all active teachers
$teachers_query = "SELECT teacher_id, name, email FROM teacher WHERE status = 'active' ORDER BY name";
$teachers_result = $connection->query($teachers_query);

// Get currently assigned teachers
$assigned_query = "SELECT t.teacher_id, t.name, t.email, tca.assignment_type 
                   FROM teacher_class_assignments tca
                   JOIN teacher t ON tca.teacher_id = t.teacher_id
                   WHERE tca.class_id = ? AND tca.status = 'active'";
$stmt = $connection->prepare($assigned_query);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$assigned_result = $stmt->get_result();
$assigned_teachers = [];
while($row = $assigned_result->fetch_assoc()) {
    $assigned_teachers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Class Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="bi bi-person-plus"></i> Assign Teacher to Class</h4>
                <p class="mb-0"><?= $class['faculty'] ?> - Semester <?= $class['semester'] ?> (Batch: <?= $class['batch_year'] ?? date('Y') ?>)</p>
            </div>
            <div class="card-body">
                
                <!-- Currently Assigned Teachers -->
                <?php if(count($assigned_teachers) > 0): ?>
                <div class="mb-4">
                    <h5>Currently Assigned Teachers</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($assigned_teachers as $teacher): ?>
                            <tr>
                                <td><?= htmlspecialchars($teacher['name']) ?></td>
                                <td><?= htmlspecialchars($teacher['email']) ?></td>
                                <td><span class="badge bg-info"><?= $teacher['assignment_type'] ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="removeTeacher(<?= $class_id ?>, <?= $teacher['teacher_id'] ?>)">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <hr>
                <?php endif; ?>
                
                <!-- Assign New Teacher Form -->
                <h5 class="mb-3">Assign New Teacher</h5>
                <form id="assignTeacherForm">
                    <input type="hidden" name="class_id" value="<?= $class_id ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Select Teacher</label>
                                <select name="teacher_id" class="form-select" required>
                                    <option value="">-- Choose Teacher --</option>
                                    <?php while($teacher = $teachers_result->fetch_assoc()): 
                                        // Skip already assigned teachers
                                        $assigned = false;
                                        foreach($assigned_teachers as $at) {
                                            if($at['teacher_id'] == $teacher['teacher_id']) {
                                                $assigned = true;
                                                break;
                                            }
                                        }
                                        if($assigned) continue;
                                    ?>
                                    <option value="<?= $teacher['teacher_id'] ?>">
                                        <?= htmlspecialchars($teacher['name']) ?> (<?= $teacher['email'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Assignment Type</label>
                                <select name="assignment_type" class="form-select">
                                    <option value="primary">Primary Teacher</option>
                                    <option value="assistant">Assistant Teacher</option>
                                    <option value="substitute">Substitute Teacher</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i> Assign
                            </button>
                        </div>
                    </div>
                </form>
                
                <div id="message" class="mt-3"></div>
                
            </div>
            <div class="card-footer">
                <button type="button" class="btn btn-secondary" onclick="window.close()">
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <script>
    // Handle form submission
    document.getElementById('assignTeacherForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Assigning...';
        submitBtn.disabled = true;
        
        fetch('api/assign_teacher_to_class.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showMessage('✅ Teacher assigned successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('❌ Error: ' + data.error, 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            showMessage('❌ Error: ' + error.message, 'danger');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Remove teacher function
    window.removeTeacher = function(classId, teacherId) {
        if(!confirm('Remove this teacher from the class?')) return;
        
        fetch('api/remove_teacher_from_class.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({class_id: classId, teacher_id: teacherId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showMessage('✅ Teacher removed successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('❌ Error: ' + data.error, 'danger');
            }
        });
    };
    
    // Show message function
    function showMessage(msg, type) {
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
        setTimeout(() => messageDiv.innerHTML = '', 3000);
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>