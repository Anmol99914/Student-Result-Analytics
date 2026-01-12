<?php
// admin_main_page.php 
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Analytics - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Our CSS Files -->
    <link rel="stylesheet" href="../../css/admin/admin-main.css">
    <link rel="stylesheet" href="../../css/admin/admin-responsive.css">
    <link rel="stylesheet" href="css/admin/verified-results.css">
    <script src="js/admin/verified-results.js" defer></script>

    <script>
// EMERGENCY: Define loadResultsVerification immediately ughhhh
        window.loadResultsVerification = function() {
            console.log('loadResultsVerification called (temporary)');
            
            // If admin-main.js hasn't loaded yet, wait for it
            if (typeof loadPage === 'function') {
                console.log('Loading Results Verification...');
                loadPage('Results/pending_results.php');
            } else {
                console.error('loadPage function not available yet');
                // Wait a bit and try again
                setTimeout(function() {
                    if (typeof loadPage === 'function') {
                        loadPage('Results/pending_results.php');
                    } else {
                        alert('Please wait for page to load completely');
                    }
                }, 500);
            }
        };
        console.log('loadResultsVerification defined (temporary)');
        </script>

    <script src="../../js/admin/admin-main.js"></script>
    <script src="../../js/admin/common.js"></script>
    <!-- Load Results Verification JS -->
    <!-- <script src="js/admin/results-verification.js"></script> -->

</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary d-md-none me-2" type="button" 
                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand mb-0">Student Result Analytics | Admin</a>
            </div>
            <form role="search" action="logout.php">
                <button class="btn btn-outline-danger" type="submit">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid flex-grow-1">
        <div class="row flex-md-nowrap flex-wrap">
            <!-- Desktop Sidebar -->
            <div class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3 d-none d-md-block admin-sidebar">
                <h5>SRA | Admin</h5>
                <ul class="nav flex-column mt-4">
                <!-- Dashboard stuffs -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="loadDashboard(); return false;">
                        <i class="bi bi-house"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                 
                <!-- Teacher Management stuffs -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="loadTeacherManagement(); return false;">
                        <i class="bi bi-person-square"></i>
                        <span>Teacher Management</span>
                    </a>
                </li>
                    <!-- Class Management Stuffs -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadClassManagement(); return false;">
                            <i class="bi bi-mortarboard-fill"></i>
                            <span>Class Management</span>
                        </a>
                    </li>
                    <!-- Student Management -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadStudentManagement(); return false;">
                            <i class="bi bi-people-fill"></i>
                            <span>Student Management</span>
                        </a>
                    </li>

                <!-- Subject management:) -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="loadSubjectManagement(); return false;">
                        <i class="bi bi-book"></i>
                        <span>Subject Management</span>
                    </a>
                </li>
                    <!-- Results -->
                    <li class="nav-item mb-2">
                    <a href="results.php" class="nav-link text-white">
                        <i class="bi bi-trophy"></i> Results
                    </a>
                </li>
                <!-- Result Verification -->
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="loadResultsVerification(); return false;">
                        <i class="bi bi-shield-check"></i>
                        <span>Results Verification</span>
                        <span class="badge bg-danger" id="pending-count">0</span>
                    </a>
                </li>
                    
                    
                    <!-- Assign Teachers -->
                    <!-- <li class="nav-item mb-2">
        <a href="assign_teachers.php" class="nav-link text-white">
            <i class="bi bi-person-plus"></i> Assign Teachers
        </a>
    </li> -->
                </ul>
            </div>
            
            <!-- Mobile Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="offcanvasSidebar">
                <div class="offcanvas-header bg-dark text-white">
                    <h5 class="offcanvas-title">SRA | Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body bg-dark text-white">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('home.php')" class="nav-link text-white">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('pages/class_management.php')" class="nav-link text-white">
                                <i class="bi bi-mortarboard-fill"></i> Classes
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('pages/teacher_management.php')" class="nav-link text-white">
                                <i class="bi bi-person-square"></i> Teachers
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('students_list.php')" class="nav-link text-white">
                                <i class="bi bi-people"></i> Students
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('subjects.php')" class="nav-link text-white">
                                <i class="bi bi-book"></i> Subjects
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('results.php')" class="nav-link text-white">
                                <i class="bi bi-trophy"></i> Results
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('assign_teachers.php')" class="nav-link text-white">
                                <i class="bi bi-person-plus"></i> Assign Teachers
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Area -->
            <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4">
                <?php 
                // Load default page
                if(isset($_GET['page'])) {
                    $page = basename($_GET['page']);
                    $allowed_pages = ['home', 'classes', 'teachers', 'students', 'subjects', 'results'];
                    
                    if(in_array($page, $allowed_pages)) {
                        include("pages/{$page}_management.php");
                    } else {
                        include("pages/home.php");
                    }
                } else {
                    include("pages/home.php");
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto bg-light text-center py-2 border-top">
        © 2025 Student Result Analytics | Admin Panel
    </footer>

   <!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Our JavaScript Files -->
<script>
    // Optional: Add any page-specific initialization here
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Admin panel ready');
    });
</script>
</body>
</html>