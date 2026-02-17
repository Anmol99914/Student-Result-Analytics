<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header('Location: admin_login.php');
    exit();
}

// Get filter parameters
$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';
$class_id = $_GET['class_id'] ?? '';

// ONLY show published results
$sql = "SELECT 
            s.student_id,
            s.student_name,
            c.faculty,
            r.semester_id as semester,
            sub.subject_name,
            r.marks_obtained,
            r.total_marks,
            r.percentage,
            r.grade,
            r.published_date
        FROM result r
        JOIN student s ON r.student_id = s.student_id
        JOIN class c ON s.class_id = c.class_id
        JOIN subject sub ON r.subject_id = sub.subject_id
        WHERE r.status = 'published'";  // ← ONLY PUBLISHED!

if ($faculty) {
    $sql .= " AND c.faculty = '" . $connection->real_escape_string($faculty) . "'";
}
if ($semester) {
    $sql .= " AND r.semester_id = '" . $connection->real_escape_string($semester) . "'"; // For filtering yo chai
}

$sql .= " ORDER BY c.faculty, c.semester, s.student_name, sub.subject_name";

$result = $connection->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Export Results - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-download me-2"></i>Export Results</h2>
            <a href="admin_main_page.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
        
        <!-- Filter Card -  -->
        <!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Results</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Faculty</label>
                <select name="faculty" class="form-select">
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
            
            <div class="col-md-5">
                <label class="form-label">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">All Semesters</option>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                            Semester <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-2"></i>Filter
                </button>
            </div>
        </form>
        <div class="mt-2 text-muted small">
            <i class="bi bi-info-circle"></i> Select Faculty and Semester to filter results
        </div>
    </div>
</div>
        
        <!-- Export Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Download Options</h5>
                        <div class="btn-group">
                            <button class="btn btn-success" onclick="exportToExcel()">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export to Excel (CSV)
                            </button>
                            <button class="btn btn-danger" onclick="exportToPDF()">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Export to PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results Table -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Results Preview (<?php echo $result->num_rows; ?> records)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="resultsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Faculty</th>
                                <th>Semester</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Total</th>
                                <th>%</th>
                                <th>Grade</th>
                                <th>Status</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><code><?php echo $row['student_id']; ?></code></td>
            <td><?php echo $row['student_name']; ?></td>
            <td><?php echo $row['faculty']; ?></td>
            <td class="text-center"><?php echo $row['semester']; ?></td>
            <td><?php echo $row['subject_name']; ?></td>
            <td class="text-center"><?php echo $row['marks_obtained']; ?></td>
            <td class="text-center"><?php echo $row['total_marks']; ?></td>
            <td class="text-center"><?php echo $row['percentage']; ?>%</td>
            <td class="text-center"><span class="badge bg-primary"><?php echo $row['grade']; ?></span></td>
            <td class="text-center">
                <span class="badge bg-success">Published</span>  <!-- Fixed: Hardcoded since we only show published -->
            </td>
            <td><?php echo date('d M Y', strtotime($row['published_date'])); ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="11" class="text-center py-4">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="mt-2">No published results found</p>
            </td>
        </tr>
    <?php endif; ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function exportToExcel() {
    const faculty = document.querySelector('select[name="faculty"]').value;
    const semester = document.querySelector('select[name="semester"]').value;
    window.location.href = `api/export_results_excel.php?faculty=${faculty}&semester=${semester}`;
    }
    
    function exportToPDF() {
        const faculty = document.querySelector('select[name="faculty"]').value;
        const semester = document.querySelector('select[name="semester"]').value;
        window.location.href = `api/export_results_pdf.php?faculty=${faculty}&semester=${semester}`;
    }
    </script>
</body>
</html>