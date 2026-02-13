<?php
// File: PHP_Files/student/includes/header.php
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title ?? 'Student Result Analytics | Student'; ?></title>
    
    <!-- Bootstrap 5 -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
      -->
    <link rel="stylesheet" href="/Student_Result_Analytics/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Student Common CSS -->
    <link rel="stylesheet" href="../css/dashboard.css">
    
    <!-- Page-specific CSS -->
    <?php if(isset($page_css)): ?>
        <link rel="stylesheet" href="../css/<?php echo $page_css; ?>.css">
    <?php endif; ?>
    
    <!-- jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script src="/Student_Result_Analytics/js/jquery.min.js"></script>

    
    <style>
        /* Inline styles for critical layout */
        body, html {
            height: 100%;
        }
        .ajax-link.active {
            background-color: #0d6efd;
            color: white !important;
            border-radius: 5px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" onload="noBack();">