<?php
// Results/view_rejected.php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

// Get filters
$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$sql = "SELECT r.*, 
               s.subject_name,
               stu.student_name,
               stu.student_id,
               c.faculty,
               c.semester,
               t.name as teacher_name
        FROM result r
        JOIN subject s ON r.subject_id = s.subject_id
        JOIN student stu ON r.student_id = stu.student_id
        JOIN class c ON stu.class_id = c.class_id
        LEFT JOIN teacher t ON r.entered_by_teacher_id = t.teacher_id
        WHERE r.verification_status = 'rejected'";
        
$params = [];
$types = '';

if ($faculty) {
    $sql .= " AND c.faculty = ?";
    $params[] = $faculty;
    $types .= 's';
}

if ($semester) {
    $sql .= " AND c.semester = ?";
    $params[] = $semester;
    $types .= 'i';
}

$sql .= " ORDER BY r.updated_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $connection->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-x-circle text-danger"></i> Rejected Results</h2>
        <div>
            <button onclick="loadResultsVerification()" class="btn btn-outline-warning">
                <i class="bi bi-clock"></i> Back to Pending
            </button>
            <button onclick="loadVerifiedResults()" class="btn btn-outline-success">
                <i class="bi bi-check-circle"></i> Verified Results
            </button>
        </div>
    </div>
    
    <!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form id="rejected-filter-form" class="row g-3">
            <div class="col-md-4">
                <select name="faculty" id="filter-faculty" class="form-select">
                    <option value="">All Faculties</option>
                    <?php
                    // Fetch all active faculties from database
                    $faculty_query = "SELECT faculty_code, faculty_name FROM faculty WHERE status = 'active' ORDER BY faculty_code";
                    $faculty_result = $connection->query($faculty_query);
                    
                    if ($faculty_result && $faculty_result->num_rows > 0) {
                        while ($faculty_row = $faculty_result->fetch_assoc()) {
                            $faculty_code = htmlspecialchars($faculty_row['faculty_code']);
                            $faculty_name = htmlspecialchars($faculty_row['faculty_name']);
                            $selected = ($faculty == $faculty_code) ? 'selected' : '';
                            echo "<option value=\"$faculty_code\" $selected>$faculty_name ($faculty_code)</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="semester" id="filter-semester" class="form-select">
                    <option value="">All Semesters</option>
                    <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?= $i ?>" <?= $semester == $i ? 'selected' : '' ?>>
                        Semester <?= $i ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>
    
    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <?php if($results->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Marks</th>
                            <th>Teacher</th>
                            <th>Rejected On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['student_id']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= $row['faculty'] ?> - Sem <?= $row['semester'] ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $row['marks_obtained'] ?>/<?= $row['total_marks'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['teacher_name'] ?? 'N/A') ?></td>
                            <td>
                                <?= date('d M Y', strtotime($row['updated_at'])) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-x-circle display-1 text-muted"></i>
                <h4 class="mt-3">No rejected results found</h4>
                <p class="text-muted">Results that are rejected will appear here.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('rejected-filter-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const faculty = document.getElementById('filter-faculty').value;
    const semester = document.getElementById('filter-semester').value;
    let url = 'Results/view_rejected.php';
    const params = [];
    if (faculty) params.push('faculty=' + faculty);
    if (semester) params.push('semester=' + semester);
    if (params.length) url += '?' + params.join('&');
    
    if (typeof loadPage === 'function') {
        loadPage(url);
    }
});
</script>