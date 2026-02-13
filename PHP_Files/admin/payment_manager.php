<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get all students with their latest payment
$sql = "SELECT 
            s.student_id,
            s.student_name,
            c.faculty,
            c.semester,
            p.payment_status,
            p.amount_paid,
            p.due_amount,
            p.payment_date
        FROM student s
        LEFT JOIN class c ON s.class_id = c.class_id
        LEFT JOIN payment p ON s.student_id = p.student_id AND p.is_latest = 1
        WHERE s.is_active = 1
        ORDER BY s.student_id";
$result = $connection->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Manager - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container-fluid py-4">
        <h2 class="mb-4"><i class="bi bi-credit-card me-2"></i>Payment Status Manager</h2>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><code><?= $row['student_id'] ?></code></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= $row['faculty'] ?></td>
                            <td>Sem <?= $row['semester'] ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    $row['payment_status'] == 'Paid' ? 'success' : 
                                    ($row['payment_status'] == 'Partial' ? 'warning' : 'danger') 
                                ?>">
                                    <?= $row['payment_status'] ?? 'Unpaid' ?>
                                </span>
                            </td>
                            <td>NPR <?= number_format($row['amount_paid'] ?? 0, 2) ?></td>
                            <td>NPR <?= number_format($row['due_amount'] ?? 50000, 2) ?></td>
                            <td><small><?= $row['payment_date'] ? date('d M Y', strtotime($row['payment_date'])) : '-' ?></small></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="togglePayment('<?= $row['student_id'] ?>')">
                                    <i class="bi bi-arrow-repeat"></i> Toggle
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function togglePayment(studentId) {
        fetch('students/update_payment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({student_id: studentId})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
    </script>
</body>
</html>