<?php
// File: PHP_Files/student/pages/login.php
require_once '../includes/auth_check.php';

// Add no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$page_title = 'Student Portal - Login';
$page_css = 'login';
$page_js = 'login';

require_once '../includes/header.php';

// Error messages
$error_msg = '';
if(isset($_GET['error'])){
    if($_GET['error'] === "invalid"){
        $error_msg = "Invalid Student ID or password!";
    } elseif ($_GET['error'] === "inactive") {
        $error_msg = "Your account is inactive. Please contact administration.";
    } elseif ($_GET['error'] === "empty") {
        $error_msg = "Please fill in all fields.";
    } elseif ($_GET['error'] === "network") {
        $error_msg = "Network error. Please check your connection.";
    }
}
?>

<!-- Back to Home -->
<a href="../../../index.html" class="btn back-home-btn position-fixed" style="top: 25px; left: 25px; z-index: 1000;">
    <i class="bi bi-arrow-left me-2"></i>Back to Home
</a>

<!-- Main Container -->
<div class="login-wrapper">
    <div class="login-container p-5 position-relative">
        <!-- Status Badge -->
        <div class="status-badge">
            <i class="bi bi-mortarboard me-2"></i>Student Portal
        </div>
        
        <!-- Student Icon -->
        <div class="student-icon">
            <i class="bi bi-person-circle text-white fs-2"></i>
        </div>
        
        <!-- Page heading -->
        <div class="header-text">
            <h2>Student Portal</h2>
            <p>View Results & Academic Profile</p>
        </div>

        <!-- Welcome Note -->
        <div class="welcome-note">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle-fill fs-5 me-3" style="color: #28a745;"></i>
                <div>
                    <h6 class="mb-2" style="color: #218838;">Welcome Students!</h6>
                    <p class="mb-0 small">Access your academic results, view profile details, and check payment status securely.</p>
                </div>
            </div>
        </div>

        <?php if($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div>
                    <strong class="d-block">Login Failed</strong>
                    <span class="small"><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="../api/login_validate.php" method="POST" id="studentForm" class="needs-validation" novalidate>
                    <div class="mb-4">
                <!-- Username field:)  -->
                <label for="username" class="form-label">
                    <i class="bi bi-person-badge me-2"></i>Student ID
                </label>
                <input type="text" class="form-control" id="username" name="username" 
                    placeholder="Enter your Student ID" required autocomplete="username">
                <div class="invalid-feedback">
                    Please enter your Student ID.
                </div>
                <small class="form-text text-muted">e.g., BCA001, BBM001</small>
            </div>

            <!--  password field  -->
            <div class="mb-4">
                <label for="password" class="form-label">
                    <i class="bi bi-key me-2"></i>Password
                </label>
                <input type="password" class="form-control" id="password" name="password" 
                    placeholder="Enter your password" required autocomplete="current-password">
                <div class="invalid-feedback">
                    Please enter your password.
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="#" class="help-links small" onclick="showForgotPasswordModal()">
                    <i class="bi bi-question-circle me-1"></i>Forgot password?
                </a>
            </div>
            
            <button type="submit" class="btn btn-student w-100" id="loginBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Access Student Dashboard
            </button>
        </form>
        <br>
        <br>
        <!-- Copyright -->
        <div class="copyright">
            <p class="mb-2">© 2026 Student Result Analytics</p>
        </div>
    </div>
</div>

<script>
    // Forgot Password Modal
function showForgotPasswordModal() {
    const modalHtml = `
        <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-key me-2"></i>
                            Reset Password
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock display-1 text-primary"></i>
                        </div>
                        <p class="text-center">For password reset, please contact the administrator:</p>
                        
                        <div class="list-group">
                            <div class="list-group-item d-flex align-items-center">
                                <i class="bi bi-envelope-fill text-primary me-3 fs-4"></i>
                                <div>
                                    <small class="text-muted">Email</small>
                                    <div class="fw-bold">giria4621@gmail.com</div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary ms-auto" onclick="copyText('giria4621@gmail.com')">
                                    <i class="bi bi-files"></i>
                                </button>
                            </div>
                            
                            <div class="list-group-item d-flex align-items-center">
                                <i class="bi bi-telephone-fill text-primary me-3 fs-4"></i>
                                <div>
                                    <small class="text-muted">Phone</small>
                                    <div class="fw-bold">+977 9818118344</div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary ms-auto" onclick="copyText('+977 9818118344')">
                                    <i class="bi bi-files"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('forgotPasswordModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
    modal.show();
    
    document.getElementById('forgotPasswordModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function copyText(text) {
    navigator.clipboard.writeText(text)
        .then(() => alert('Copied to clipboard!'))
        .catch(() => alert('Failed to copy'));
}
</script>
<?php require_once '../includes/footer.php'; ?>