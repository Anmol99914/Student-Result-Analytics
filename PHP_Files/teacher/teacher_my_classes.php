<!-- teacher_my_classes.php -->
<?php
include '../../../config.php';
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
?>

<h2>My Classes</h2>
<?php
// Fetch distinct semesters/classes the teacher has students in
$stmt = $connection->prepare("
    SELECT DISTINCT semester, faculty
    FROM students
    WHERE assigned_teacher_id=?
    ORDER BY semester ASC
");
$stmt->bind_param("i", $_SESSION['teacher_id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    echo "<ul>";
    while($row = $result->fetch_assoc()){
        echo "<li>Faculty: " . htmlspecialchars($row['faculty']) . " | Semester: " . htmlspecialchars($row['semester']) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No classes assigned yet.</p>";
}
