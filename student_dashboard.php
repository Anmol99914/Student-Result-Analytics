<?php
include('config.php');
session_start();

if(!isset($_SESSION['student_logged_in']) || isset($_SESSION['student_logged_in'])!= true){
    header("Location: student_login.html");
    exit();
}
$roll = $_SESSION['student_id'] ?? 0;
$semester = $_SESSION['semester_id'] ?? 0;

// If $_SESSION['student_id'] exists and is not null, assign its value to $roll.
// Otherwise, assign 0 to $roll and same for sem.
// ?? - null coalescing operator in PHP.

/* The ?? 0 ensures that if someone tries to access the dashboard without logging in, 
 $roll and $semester default to 0 → redirect back to login. 
*/

if($roll == 0 || $semester == 0){
    header("Location: student_login.html");
    exit();
}

// Fetch student information
$student_query = "SELECT student_name, semester_id FROM student WHERE student_id = '$roll'";
$student_result = mysqli_query($connection, $student_query);
$student = mysqli_fetch_assoc($student_result); // Give me each row’s data in an associative array form

// Fetch result with subject name
$stmt = $connection->prepare("
SELECT s.subject_name , r.marks_obtained, r.total_marks
FROM result r
JOIN subject s ON r.subject_id = s.subject_id 
WHERE r.student_id = ? AND r.semester_id = ?; 
");
// connecting the subject table to the result table wheir their subject_id values are same
// Show only the results where student_id = whatever is inside $roll, and semester_id = whatever is inside $semester
$stmt->bind_param("si", $roll, $semester);
$stmt->execute();
$result_data = $stmt->get_result();

// Calculate totals
$total_marks = 0;
$obtained_marks = 0;
$total_subjects = mysqli_num_rows($result_data); // How many rows?
$results = [];

while($row = mysqli_fetch_assoc($result_data)){
    $results[] = $row;
    $total_marks += $row['total_marks'];
    $obtained_marks += $row['marks_obtained'];
}

$percentage = $total_marks > 0? ($obtained_marks/ $total_marks) *100 :0; 

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Result Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
       @media print {
    body, html {
        width: 100%;
        height: auto;
        font-size: 18px; /* increase font size */
    }

    .container, .card {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0;
        margin: 0;
        box-shadow: none;
        border: none;
        background-color: white;
    }

    table {
        width: 100% !important;
        font-size: 18px;
        border-collapse: collapse;
    }

    th, td {
        padding: 14px;
        border: 1px solid #000;
    }

    th {
        font-size: 20px;
    }

    .print-btn, .btn-danger, .navbar, .footer {
        display: none !important; /* hide buttons & navs */
    }
}

    </style>
    </head>
<body>
<body class="d-flex flex-column align-items-center py-5">

  <div class="container col-md-8">
    <div class="card p-4">
      <h2 class="text-center mb-4">Student Result Details</h2>
      <div class="text-end mb-3">
        <a href="student_logout.php" class="btn btn-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>


      <div class="mb-3">
        <p><strong>Student Name:</strong> <?= $student['student_name']; ?></p>
        <p><strong>Student Roll ID:</strong> <?= $roll; ?></p>
        <p><strong>Student Class:</strong> <?= $semester; ?> Semester</p>
      </div>

      <table class="table  table-primary table-bordered text-center">
        <thead>
          <tr>
            <th>S.N. </th>
            <th>Subject</th>
            <th>Marks Obtained</th>
            <th>Total Marks</th>
          </tr>
        </thead>
        <tbody class = "table-group-divider">
          <?php
          $count = 1;
          foreach($results as $r){
            echo "<tr>
            <td>{$count}</td>
            <td>{$r['subject_name']}</td>
            <td>{$r['marks_obtained']}</td>
            <td>{$r['total_marks']}</td>
            </tr>";
            $count++;
          }
          ?>
        </tbody>
        <tfoot class="table-primary">
          <tr class="summary">
            <td colspan="2" class="text-end"><b>Total Marks</b></td>
            <td colspan="2"><b><?= $obtained_marks ?> out of <?= $total_marks ?></b></td>
          </tr>
          <tr class="summary">
            <td colspan="2" class="text-end"><b>Percentage</b></td>
            <td colspan="2"><b><?= round($percentage, 2) ?>%</b></td>
          </tr>
        </tfoot>
      </table>

      <div class="text-center mt-3">
        <button class = "btn btn-outline-primary print-btn" onclick = "window.print()" style = "display-none">
          <i class = "bi bi-printer"></i>Print
        </button>
    </div>
    </div>
  </div>
  

 


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
</script>
</body>

</html>