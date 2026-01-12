<!-- teacher_my_students.php -->
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");

// Teacher login check
if(!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true){
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

// Database connection
// require_once "../config.php";

require_once $_SERVER['DOCUMENT_ROOT'] . '/Student_Result_Analytics/config.php';

// Fetch all students
$result = $connection->query("SELECT * FROM student ORDER BY student_id ASC");
?>

<style>
.view-student-card {
    max-width: 1000px;
    margin: auto;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 0 12px rgba(0,0,0,0.08);
}
.view-student-card h3 {
    background: #0d6efd;
    color: white;
    padding: 12px;
    border-radius: 10px 10px 0 0;
}
.table td, .table th {
    vertical-align: middle;
}
.btn-action {
    margin-right: 5px;
}
</style>

<div class="view-student-card mt-3">
    <h3 class="text-center">Students List</h3>
    <div class="p-4">

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Class ID</th>
                    <th>Semester</th>
                    <th>Phone Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['class_id']) ?></td>
                    <td><?= htmlspecialchars($row['semester_id']) ?></td>
                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td>
                        <a href="edit_student.php?student_id=<?= urlencode($row['student_id']) ?>" class="btn btn-sm btn-warning btn-action">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="delete_student.php?student_id=<?= urlencode($row['student_id']) ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Are you sure?');">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No students found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>
