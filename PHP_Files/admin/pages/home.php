<?php
// home.php - Admin Dashboard
// include('../../../config.php');
include(__DIR__ . '/../../../config.php');

?>
<div class="dashboard-container">
    <h1>Welcome to Student Result Analytics Admin Panel</h1>
    <p class="lead">Manage students, teachers, classes, subjects, and results from one dashboard.</p>
    
    <!-- Quick Stats Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
        <!-- Total Students -->
        <div class="col">
            <div class="card stats-card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Students</h6>
                            <?php
                            $student_count = $connection->query("SELECT COUNT(*) as count FROM student WHERE is_active = 1")->fetch_assoc()['count'];
                            ?>
                            <h3 class="mb-0"><?php echo $student_count; ?></h3>
                        </div>
                        <div class="icon-circle bg-primary">
                            <i class="bi bi-people text-white"></i>
                        </div>
                    </div>
                    <!-- <a href="#" onclick="loadPage('students_list.php')" class="stretched-link text-decoration-none"></a> -->
                </div>
            </div>
        </div>
        
        <!-- Active Teachers -->
        <div class="col">
            <div class="card stats-card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Teachers</h6>
                            <?php
                            $teacher_count = $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status = 'active'")->fetch_assoc()['count'];
                            ?>
                            <h3 class="mb-0"><?php echo $teacher_count; ?></h3>
                        </div>
                        <div class="icon-circle bg-success">
                            <i class="bi bi-person-check text-white"></i>
                        </div>
                    </div>
                    <!-- <a href="#" onclick="loadPage('pages/teacher_management.php')" class="stretched-link text-decoration-none"></a> -->
                </div>
            </div>
        </div>
        
        <!-- Active Classes -->
        <div class="col">
            <div class="card stats-card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Classes</h6>
                            <?php
                            $class_count = $connection->query("SELECT COUNT(*) as count FROM class WHERE status = 'active'")->fetch_assoc()['count'];
                            ?>
                            <h3 class="mb-0"><?php echo $class_count; ?></h3>
                        </div>
                        <div class="icon-circle bg-info">
                            <i class="bi bi-mortarboard text-white"></i>
                        </div>
                    </div>
                    <!-- <a href="#" onclick="loadPage('pages/class_management.php')" class="stretched-link text-decoration-none"></a> -->
                </div>
            </div>
        </div>
        
        <!-- Total Subjects -->
        <div class="col">
            <div class="card stats-card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Subjects</h6>
                            <?php
                            $subject_count = $connection->query("SELECT COUNT(*) as count FROM subject WHERE status = 'active'")->fetch_assoc()['count'];
                            ?>
                            <h3 class="mb-0"><?php echo $subject_count; ?></h3>
                        </div>
                        <div class="icon-circle bg-warning">
                            <i class="bi bi-book text-white"></i>
                        </div>
                    </div>
                    <!-- <a href="#" onclick="loadPage('subjects.php')" class="stretched-link text-decoration-none"></a> -->
                </div>
            </div>
        </div>
    </div>
    
  <!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <!-- Section Title: Management -->
                <div class="mb-3">
                    <small class="text-muted fw-bold text-uppercase">MANAGEMENT</small>
                </div>
                
                <!-- First Row - Management Actions -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <a href="#" onclick="loadPage('pages/manage_students.php?action=add')" 
                           class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-person-plus fs-2 mb-2"></i>
                            <span>Add Student</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="#" onclick="loadPage('pages/teacher_management.php?action=add')" 
                           class="btn btn-outline-secondary w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-person-plus fs-2 mb-2"></i>
                            <span>Add Teacher</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="#" onclick="loadPage('pages/class_management.php?action=add')" 
                           class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-plus-circle fs-2 mb-2"></i>
                            <span>Create Class</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="#" onclick="loadPage('pages/subject_management.php?action=add')" 
                           class="btn btn-outline-warning w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-person-check fs-2 mb-2"></i>
                            <span>Manage Subjects</span>
                        </a>
                    </div>
                </div>
                
                <!-- Second Row - Faculty & Performance -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6">
                        <a href="add_faculty.php" 
                           class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-plus-circle fs-2 mb-2"></i>
                            <span>Add Faculty</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="#" onclick="loadPage('pages/performance_viewer.php')" 
                           class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-bar-chart-fill fs-2 mb-2"></i>
                            <span>View Student's Performance</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <!-- Empty for spacing -->
                    </div>
                    <div class="col-md-3 col-6">
                        <!-- Empty for spacing -->
                    </div>
                </div>
                
                <!-- Section Title: Reports & Analytics -->
                <div class="mb-3 mt-2">
                    <small class="text-muted fw-bold text-uppercase">REPORTS & ANALYTICS</small>
                </div>
                
                <!-- Third Row - Reports -->
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <a href="dashboard_stats.php" 
                           class="btn btn-outline-info w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-speedometer2 fs-2 mb-2"></i>
                            <span>Dashboard Stats</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="export_results.php" 
                           class="btn btn-success w-100 d-flex flex-column align-items-center py-3">
                            <i class="bi bi-download fs-2 mb-2"></i>
                            <span>Export Results</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <!-- Placeholder for future action -->
                    </div>
                    <div class="col-md-3 col-6">
                        <!-- Placeholder for future action -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php
                        // Get recent students
                        $recent_students = $connection->query("
                            SELECT student_id, student_name, created_at 
                            FROM student 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        
                        while($student = $recent_students->fetch_assoc()) {
                            $time_ago = time_elapsed_string($student['created_at']);
                            echo '
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-sm bg-primary me-3">
                                        <i class="bi bi-person text-white"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong>New Student:</strong> ' . $student['student_name'] . '
                                        <div class="text-muted small">' . $time_ago . '</div>
                                    </div>
                                    <span class="badge bg-light text-dark">' . $student['student_id'] . '</span>
                                </div>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-calendar-event"></i> System Information</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <span>Academic Year:</span>
                                <strong>2026</strong>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <span>Faculties:</span>
                                <strong>BCA, BBM, BIM</strong>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0">
                            <div class="d-flex justify-content-between">
                                <span>Total Semesters:</span>
                                <strong>8</strong>
                            </div>
                        </div>
    
                    
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Helper function for time ago (PHP 8.2+ compatible)
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>