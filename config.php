<?php
$host = "localhost";
$username = "root";
$password = "installationprocess12345";
$database = "student_result";
$port = 3307;

$connection = mysqli_connect($host,$username,$password,$database,$port);

if(!$connection){
    echo "Database couldnot be connected!!!!";
}
?>