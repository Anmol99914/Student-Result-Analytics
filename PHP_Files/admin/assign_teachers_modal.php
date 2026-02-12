<?php
// assign_teachers_modal.php - CLEAN VERSION (NO JAVASCRIPT)
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
$teacher_query = "SELECT name FROM teacher WHERE teacher_id = ?";
$stmt = $connection->prepare($teacher_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

// Get all classes
$classes_query = "SELECT * FROM class ORDER BY faculty, semester";
$classes_result = $connection->query($classes_query);
?>

<input type="hidden" id="modalTeacherId" value="<?= $teacher_id ?>">

<!-- Class Selection -->
<div class="mb-4">
    <label class="form-label fw-bold">📌 Select Class:</label>
    <select id="classSelect" class="form-select">
        <option value="">-- Choose Class --</option>
        <?php while($class = $classes_result->fetch_assoc()): ?>
        <option value="<?= $class['class_id'] ?>" 
                data-faculty="<?= $class['faculty'] ?>" 
                data-semester="<?= $class['semester'] ?>">
            <?= $class['faculty'] ?> - Semester <?= $class['semester'] ?> 
            (Batch: <?= $class['batch_year'] ?>)
        </option>
        <?php endwhile; ?>
    </select>
</div>

<!-- Subjects Container -->
<div id="subjectListContainer">
    <div class="text-center py-4 text-muted">
        <i class="bi bi-arrow-up-circle" style="font-size: 2rem;"></i>
        <p class="mt-2">Select a class to view subjects</p>
    </div>
</div>

<!-- Action Buttons -->
<div class="d-flex justify-content-end gap-2 mt-3">
    <button type="button" class="btn btn-success" id="saveAssignmentsBtn" disabled>
        <i class="bi bi-check-circle"></i> Save Assignments
    </button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        Cancel
    </button>
</div>