<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$result_id = isset($_POST['result_id']) ? intval($_POST['result_id']) : 0;

if (!$result_id) {
    echo '<div class="alert alert-danger">Invalid result ID</div>';
    exit();
}

// Get result details
$sql = "SELECT r.*, 
               s.subject_name,
               stu.student_name,
               stu.email as student_email,
               c.faculty,
               c.semester,
               t.name as teacher_name,
               t.email as teacher_email
        FROM result r
        JOIN subject s ON r.subject_id = s.subject_id
        JOIN student stu ON r.student_id = stu.student_id
        JOIN class c ON stu.class_id = c.class_id
        JOIN teacher t ON r.entered_by_teacher_id = t.teacher_id
        WHERE r.result_id = ?";
        
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $result_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo '<div class="alert alert-danger">Result not found</div>';
    exit();
}
?>

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
                <i class="bi bi-info-circle me-2"></i> Result Details
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Student Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="120">Student ID:</th>
                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($row['student_email']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Class Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="120">Faculty:</th>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($row['faculty']); ?></span></td>
                        </tr>
                        <tr>
                            <th>Semester:</th>
                            <td><span class="badge bg-info">Sem <?php echo $row['semester']; ?></span></td>
                        </tr>
                        <tr>
                            <th>Subject:</th>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <h6>Marks Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="120">Marks Obtained:</th>
                            <td>
                                <span class="badge bg-primary fs-6">
                                    <?php echo $row['marks_obtained']; ?>/<?php echo $row['total_marks']; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Percentage:</th>
                            <td><span class="badge bg-info"><?php echo number_format($row['percentage'], 2); ?>%</span></td>
                        </tr>
                        <tr>
                            <th>Grade:</th>
                            <td><span class="badge bg-success fs-6"><?php echo $row['grade']; ?></span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Teacher Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <th width="120">Teacher:</th>
                            <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($row['teacher_email']); ?></td>
                        </tr>
                        <tr>
                            <th>Submitted:</th>
                            <td><?php echo date('M d, Y H:i', strtotime($row['published_date'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <h6>Verification Status</h6>
                    <div class="alert alert-<?php echo $row['verification_status'] == 'pending' ? 'warning' : ($row['verification_status'] == 'verified' ? 'success' : 'danger'); ?>">
                        <i class="bi bi-shield-check me-2"></i>
                        Current Status: <strong><?php echo ucfirst($row['verification_status']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <?php if($row['verification_status'] == 'pending'): ?>
            <button type="button" class="btn btn-success" 
                    onclick="verifyResult(<?php echo $row['result_id']; ?>)">
                <i class="bi bi-check"></i> Verify
            </button>
            <button type="button" class="btn btn-danger" 
                    onclick="rejectResult(<?php echo $row['result_id']; ?>)">
                <i class="bi bi-x"></i> Reject
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>