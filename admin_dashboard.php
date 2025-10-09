<?php
// This ensures only logged-in admins can see the dashboard.
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    header("Location: admin_login.html");
    exit();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Result Analytics - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
  <body>
  <nav class="navbar" style="background-color: #e3f2fd;" data-bs-theme="light">
    <div class="container-fluid">
    <a class="navbar-brand">Student Result Analytics | Admin </a>
    <form class="d-flex" role="search" action = "logout.php">
      <button class="btn btn-outline-success" type="submit"><i class="bi bi-box-arrow-right"></i>Logout</button>
    </form>
  </div>
</nav>
<div class="d-flex">   <!-- display flex  -->
  <!-- Sidebar -->
  <div class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
    <h5>SRA | Admin</h5>
    <ul class="nav flex-column mt-4">
      <li class="nav-item">
        <a href="#" class="nav-link text-white"><i class="bi bi-speedometer2"></i> Dashboard</a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link text-white"><i class="bi bi-building"></i> Student Classes</a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link text-white"><i class="bi bi-book"></i> Subjects</a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link text-white"><i class="bi bi-people"></i> Students</a>
      </li>
      <li class = "nav-item">
        <a href="#" class="nav-link text-white"><i class="bi bi-trophy"></i> Result</a>
      </li>
    </ul>
  </div>

  <!-- Main content -->
  <div class="flex-grow-1 p-3">
    <h1>Dashboard</h1>
    <p>Content goes here...</p>
  </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>