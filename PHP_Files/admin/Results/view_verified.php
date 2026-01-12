<?php
// Results/view_verified.php - CLEAN CONTENT ONLY
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied. Please log in again.</div>';
    exit();
}

// Get filters
$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT r.result_id, 
               r.marks_obtained, 
               r.total_marks,
               r.percentage,
               r.grade,
               r.verification_date,
               r.entered_by_teacher_id,
               r.verified_by_admin_id,
               s.subject_name,
               stu.student_name,
               stu.student_id as roll_number,
               c.faculty,
               c.semester,
               t.name as teacher_name
        FROM result r
        JOIN subject s ON r.subject_id = s.subject_id
        JOIN student stu ON r.student_id = stu.student_id
        JOIN class c ON stu.class_id = c.class_id
        LEFT JOIN teacher t ON r.entered_by_teacher_id = t.teacher_id
        WHERE r.verification_status = 'verified'";
        
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

$sql .= " ORDER BY r.verification_date DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $connection->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Get total count for stats
$total_count_sql = "SELECT COUNT(*) as total FROM result WHERE verification_status = 'verified'";
$total_result = $connection->query($total_count_sql);
$total_verified = $total_result->fetch_assoc()['total'];
?>

<!-- Content only - no <html>, <head>, <body> -->
<div class="container-fluid py-4" id="verified-results-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-check-circle text-success"></i> Verified Results</h2>
        <div>
            <button onclick="loadResultsVerification()" class="btn btn-outline-warning">
                <i class="bi bi-clock"></i> Back to Pending
            </button>
        </div>
    </div>
    
    <!-- Stats Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Verified</h6>
                            <h3 class="mb-0" id="total-verified-count"><?= $total_verified ?></h3>
                        </div>
                        <i class="bi bi-check-circle display-6 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="verified-filter-form" class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label">Faculty</label>
                            <select name="faculty" id="filter-faculty" class="form-select">
                                <option value="">All Faculties</option>
                                <option value="BCA" <?= $faculty == 'BCA' ? 'selected' : '' ?>>BCA</option>
                                <option value="BBM" <?= $faculty == 'BBM' ? 'selected' : '' ?>>BBM</option>
                                <option value="BIM" <?= $faculty == 'BIM' ? 'selected' : '' ?>>BIM</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Semester</label>
                            <select name="semester" id="filter-semester" class="form-select">
                                <option value="">All Semesters</option>
                                <?php for($i=1; $i<=8; $i++): ?>
                                <option value="<?= $i ?>" <?= $semester == $i ? 'selected' : '' ?>>
                                    Semester <?= $i ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                                <?php if($faculty || $semester): ?>
                                <button type="button" id="clear-filters" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
                            <th>#</th>
                            <th>Student</th>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Teacher</th>
                            <th>Verified On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 1; 
                        $total_marks_obtained = 0;
                        $total_marks = 0;
                        while($row = $results->fetch_assoc()): 
                            $marks_obtained = $row['marks_obtained'] ?? 0;
                            $total_marks_value = $row['total_marks'] ?? 100;
                            $percentage = $row['percentage'] ?? (($marks_obtained / $total_marks_value) * 100);
                            $grade = $row['grade'] ?? '';
                            $class_name = $row['faculty'] . ' - Semester ' . $row['semester'];
                            
                            $total_marks_obtained += $marks_obtained;
                            $total_marks += $total_marks_value;
                        ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($row['student_name']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark font-monospace">
                                    <?= htmlspecialchars($row['roll_number']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($row['subject_name']) ?></div>
                            </td>
                            <td>
                                <div class="text-primary fw-medium"><?= htmlspecialchars($class_name) ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold"><?= $marks_obtained ?></span>
                                    <span class="text-muted">/ <?= $total_marks_value ?></span>
                                    <?php if($percentage): ?>
                                    <span class="badge <?= $percentage >= 80 ? 'bg-success' : ($percentage >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= round($percentage, 1) ?>%
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if($grade): ?>
                                <span class="badge <?= 
                                    $grade === 'A' ? 'grade-A' : 
                                    ($grade === 'B' ? 'grade-B' : 
                                    ($grade === 'C' ? 'grade-C' : 'grade-D')) 
                                ?> px-2 py-1">
                                    <?= $grade ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($row['teacher_name'])): ?>
                                    <span class="text-primary"><?= htmlspecialchars($row['teacher_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($row['verification_date']): ?>
                                    <div class="text-nowrap">
                                        <?= date('d M Y', strtotime($row['verification_date'])) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('h:i A', strtotime($row['verification_date'])) ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Verified
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <?php if($results->num_rows > 1): ?>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Average:</td>
                            <td class="fw-bold">
                                <?php 
                                $avg_percentage = $total_marks > 0 ? ($total_marks_obtained / $total_marks) * 100 : 0;
                                echo round($avg_percentage, 1) . '%';
                                ?>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php
            // Count total records for pagination
            $count_sql = "SELECT COUNT(*) as total FROM result r
                         JOIN student stu ON r.student_id = stu.student_id
                         JOIN class c ON stu.class_id = c.class_id
                         WHERE r.verification_status = 'verified'";
            
            if ($faculty) $count_sql .= " AND c.faculty = '$faculty'";
            if ($semester) $count_sql .= " AND c.semester = $semester";
            
            $count_result = $connection->query($count_sql);
            $total_records = $count_result->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $limit);
            
            if ($total_pages > 1):
            ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Previous -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" 
                           onclick="loadVerifiedPage(<?= $page-1 ?>); return false;">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    
                    <!-- Page numbers -->
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="#" 
                           onclick="loadVerifiedPage(<?= $i ?>); return false;">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="#" 
                           onclick="loadVerifiedPage(<?= $page+1 ?>); return false;">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small">
                Showing <?= ($page-1)*$limit + 1 ?> to <?= min($page*$limit, $total_records) ?> of <?= $total_records ?> verified results
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-check-circle display-1 text-muted"></i>
                <h4 class="mt-3">No verified results found</h4>
                <p class="text-muted">
                    <?php if($faculty || $semester): ?>
                    Try changing your filters or 
                    <?php endif; ?>
                    Verified results will appear here.
                </p>
                <div class="mt-3">
                    <?php if($faculty || $semester): ?>
                    <button onclick="loadVerifiedPage(1, '', '')" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                    <?php endif; ?>
                    <button onclick="loadResultsVerification()" class="btn btn-primary ms-2">
                        <i class="bi bi-clock"></i> Check Pending Results
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Minimal JavaScript for this page -->
<script>
// Function to load verified results with pagination and filters
function loadVerifiedPage(page = 1) {
    const faculty = document.getElementById('filter-faculty')?.value || '';
    const semester = document.getElementById('filter-semester')?.value || '';
    
    // Build URL
    let url = 'Results/view_verified.php?page=' + page;
    if (faculty) url += '&faculty=' + encodeURIComponent(faculty);
    if (semester) url += '&semester=' + semester;
    
    // Use global loadPage function
    if (typeof loadPage === 'function') {
        loadPage(url);
    }
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verified-filter-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            loadVerifiedPage(1);
        });
    }
    
    // Clear filters button
    const clearBtn = document.getElementById('clear-filters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            document.getElementById('filter-faculty').value = '';
            document.getElementById('filter-semester').value = '';
            loadVerifiedPage(1);
        });
    }
});
</script>