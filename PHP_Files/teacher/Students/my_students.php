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

$class_sql = "SELECT DISTINCT 
                c.class_id, 
                c.faculty, 
                c.semester,
                c.status,
                (SELECT COUNT(*) FROM student WHERE class_id = c.class_id AND is_active = 1) as student_count
            FROM class c
            INNER JOIN teacher_subject_assignment tsa ON c.class_id = tsa.class_id
            WHERE tsa.teacher_id = ? 
            AND c.status = 'active'
            ORDER BY c.faculty, c.semester";

$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("i", $teacher_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();
$teacher_classes = $class_result->fetch_all(MYSQLI_ASSOC);
$class_stmt->close();

// Get class IDs for query
$class_ids = array_column($teacher_classes, 'class_id');

// Get students from these classes
$students = [];
if (!empty($class_ids)) {
    $placeholders = str_repeat('?,', count($class_ids) - 1) . '?';
    
    $student_sql = "SELECT s.*, 
                           c.faculty, 
                           c.semester
                    FROM student s
                    INNER JOIN class c ON s.class_id = c.class_id
                    WHERE s.class_id IN ($placeholders) 
                    AND s.is_active = 1
                    ORDER BY c.faculty, c.semester, s.student_name";
    
    $student_stmt = $connection->prepare($student_sql);
    $types = str_repeat('i', count($class_ids));
    $student_stmt->bind_param($types, ...$class_ids);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $students = $student_result->fetch_all(MYSQLI_ASSOC);
    $student_stmt->close();
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
    
    <style>
        .student-id { font-family: 'Courier New', monospace; font-weight: bold; }
        .badge-faculty { background-color: #6f42c1; }
        .badge-semester { background-color: #fd7e14; }
        .table-hover tbody tr:hover { background-color: rgba(0, 123, 255, 0.05); }
        .filter-btn.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
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
                </p>
            </div>
            <div>
                <button onclick="window.location.href='teacher_dashboard.php'" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <?php if (empty($teacher_classes)): ?>
            <div class="text-center py-5">
                <div class="alert alert-warning mx-4">
                    <i class="bi bi-exclamation-triangle-fill fs-1 mb-3"></i>
                    <h4 class="alert-heading">No Classes Assigned</h4>
                    <p>You don't have any subjects assigned to any classes yet.</p>
                    <hr>
                    <p class="mb-0 text-muted">Contact the administrator to assign subjects to you.</p>
                </div>
            </div>
        <?php elseif (empty($students)): ?>
            <div class="text-center py-5">
                <div class="alert alert-info mx-4">
                    <i class="bi bi-people-fill fs-1 mb-3"></i>
                    <h4 class="alert-heading">No Students Found</h4>
                    <p>There are no active students in your assigned classes.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Class Filter -->
            <div class="card mb-4 border-light shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter by Class</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2" id="class-filter">
                        <button type="button" class="btn btn-primary filter-btn active" data-filter="all">
                            All Classes <span class="badge bg-light text-dark ms-1"><?php echo count($students); ?></span>
                        </button>
                        <?php foreach ($teacher_classes as $class): 
                            $class_students = array_filter($students, function($s) use ($class) {
                                return $s['class_id'] == $class['class_id'];
                            });
                            $student_count = count($class_students);
                        ?>
                        <button type="button" class="btn btn-outline-secondary filter-btn" 
                                data-filter="class-<?php echo $class['class_id']; ?>">
                            <?php echo htmlspecialchars($class['faculty']); ?> Sem <?php echo $class['semester']; ?>
                            <span class="badge bg-secondary ms-1"><?php echo $student_count; ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card border-light shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2"></i>Student List
                            <small class="text-muted ms-2">(<?php echo count($students); ?> students)</small>
                        </h5>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="search-student" 
                                   placeholder="Search by name or ID...">
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="students-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Semester</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $phone = $student['phone_number'] ?: 'N/A';
                                ?>
                                <tr data-class-id="<?php echo $student['class_id']; ?>">
                                    <td>
                                        <span class="student-id text-primary">
                                            <?php echo htmlspecialchars($student['student_id']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small></td>
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
                                    <td><code><?php echo htmlspecialchars($phone); ?></code></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/Student_Result_Analytics/js/teacher/student-filter.js"></script>
</body>
</html>