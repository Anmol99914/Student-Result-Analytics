<?php
session_start();
require_once '../../../config.php';

// Check if teacher is logged in
if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'] ?? 'Teacher';

// Debug: Log teacher info
error_log("My Students - Teacher ID: $teacher_id, Name: $teacher_name");

// Try to get classes from multiple sources (most specific to general)
$teacher_classes = [];

// Method 1: From teacher_class_assignments table
$class_sql = "SELECT DISTINCT c.class_id, c.faculty, c.semester 
              FROM class c 
              INNER JOIN teacher_class_assignments tca ON c.class_id = tca.class_id
              WHERE tca.teacher_id = ? AND c.status = 'active'";
$class_stmt = $connection->prepare($class_sql);
if ($class_stmt) {
    $class_stmt->bind_param("i", $teacher_id);
    $class_stmt->execute();
    $class_result = $class_stmt->get_result();
    $teacher_classes = $class_result->fetch_all(MYSQLI_ASSOC);
    $class_stmt->close();
    error_log("Found " . count($teacher_classes) . " classes from teacher_class_assignments");
}

// Method 2: If still empty, try teacher_subject_assignment
if (empty($teacher_classes)) {
    $class_sql = "SELECT DISTINCT c.class_id, c.faculty, c.semester 
                  FROM class c 
                  INNER JOIN teacher_subject_assignment tsa ON c.class_id = tsa.class_id
                  WHERE tsa.teacher_id = ? AND tsa.status = 'active' AND c.status = 'active'";
    $class_stmt = $connection->prepare($class_sql);
    if ($class_stmt) {
        $class_stmt->bind_param("i", $teacher_id);
        $class_stmt->execute();
        $class_result = $class_stmt->get_result();
        $teacher_classes = $class_result->fetch_all(MYSQLI_ASSOC);
        $class_stmt->close();
        error_log("Found " . count($teacher_classes) . " classes from teacher_subject_assignment");
    }
}

// Method 3: If still empty, show all classes from subjects teacher has taught
if (empty($teacher_classes)) {
    // Get subjects teacher has taught
    $subject_sql = "SELECT DISTINCT subject_id FROM teacher_subject_assignment WHERE teacher_id = ?";
    $subject_stmt = $connection->prepare($subject_sql);
    if ($subject_stmt) {
        $subject_stmt->bind_param("i", $teacher_id);
        $subject_stmt->execute();
        $subject_result = $subject_stmt->get_result();
        $subject_ids = array_column($subject_result->fetch_all(MYSQLI_ASSOC), 'subject_id');
        $subject_stmt->close();
        
        if (!empty($subject_ids)) {
            // Get classes for these subjects
            $placeholders = str_repeat('?,', count($subject_ids) - 1) . '?';
            $class_sql = "SELECT DISTINCT c.class_id, c.faculty, c.semester 
                          FROM class c 
                          INNER JOIN subject s ON s.faculty_id = 
                              (SELECT faculty_id FROM faculty WHERE faculty_code = c.faculty)
                          WHERE s.subject_id IN ($placeholders) AND c.status = 'active'";
            
            $class_stmt = $connection->prepare($class_sql);
            if ($class_stmt) {
                $types = str_repeat('i', count($subject_ids));
                $class_stmt->bind_param($types, ...$subject_ids);
                $class_stmt->execute();
                $class_result = $class_stmt->get_result();
                $teacher_classes = $class_result->fetch_all(MYSQLI_ASSOC);
                $class_stmt->close();
                error_log("Found " . count($teacher_classes) . " classes from teacher's subjects");
            }
        }
    }
}

// Get class IDs for query
$class_ids = array_column($teacher_classes, 'class_id');

// Get students from these classes
$students = [];
if (!empty($class_ids)) {
    // Create placeholders for IN clause
    $placeholders = str_repeat('?,', count($class_ids) - 1) . '?';
    
    $student_sql = "SELECT s.*, 
                           c.faculty, 
                           c.semester,
                           se.semester_name
                    FROM student s
                    INNER JOIN class c ON s.class_id = c.class_id
                    INNER JOIN semester se ON s.semester_id = se.semester_id
                    WHERE s.class_id IN ($placeholders) AND s.is_active = 1
                    ORDER BY s.class_id, s.student_name";
    
    $student_stmt = $connection->prepare($student_sql);
    if ($student_stmt) {
        // Bind parameters dynamically
        $types = str_repeat('i', count($class_ids));
        $student_stmt->bind_param($types, ...$class_ids);
        $student_stmt->execute();
        $student_result = $student_stmt->get_result();
        $students = $student_result->fetch_all(MYSQLI_ASSOC);
        $student_stmt->close();
        error_log("Found " . count($students) . " students in assigned classes");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Teacher Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS from root folder -->
    <link rel="stylesheet" href="../../../../css/mystudents-style.css">
    
    <style>
        /* Inline styles for this page only */
        .status-active { color: #198754; }
        .status-inactive { color: #6c757d; }
        .table-hover tbody tr:hover { background-color: rgba(0, 123, 255, 0.05); }
        .badge-faculty { background-color: #6f42c1; }
        .badge-semester { background-color: #fd7e14; }
        .card-header-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .student-id { font-family: 'Courier New', monospace; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-primary">
                    <i class="bi bi-people-fill me-2"></i>My Students
                </h1>
                <p class="text-muted mb-0">
                    Teacher: <strong><?php echo htmlspecialchars($teacher_name); ?></strong> 
                    | Total Students: <span class="badge bg-primary"><?php echo count($students); ?></span>
                </p>
            </div>
            <div>
                <button onclick="window.parent.showHome(); return false;" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <?php if (!empty($teacher_classes)): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <i class="bi bi-mortarboard-fill fs-1"></i>
                        </div>
                        <h3 class="card-title"><?php echo count($teacher_classes); ?></h3>
                        <p class="card-text text-muted">Classes Assigned</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="bi bi-people-fill fs-1"></i>
                        </div>
                        <h3 class="card-title"><?php echo count($students); ?></h3>
                        <p class="card-text text-muted">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <i class="bi bi-check-circle-fill fs-1"></i>
                        </div>
                        <h3 class="card-title">
                            <?php echo count(array_filter($students, function($s) { return $s['is_active'] == 1; })); ?>
                        </h3>
                        <p class="card-text text-muted">Active Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="bi bi-calendar-check-fill fs-1"></i>
                        </div>
                        <h3 class="card-title">
                            <?php 
                            $recent = array_filter($students, function($s) {
                                return strtotime($s['created_at']) > strtotime('-30 days');
                            });
                            echo count($recent);
                            ?>
                        </h3>
                        <p class="card-text text-muted">Last 30 Days</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Class Filter -->
        <?php if (!empty($teacher_classes)): ?>
        <div class="card mb-4 border-light shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter by Class</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2" id="class-filter">
                    <button type="button" class="btn btn-primary" data-filter="all">
                        All Classes <span class="badge bg-light text-dark ms-1"><?php echo count($students); ?></span>
                    </button>
                    <?php foreach ($teacher_classes as $class): 
                        $class_students = array_filter($students, function($s) use ($class) {
                            return $s['class_id'] == $class['class_id'];
                        });
                        $student_count = count($class_students);
                    ?>
                    <button type="button" class="btn btn-outline-secondary" 
                            data-filter="class-<?php echo $class['class_id']; ?>"
                            data-class-id="<?php echo $class['class_id']; ?>">
                        <?php echo htmlspecialchars($class['faculty']); ?> Sem <?php echo $class['semester']; ?>
                        <span class="badge bg-secondary ms-1"><?php echo $student_count; ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Students Table -->
        <div class="card border-light shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2"></i>Student List
                            <small class="text-muted ms-2">(<?php echo count($students); ?> students)</small>
                        </h5>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search-student" 
                                   placeholder="Search students...">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (empty($teacher_classes)): ?>
                    <div class="text-center py-5">
                        <div class="alert alert-warning mx-4">
                            <i class="bi bi-exclamation-triangle-fill fs-1 mb-3"></i>
                            <h4 class="alert-heading">No Classes Assigned</h4>
                            <p>You don't have any classes assigned to you yet.</p>
                            <hr>
                            <p class="mb-0 text-muted">Please contact the administrator to get classes assigned.</p>
                        </div>
                    </div>
                <?php elseif (empty($students)): ?>
                    <div class="text-center py-5">
                        <div class="alert alert-info mx-4">
                            <i class="bi bi-people-fill fs-1 mb-3"></i>
                            <h4 class="alert-heading">No Students Found</h4>
                            <p>There are no students in your assigned classes yet.</p>
                            <hr>
                            <p class="mb-0 text-muted">Students need to be added to your classes by the administrator.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="students-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="120">Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th width="100">Class</th>
                                    <th width="100">Semester</th>
                                    <th width="120">Phone</th>
                                    <th width="100">Status</th>
                                    <th width="120">Created</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $status_class = $student['is_active'] == 1 ? 'text-success' : 'text-muted';
                                    $status_text = $student['is_active'] == 1 ? 'Active' : 'Inactive';
                                    $status_icon = $student['is_active'] == 1 ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                                    $phone = $student['phone_number'] ?: 'N/A';
                                    $created_date = date('M d, Y', strtotime($student['created_at']));
                                ?>
                                <tr data-class-id="<?php echo $student['class_id']; ?>" 
                                    data-search="<?php echo strtolower(htmlspecialchars($student['student_name'] . ' ' . $student['student_id'] . ' ' . $student['email'])); ?>">
                                    <td>
                                        <span class="student-id text-primary">
                                            <?php echo htmlspecialchars($student['student_id']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person-fill text-white"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <?php echo htmlspecialchars($student['student_name']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($student['email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-faculty text-white">
                                            <?php echo htmlspecialchars($student['faculty']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-semester text-white">
                                            Sem <?php echo $student['semester']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($phone); ?></code>
                                    </td>
                                    <td>
                                        <span class="<?php echo $status_class; ?>">
                                            <i class="bi <?php echo $status_icon; ?> me-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo $created_date; ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-view-result" 
                                                    data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>"
                                                    title="View Results">
                                                <i class="bi bi-trophy"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-view-details"
                                                    data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>"
                                                    title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($students)): ?>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">
                            Showing <span id="showing-count"><?php echo count($students); ?></span> 
                            of <?php echo count($students); ?> students
                        </span>
                    </div>
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            Updated: <?php echo date('h:i A'); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript (separated from PHP) -->
    <script>
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeMyStudents();
        });

        function initializeMyStudents() {
            // Class filter functionality
            setupClassFilter();
            
            // Search functionality
            setupSearch();
            
            // Button event handlers
            setupButtonHandlers();
        }

        function setupClassFilter() {
            const filterButtons = document.querySelectorAll('#class-filter button[data-filter]');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Update active button styles
                    filterButtons.forEach(btn => {
                        btn.classList.remove('btn-primary', 'active');
                        btn.classList.add('btn-outline-secondary');
                    });
                    this.classList.add('btn-primary', 'active');
                    this.classList.remove('btn-outline-secondary');
                    
                    // Filter table rows
                    filterTableRows(filterValue);
                });
            });
        }

        function filterTableRows(filterValue) {
            const rows = document.querySelectorAll('#students-table tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const classId = row.getAttribute('data-class-id');
                const rowClass = 'class-' + classId;
                
                if (filterValue === 'all' || rowClass === filterValue) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update counters
            updateCounters(visibleCount);
        }

        function setupSearch() {
            const searchInput = document.getElementById('search-student');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#students-table tbody tr');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    const searchData = row.getAttribute('data-search');
                    const isVisible = row.style.display !== 'none';
                    
                    if (searchData.includes(searchTerm) && isVisible) {
                        row.style.display = '';
                        visibleCount++;
                    } else if (searchTerm === '' && isVisible) {
                        row.style.display = '';
                        visibleCount++;
                    } else if (isVisible) {
                        row.style.display = 'none';
                    }
                });
                
                updateCounters(visibleCount);
            });
        }

        function updateCounters(visibleCount) {
            const showingCount = document.getElementById('showing-count');
            const studentCountBadge = document.getElementById('student-count');
            
            if (showingCount) showingCount.textContent = visibleCount;
            if (studentCountBadge) studentCountBadge.textContent = visibleCount;
        }

        function setupButtonHandlers() {
            // View Results button
            document.querySelectorAll('.btn-view-result').forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    viewStudentResults(studentId);
                });
            });
            
            // View Details button
            document.querySelectorAll('.btn-view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    viewStudentDetails(studentId);
                });
            });
        }

        function viewStudentResults(studentId) {
            // Show loading or message
            alert('Viewing results for student: ' + studentId + '\n\nThis feature will be available in the Student Portal.');
            
            // Future implementation:
            // window.parent.loadContent('student_results.php?student_id=' + encodeURIComponent(studentId));
        }

        function viewStudentDetails(studentId) {
            // Show loading or message
            alert('Viewing details for student: ' + studentId + '\n\nThis feature will be available in the Student Portal.');
            
            // Future implementation:
            // window.parent.loadContent('student_details.php?student_id=' + encodeURIComponent(studentId));
        }

        // Export functions to window for parent frame access if needed
        window.MyStudents = {
            initializeMyStudents,
            filterTableRows,
            viewStudentResults,
            viewStudentDetails
        };
    </script>
</body>
</html>