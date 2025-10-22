<?php
include('config.php');

session_start();

//Get data from form
$username = $_POST['username'];
$password = $_POST['password'];

$valid_username = "admin999";
$valid_password = "iamthefreakingadmin";

if($username === $valid_username && $password === $valid_password){
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    header("Location: admin_main_page.php");
    exit();
}
else{
    echo "<script>
    alert('Invalid username or password!');
    window.location.href = 'admin_login.html';
    </script>
    ";
}
?>