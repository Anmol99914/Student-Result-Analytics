<?php

error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off display, log instead
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');

$host = "localhost";
$username = "root";
$password = "installationprocess12345";
$database = "sra";
$port = 3307;


$connection = mysqli_connect($host,$username,$password,$database,$port);

if(!$connection){
    echo "Database couldnot be connected!!!!";
}
?>