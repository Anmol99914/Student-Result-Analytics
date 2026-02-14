<!-- teacher_home.php -->
<?php
session_start();
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
include('../../config.php');

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true) {
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// FIXED: Get teacher's assigned classes count
$class_query = "SELECT COUNT(DISTINCT class_id) as class_count FROM teacher_subject_assignment WHERE teacher_id = ?";
$stmt = $connection->prepare($class_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$class_count = $result->fetch_assoc()['class_count'] ?? 0;
$stmt->close();

// FIXED: Get teacher's students count (students in classes teacher teaches)
$student_query = "SELECT COUNT(DISTINCT s.student_id) as student_count 
                 FROM student s 
                 JOIN class c ON s.class_id = c.class_id 
                 JOIN teacher_subject_assignment tsa ON c.class_id = tsa.class_id 
                 WHERE tsa.teacher_id = ?";
$stmt2 = $connection->prepare($student_query);
$stmt2->bind_param("i", $teacher_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$student_count = $result2->fetch_assoc()['student_count'] ?? 0;
$stmt2->close();
?>

<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?> 👨‍🏫</h2>
        <p class="text-muted">
            Manage your classes, students, and enter results from this dashboard.
        </p>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <!-- My Classes -->
        <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-table display-4 text-primary"></i>
                    <h3 class="mt-3"><?php echo $class_count; ?></h3>
                    <h5 class="card-title">My Classes</h5>
                    <p class="card-text">Classes assigned to you</p>
                    <button class="btn btn-outline-primary" onclick="loadMyClasses()">
                        View Classes
                    </button>
                </div>
            </div>
        </div>

        <!-- My Students -->
        <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 text-success"></i>
                    <h3 class="mt-3"><?php echo $student_count; ?></h3>
                    <h5 class="card-title">My Students</h5>
                    <p class="card-text">Students in your classes</p>
                    <button class="btn btn-outline-success" onclick="loadMyStudents()">
                        View Students
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-lightning display-4 text-info"></i>
                    <h5 class="card-title mt-3">Quick Actions</h5>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-success" onclick="loadAddStudentForm()">
                            <i class="bi bi-person-plus"></i> Add New Student
                        </button>
                        <button class="btn btn-warning" onclick="loadAddResultForm()">
                            <i class="bi bi-trophy"></i> Enter Results
                        </button>
                        <!-- BULK UPLOAD BUTTON - ADDED HERE -->
                        <button class="btn btn-info" onclick="showBulkUploadModal()">
                            <i class="bi bi-cloud-upload"></i> Bulk Upload Marks
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the bulk upload modal (will be loaded when needed) -->
    <?php include 'bulk_upload_modal.php'; ?>

    <!-- Recent Activity -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
        </div>
        <div class="card-body">
            <?php
            // Get recent results entered by this teacher
            $recent_query = "SELECT r.result_id, s.subject_name, stu.student_name, 
                                    r.marks_obtained, r.verification_status, r.updated_at
                             FROM result r
                             JOIN subject s ON r.subject_id = s.subject_id
                             JOIN student stu ON r.student_id = stu.student_id
                             WHERE r.entered_by_teacher_id = ?
                             ORDER BY r.updated_at DESC
                             LIMIT 5";
            $recent_stmt = $connection->prepare($recent_query);
            $recent_stmt->bind_param("i", $teacher_id);
            $recent_stmt->execute();
            $recent_results = $recent_stmt->get_result();
            
            if ($recent_results->num_rows > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_results->fetch_assoc()): 
                            $status_class = '';
                            if($row['verification_status'] == 'verified') $status_class = 'success';
                            elseif($row['verification_status'] == 'rejected') $status_class = 'danger';
                            else $status_class = 'warning';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td><?php echo $row['marks_obtained']; ?>/100</td>
                            <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $row['verification_status']; ?></span></td>
                            <td><?php echo date('d M Y', strtotime($row['updated_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-3">
                No recent activity. Start by entering student results.
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for the modal function -->
<script>
function showBulkUploadModal() {
    // First ensure the modal is loaded
    fetch('bulk_upload_modal.php')
        .then(response => response.text())
        .then(html => {
            // Create temporary container
            const temp = document.createElement('div');
            temp.innerHTML = html;
            
            // Add modal to body
            document.body.appendChild(temp.firstElementChild);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('bulkUploadModal'));
            modal.show();
            
            // Clean up when hidden
            document.getElementById('bulkUploadModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        })
        .catch(error => {
            console.error('Error loading bulk upload modal:', error);
            alert('Could not load bulk upload feature. Please try again.');
        });
}

// Make sure it's globally available
window.showBulkUploadModal = showBulkUploadModal;
</script>