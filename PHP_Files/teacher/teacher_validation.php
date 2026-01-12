<!-- teacher_validation.php -->
<?php
session_start();

include('../../config.php');

// Prevent direct access
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: teacher_login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Validate inputs
if(empty($email) || empty($password)){
    header("Location: teacher_login.php?error=empty");
    exit();
}

// Check in teacher table (teachers login with email)
$stmt = $connection->prepare("SELECT teacher_id, name, email, password, status FROM teacher WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows === 0){
    // No teacher found with this email
    header("Location: teacher_login.php?error=invalid");
    exit();
}

$stmt->bind_result($teacher_id, $teacher_name, $teacher_email, $hashed_password, $status);
$stmt->fetch();
$stmt->close();

// Verify password
if(password_verify($password, $hashed_password)) {
    // Check if teacher is active
    if($status !== 'active'){
        // Teacher is inactive
        header("Location: teacher_login.php?error=inactive");
        exit();
    }
    
    // Set session variables
    $_SESSION['teacher_logged_in'] = true;
    $_SESSION['teacher_id'] = $teacher_id;
    $_SESSION['teacher_name'] = $teacher_name;
    $_SESSION['teacher_email'] = $teacher_email;

    // Redirect to dashboard
    header("Location: teacher_dashboard.php");
    exit();

} else {
    // Wrong password
    header("Location: teacher_login.php?error=invalid");
    exit();
}
?>