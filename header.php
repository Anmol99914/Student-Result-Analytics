<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Result Analytics - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .content {
      flex: 1;
    }
    footer {
      background-color: #f8f9fa;
      text-align: center;
      padding: 10px 0;
      border-top: 1px solid #ddd;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar" style="background-color: #e3f2fd;" data-bs-theme="light">
    <div class="container-fluid">
      <a class="navbar-brand">Student Result Analytics | Admin</a>
      <form class="d-flex" role="search" action="logout.php">
        <button class="btn btn-outline-success" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
      </form>
    </div>
  </nav>

  <div class="d-flex flex-grow-1 content">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-3" style="width: 250px; min-height: calc(100vh - 56px);">
      <h5>SRA | Admin</h5>
      <ul class="nav flex-column mt-4">
      <li class="nav-item">
            <a href="admin_main_page.php" class="nav-link text-white"><i class="bi bi-house"></i> Home</a>
          </li>
        <li class="nav-item">
          <a href="dashboard.php" class="nav-link text-white"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </li>
        <li class="nav-item">
          <a href="student_classes.php" class="nav-link text-white"><i class="bi bi-building"></i> Student Classes</a>
        </li>
        <li class="nav-item">
          <a href="subjects.php" class="nav-link text-white"><i class="bi bi-book"></i> Subjects</a>
        </li>
        <li class="nav-item">
          <a href="students.php" class="nav-link text-white"><i class="bi bi-people"></i> Students</a>
        </li>
        <li class="nav-item">
          <a href="results.php" class="nav-link text-white"><i class="bi bi-trophy"></i> Result</a>
        </li>
      </ul>
    </div>

    <!-- Main content starts -->
    <div class="flex-grow-1 p-4">
