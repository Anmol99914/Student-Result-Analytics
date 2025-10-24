<?php
// Connect to database
include('config.php');

session_start();

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $roll = $_POST['roll_num'];
    $semester = $_POST['semester'];
}
// var_dump($roll, $semester);
// exit();
if (empty($roll) || empty($semester)) {
    echo "<script>
    alert('Please enter Roll Number and select Semester'); 
    window.location.href='student_login.html';
    </script>";
    exit();
}

// Check if the student exists (p.s hopefully hos :) 
$sql = "SELECT * FROM student WHERE student_id = '$roll' AND semester_id = '$semester'";
$result = mysqli_query($connection, $sql);

if(mysqli_num_rows($result) > 0){
    $_SESSION['student_logged_in'] = true;
    $_SESSION['student_id'] = $roll;
    $_SESSION['semester_id'] = $semester;
    header('Location: student_dashboard.php');
    exit();
}
else{
    echo "<script>
    alert('Invalid roll number or semester!');
    window.location.href = 'student_login.html';
    </script>";
}


?>