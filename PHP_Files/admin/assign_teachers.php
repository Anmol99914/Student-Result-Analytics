<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$teacher_id = $_GET['teacher_id'] ?? 0;
if (!$teacher_id) {
    echo '<div class="alert alert-warning">No teacher selected</div>';
    exit();
}

// Get teacher info
$teacher_query = "SELECT * FROM teacher WHERE teacher_id = ?";
$stmt = $connection->prepare($teacher_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

// Get all classes for dropdown
$classes_query = "SELECT * FROM class ORDER BY faculty, semester";
$classes_result = $connection->query($classes_query);
?>
<div class="assign-teacher-container">
    <!-- Hidden data for JavaScript -->
    <input type="hidden" id="teacherId" value="<?= $teacher_id ?>">
    <input type="hidden" id="teacherName" value="<?= htmlspecialchars($teacher['name']) ?>">
    
    <!-- Header -->
    <div class="assign-header">
        <h3><i class="bi bi-person-workspace"></i> Assign Subjects</h3>
        <p>Teacher: <strong><?= htmlspecialchars($teacher['name']) ?></strong> (ID: #<?= $teacher_id ?>)</p>
    </div>
    
    <!-- Class Selection -->
    <div class="class-selector">
        <label for="classSelect">Select Class:</label>
        <select id="classSelect" class="form-control">
            <option value="">-- Choose Class --</option>
            <?php while($class = $classes_result->fetch_assoc()): ?>
            <option value="<?= $class['class_id'] ?>" 
                    data-faculty="<?= $class['faculty'] ?>" 
                    data-semester="<?= $class['semester'] ?>">
                <?= $class['faculty'] ?> - Semester <?= $class['semester'] ?> (Batch: <?= $class['batch_year'] ?>)
            </option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <!-- Subject List Container (filled by AJAX) -->
    <div id="subjectListContainer"></div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
        <button id="saveAssignmentsBtn" class="btn btn-primary" disabled>
            <i class="bi bi-check-circle"></i> Save Assignments
        </button>
        <button type="button" id="cancelBtn" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> Cancel
        </button>
    </div>
</div>

<!-- Load CSS and JS -->
<link rel="stylesheet" href="/Student_Result_Analytics/css/admin/assign-teachers.css">
<script src="/Student_Result_Analytics/js/admin/assign-teachers.js" defer></script>