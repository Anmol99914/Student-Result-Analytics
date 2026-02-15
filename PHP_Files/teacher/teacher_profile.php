<!-- teacher_profile.php -->
<?php
include '../../config.php';
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
session_start();

// Role-based access
if(!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true){
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher info - REMOVED 'phone' column
$stmt = $connection->prepare("SELECT teacher_id, name, email, status, created_at FROM teacher WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    echo '<div class="alert alert-danger">Teacher not found</div>';
    exit();
}
?>

<div class="container-fluid py-4">
    <!-- Header with back button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person-circle text-primary me-2"></i>My Profile</h2>
        <button class="btn btn-outline-secondary" onclick="loadDashboard()">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </button>
    </div>

    <!-- Profile Information Card -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Personal Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Teacher ID:</th>
                            <td><strong class="text-primary"><?= htmlspecialchars($teacher['teacher_id']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Full Name:</th>
                            <td><?= htmlspecialchars($teacher['name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-<?= $teacher['status'] == 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($teacher['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Member Since:</th>
                            <td><?= date('d M Y', strtotime($teacher['created_at'])) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <!-- <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="changePasswordForm" onsubmit="return false;">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </button>
                        <div id="passwordMessage" class="mt-3"></div>
                    </form>
                </div>
            </div>
        </div> -->
    </div>
</div>

<!-- Password Change Script -->
<!-- <script>
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const currentPwd = document.getElementById('current_password').value;
    const newPwd = document.getElementById('new_password').value;
    const confirmPwd = document.getElementById('confirm_password').value;
    const messageDiv = document.getElementById('passwordMessage');
    const submitBtn = document.getElementById('changePasswordBtn');
    
    // Validate
    if (newPwd.length < 6) {
        messageDiv.innerHTML = '<div class="alert alert-danger">New password must be at least 6 characters</div>';
        return;
    }
    
    if (newPwd !== confirmPwd) {
        messageDiv.innerHTML = '<div class="alert alert-danger">New passwords do not match</div>';
        return;
    }
    
    // Show loading
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Changing...';
    submitBtn.disabled = true;
    
    // Send request
    fetch('teacher_change_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'current_password=' + encodeURIComponent(currentPwd) + 
              '&new_password=' + encodeURIComponent(newPwd) + 
              '&confirm_password=' + encodeURIComponent(confirmPwd)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            messageDiv.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle"></i> ' + data.message + '</div>';
            document.getElementById('changePasswordForm').reset();
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        messageDiv.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
    });
});
</script> -->