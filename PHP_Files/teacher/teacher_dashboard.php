<?php
// teacher_dashboard.php 
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Student Result Analytics - Teacher</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Teacher CSS -->
    <link rel="stylesheet" href="../../css/teacher_dashboard.css">

</head>
<body class="teacher-wrapper">
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light teacher-navbar">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary d-md-none me-2" type="button" 
                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand mb-0 fw-bold">
                    <i class="bi bi-person-badge text-primary me-2"></i>
                    Teacher Dashboard
                </a>
                <span class="ms-3 text-muted d-none d-md-block welcome-text">
                    Welcome, <?php echo htmlspecialchars($teacher_name); ?>
                </span>
            </div>
            <form class="d-flex" action="teacher_logout.php">
                <button class="btn btn-outline-danger" type="submit">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid flex-grow-1 teacher-container">
        <div class="row flex-md-nowrap flex-wrap">
            <!-- Desktop Sidebar -->
            <div class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3 d-none d-md-block teacher-sidebar">
                <h5 class="mb-4">
                    <i class="bi bi-person-badge me-2"></i>
                    Teacher Panel
                </h5>
                <ul class="nav flex-column mt-4">
                    <li class="nav-item">
                        <a href="#" onclick="loadDashboard(); return false;" class="nav-link active">
                            <i class="bi bi-house"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" onclick="loadMyClasses(); return false;" class="nav-link">
                            <i class="bi bi-table"></i>
                            <span>My Classes</span>
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="#" onclick="loadAddStudentForm(); return false;" class="nav-link">
                            <i class="bi bi-person-plus"></i>
                            <span>Add Student</span>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a href="#" onclick="loadMyStudents(); return false;" class="nav-link">
                            <i class="bi bi-people"></i>
                            <span>My Students</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" onclick="loadAddResultForm(); return false;" class="nav-link">
                            <i class="bi bi-trophy"></i>
                            <span>Enter Results</span>
                        </a>
                    </li>
                
                    <li class="nav-item">
                        <a href="#" onclick="loadProfile(); return false;" class="nav-link">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Mobile Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="offcanvasSidebar">
                <div class="offcanvas-header bg-dark text-white">
                    <h5 class="offcanvas-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Teacher Panel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body bg-dark text-white">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadDashboard(); closeOffcanvas(); return false;" class="nav-link">
                                <i class="bi bi-house me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadMyClasses(); closeOffcanvas(); return false;" class="nav-link">
                                <i class="bi bi-table me-2"></i> My Classes
                            </a>
                        </li>
                        <!-- <li class="nav-item mb-2">
                            <a href="#" onclick="loadAddStudentForm(); closeOffcanvas(); return false;" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i> Add Student
                            </a>
                        </li> -->
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadMyStudents(); closeOffcanvas(); return false;" class="nav-link">
                                <i class="bi bi-people me-2"></i> My Students
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadAddResultForm(); closeOffcanvas(); return false;" class="nav-link">
                                <i class="bi bi-trophy me-2"></i> Enter Results
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadProfile(); closeOffcanvas(); return false;" class="nav-link">
                                <i class="bi bi-person me-2"></i> My Profile
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Area -->
            <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4 teacher-main-content">
                <!-- Content will be loaded by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto bg-light text-center py-2 border-top teacher-footer">
        © 2025 Student Result Analytics | Teacher Panel
    </footer>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Teacher JavaScript Files -->
    <script src="../../js/teacher/teacher-common.js"></script>
    <script src="../../js/teacher/teacher-main.js"></script>

    <!-- Load results system JavaScript -->
    <script src="../../js/results-main.js"></script>
    <script src="../../js/results-marks.js"></script>

    <script>
    // Pass PHP variables to JavaScript
    window.TEACHER_ID = <?php echo json_encode($teacher_id); ?>;
    window.TEACHER_NAME = <?php echo json_encode($teacher_name); ?>;
    </script>
</body>
</html>