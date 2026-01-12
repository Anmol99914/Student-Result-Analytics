<!-- teacher_home.php -->
<?php
session_start();
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
include('../config.php');

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit();
}

// Get teacher's assigned classes
$teacher_id = $_SESSION['teacher_id'];
$stmt = $connection->prepare("SELECT COUNT(*) as class_count FROM class WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$stmt->bind_result($class_count);
$stmt->fetch();
$stmt->close();

// Get teacher's students count
$stmt2 = $connection->prepare("SELECT COUNT(*) as student_count FROM student s 
                               JOIN class c ON s.class_id = c.class_id 
                               WHERE c.teacher_id = ?");
$stmt2->bind_param("i", $teacher_id);
$stmt2->execute();
$stmt2->bind_result($student_count);
$stmt2->fetch();
$stmt2->close();
?>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?> 👨‍🏫</h2>
        <p class="text-muted">
            Manage your classes, students, and enter results from this dashboard.
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4">
        <!-- My Classes -->
        <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-table display-4 text-primary"></i>
                    <h3 class="mt-3"><?php echo $class_count; ?></h3>
                    <h5 class="card-title">My Classes</h5>
                    <p class="card-text">Classes assigned to you</p>
                    <button class="btn btn-outline-primary" onclick="showMyClasses()">
                        View Classes
                    </button>
                </div>
            </div>
        </div>

        <!-- My Students -->
        <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 text-success"></i>
                    <h3 class="mt-3"><?php echo $student_count; ?></h3>
                    <h5 class="card-title">My Students</h5>
                    <p class="card-text">Students in your classes</p>
                    <button class="btn btn-outline-success" onclick="showMyStudents()">
                        View Students
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-lightning display-4 text-info"></i>
                    <h5 class="card-title mt-3">Quick Actions</h5>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-success" onclick="showAddStudentForm()">
                            <i class="bi bi-person-plus"></i> Add New Student
                        </button>
                        <button class="btn btn-warning" onclick="showAddResultForm()">
                            <i class="bi bi-trophy"></i> Enter Results
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity (Placeholder) -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
        </div>
        <div class="card-body">
            <p class="text-muted text-center py-3">
                Your recent activities will appear here.
            </p>
        </div>
    </div>
</div>