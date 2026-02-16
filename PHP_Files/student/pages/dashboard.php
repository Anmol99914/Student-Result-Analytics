<?php
// File: PHP_Files/student/pages/dashboard.php
require_once '../includes/auth_check.php';
require_student_login();

$page_title = 'Student Dashboard';
$page_css = 'dashboard';
$page_js = 'dashboard';

require_once '../includes/header.php';

$student_name = $_SESSION['student_name'] ?? 'Student';
$student_id = $_SESSION['student_username'] ?? '';
?>

<!-- ===== PRINT FUNCTION ===== -->
<script>
window.printResults = function() {
    console.log('Print called');
    
    // Hide buttons temporarily
    const buttons = document.querySelectorAll('.btn, .card-header .btn, .d-flex.gap-2');
    buttons.forEach(btn => btn.style.display = 'none');
    
    // Print
    window.print();
    
    // Restore buttons
    setTimeout(() => {
        buttons.forEach(btn => btn.style.display = '');
    }, 500);
    
    return false;
};

console.log('Print function loaded');

// Make sure loadPage function exists
window.loadPage = window.loadPage || function(url) {
    console.log('Loading page:', url);
    const mainContent = document.getElementById('main-content');
    if (!mainContent) return;
    
    // Show loading
    mainContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2">Loading...</p>
        </div>
    `;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            // Create a temporary container
            const temp = document.createElement('div');
            temp.innerHTML = html;
            
            // Extract all script tags
            const scripts = temp.querySelectorAll('script');
            
            // Remove scripts from HTML to prevent double execution
            scripts.forEach(script => script.remove());
            
            // Set the HTML (without scripts)
            mainContent.innerHTML = temp.innerHTML;
            
            // Now execute scripts one by one
            scripts.forEach(script => {
                if (script.src) {
                    // External script
                    const newScript = document.createElement('script');
                    newScript.src = script.src;
                    newScript.async = false;
                    document.head.appendChild(newScript);
                    console.log('Loaded external script:', script.src);
                } else {
                    // Inline script
                    try {
                        eval(script.textContent);
                        console.log('Executed inline script');
                    } catch (e) {
                        console.error('Script execution error:', e);
                    }
                }
            });
            
            console.log('Page loaded:', url);
        })
        .catch(error => {
            console.error('Fetch error:', error);
            mainContent.innerHTML = `
                <div class="alert alert-danger">
                    Error loading page: ${error.message}
                </div>
            `;
        });
};

// Load home page automatically
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const mainContent = document.getElementById('main-content');
        if (mainContent && mainContent.innerHTML.includes('Loading')) {
            loadPage('../pages/home.php');
            
            // Set active link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '../pages/home.php') {
                    link.classList.add('active');
                }
            });
        }
    }, 100);
});


</script>

<!-- Simple Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="bi bi-mortarboard-fill me-2"></i>
            Student Dashboard
        </a>
        
        <div class="ms-auto d-flex align-items-center">
            <span class="text-white me-3">
                <i class="bi bi-person-circle me-1"></i>
                <?php echo htmlspecialchars($student_name); ?>
            </span>
            
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown">
                    <i class="bi bi-gear"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <!-- <li>
                        <a class="dropdown-item" href="../pages/profile.php">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li> -->
                    <!-- <li>
                        <hr class="dropdown-divider">
                    </li> -->
                    <li>
                        <a class="dropdown-item text-danger" href="../api/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3 bg-dark min-vh-100 p-0">
            <div class="p-3">
                <!-- Student Info -->
                <div class="text-center text-white mb-4 p-3 border-bottom border-secondary">
                    <i class="bi bi-person-circle fs-1 mb-2"></i>
                    <h6 class="mb-1"><?php echo htmlspecialchars($student_name); ?></h6>
                    <small class="text-muted"><?php echo htmlspecialchars($student_id); ?></small>
                </div>
                
                <!-- Navigation -->
                <nav class="nav flex-column">
                    <a class="nav-link text-white mb-2" href="../pages/home.php" 
                       onclick="loadPage(this.href); return false;">
                        <i class="bi bi-house-door me-2"></i>Dashboard
                    </a>
                    <a class="nav-link text-white mb-2" href="../pages/profile.php" 
                       onclick="loadPage(this.href); return false;">
                        <i class="bi bi-person me-2"></i>My Profile
                    </a>
                    <a class="nav-link text-white mb-2" href="../pages/results.php" 
                       onclick="loadPage(this.href); return false;">
                        <i class="bi bi-clipboard-data me-2"></i>View Results
                    </a>
                    <!-- <a class="nav-link text-white mb-2" href="../pages/payments.php" 
                       onclick="loadPage(this.href); return false;">
                        <i class="bi bi-credit-card me-2"></i>Fee Payments
                    </a> -->
                </nav>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-lg-10 col-md-9">
            <div id="main-content" class="p-4">
                <!-- Content loaded via AJAX will appear here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading dashboard...</p>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="mt-auto py-3 border-top text-center text-muted small">
                <div class="container">
                    <p class="mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        Student Result Analytics System © <?php echo date('Y'); ?>
                    </p>
                </div>
            </footer>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>