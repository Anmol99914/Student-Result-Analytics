<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header('Location: admin_login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_code = strtoupper(trim($_POST['faculty_code']));
    $faculty_name = trim($_POST['faculty_name']);
        
    $sql = "INSERT INTO faculty (faculty_code, faculty_name, status) VALUES (?, ?, 'active')";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ss", $faculty_code, $faculty_name);
    
    if ($stmt->execute()) {
        $faculty_id = $stmt->insert_id;
        $success = "Faculty added successfully! ID: " . $faculty_id;
    } else {
        $error = "Error: " . $connection->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Faculty - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Add New Faculty</h2>
            <a href="admin_main_page.php" class="btn btn-secondary">Back</a>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Faculty Code</label>
                        <input type="text" name="faculty_code" class="form-control" 
                               required placeholder="e.g., BIT" maxlength="10">
                        <small class="text-muted">Example: BIT, BSW, BBA</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Faculty Name</label>
                        <input type="text" name="faculty_name" class="form-control" 
                               required placeholder="e.g., Bachelor of Information Technology">
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Add Faculty</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Show existing faculties -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Existing Faculties</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $connection->query("SELECT * FROM faculty ORDER BY faculty_id");
                        while($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['faculty_id']; ?></td>
                            <td><strong><?php echo $row['faculty_code']; ?></strong></td>
                            <td><?php echo $row['faculty_name']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>