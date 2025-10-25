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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
      body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }
      .content-wrapper {
        flex: 1;
        display: flex;
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
          <button class="btn btn-outline-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
        </form>
      </div>
    </nav>

    <!-- Sidebar + Main Content -->
    <div class="content-wrapper">
      <!-- Sidebar -->
      <div class="bg-dark text-white p-3" style="width: 250px; min-height: calc(100vh - 56px);">
        <h5>SRA | Admin</h5>
        <ul class="nav flex-column mt-4">
          <li class="nav-item">
            <a href="home.php" class="nav-link text-white ajax-link"><i class="bi bi-house"></i> Home</a>
          </li>
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white ajax-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
          </li>
          <li class="nav-item">
            <a href="student_classes.php" class="nav-link text-white ajax-link"><i class="bi bi-building"></i> Student Classes</a>
          </li>
          <li class="nav-item">
            <a href="subjects.php" class="nav-link text-white ajax-link"><i class="bi bi-book"></i> Subjects</a>
          </li>
          <li class="nav-item">
            <a href="students.php" class="nav-link text-white ajax-link"><i class="bi bi-people"></i> Students</a>
          </li>
          <li class="nav-item">
            <a href="results.php" class="nav-link text-white ajax-link"><i class="bi bi-trophy"></i> Result</a>
          </li>
        </ul>
      </div>

      <!-- ✅ This is where AJAX content will be loaded -->
  <div id="main-content" class="flex-grow-1 p-4">
    <h1>Welcome, Admin!</h1>
    <p>Select an option from the sidebar to get started.</p>
  </div>
  </div>

    <!-- Footer -->
    <!-- <footer>
      <p class="mb-0">© 2025 Student Result Analytics | Admin Panel</p>
    </footer> -->
    <?php
  include 'footer.php';
    ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
//  Function to load a page into the main content
function loadPage(url) {
  const mainContent = document.getElementById('main-content');

  fetch(url) // Fetch the page content
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not OK');
      }
      return response.text(); // Convert response to text
    })
    .then(data => {
      mainContent.innerHTML = data; // Put content in main area
    })
    .catch(error => {
      mainContent.innerHTML = "<p class='text-danger'>Error loading page.</p>";
      console.error('Fetch error:', error);
    });
}

// Function to handle sidebar link clicks
function setupSidebarLinks() {
  const links = document.querySelectorAll('.ajax-link');

  links.forEach(link => {
    link.addEventListener('click', function(event) {
      event.preventDefault(); // Prevent default page reload

      // Highlight active link
      links.forEach(l => l.classList.remove('active'));
      this.classList.add('active');

      // Load the page via AJAX
      const url = this.getAttribute('href');
      loadPage(url);
    });
  });
}

// Run setup when page loads
document.addEventListener('DOMContentLoaded', function() {
  setupSidebarLinks();

  // Automatically load Home page (your default content)
  loadPage('home.php');

  // If you want the “Home” link to show active style by default:
  document.querySelector('.ajax-link[href="home.php"]').classList.add('active');

  
});
</script>
</body>
</html>
