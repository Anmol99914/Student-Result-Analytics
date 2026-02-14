<?php
include('../../config.php');
session_start();

// Prevent caching 
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Get data from form 
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Basic validation 
if ($username === '' || $password === '') {
    header("Location: admin_login.php?error=empty");
    exit();
}

// Fetch admin from database 
$stmt = $connection->prepare(
    "SELECT admin_id, name, email, password 
     FROM administrator 
     WHERE email = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

/* Check credentials */
if ($admin = $result->fetch_assoc()) {

    if (password_verify($password, $admin['password'])) {

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['name'];

        header("Location: admin_main_page.php");
        exit();
    }
}

/* Invalid login */
header("Location: admin_login.php?error=invalid");
exit();
?>
