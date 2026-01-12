<!-- teacher_profile.php -->
<?php
include '../../config.php';
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
session_start();

// Role-based access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher'){
    header("Location: teacher_login.php");
    exit();
}

// Fetch teacher info
$stmt = $connection->prepare("SELECT * FROM teachers WHERE teacher_id=?");
$stmt->bind_param("i", $_SESSION['teacher_id']);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
?>

<h2>My Profile</h2>
<table class="table table-bordered" style="max-width:600px;">
    <tr>
        <th>Full Name</th>
        <td><?= htmlspecialchars($teacher['full_name']) ?></td>
    </tr>
    <tr>
        <th>Department</th>
        <td><?= htmlspecialchars($teacher['department']) ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?= htmlspecialchars($teacher['status']) ?></td>
    </tr>
</table>

<h3>Change Password</h3>
<form action="teacher_change_password.php" method="post">
    <div class="mb-3">
        <label>Current Password:</label>
        <input type="password" name="current_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>New Password:</label>
        <input type="password" name="new_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Change Password</button>
</form>
