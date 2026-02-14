<?php
session_start();
require_once '../../../config.php';
// Also add MySQL query that doesn't use cache
$connection->query("SET SESSION query_cache_type = OFF");

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$subject_name = isset($_GET['subject_name']) ? $_GET['subject_name'] : 'Subject';

if (!$class_id || !$subject_id) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit();
}

// Get subject details
$subject_sql = "SELECT * FROM subject WHERE subject_id = ?";
$subject_stmt = $connection->prepare($subject_sql);
$subject_stmt->bind_param("i", $subject_id);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();
$subject_data = $subject_result->fetch_assoc();

if (!$subject_data) {
    echo '<div class="alert alert-danger">Subject not found</div>';
    exit();
}

// Get class details
$class_sql = "SELECT * FROM class WHERE class_id = ?";
$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();
$class_data = $class_result->fetch_assoc();

// Get students in this class
$students_sql = "SELECT student_id, student_name, class_id FROM student WHERE class_id = ? ORDER BY student_id";
$students_stmt = $connection->prepare($students_sql);
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();

if ($students_result->num_rows === 0) {
    echo '<div class="alert alert-warning">No students found in this class</div>';
    exit();
}

// Check for existing marks
// $existing_marks = [];
// $marks_sql = "SELECT student_id, marks_obtained, total_marks, percentage, grade, verification_status 
//               FROM result 
//               WHERE subject_id = ? AND entered_by_teacher_id = ? 
//               AND student_id IN (SELECT student_id FROM student WHERE class_id = ?)";

// Get existing marks for THIS SUBJECT and THIS CLASS only - NO TEACHER FILTER!
$marks_sql = "SELECT 
                student_id,
                marks_obtained,
                grade,
                verification_status
              FROM result 
              WHERE subject_id = ? 
              AND class_id = ?
              ORDER BY student_id";

$marks_stmt = $connection->prepare($marks_sql);
$marks_stmt->bind_param("ii", $subject_id, $class_id);
$marks_stmt->execute();
$marks_result = $marks_stmt->get_result();

$existing_marks = [];
while ($row = $marks_result->fetch_assoc()) {
    $existing_marks[$row['student_id']] = $row;
}


?>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Enter Marks</h5>
            <small class="opacity-75">
                <span id="save-counter">Ready to save</span>
                • <?php echo htmlspecialchars($subject_name); ?>
            </small>
        </div>
        <div>
            <button class="btn btn-sm btn-light me-2" onclick="ResultsMarks.viewInstructions()">
                <i class="bi bi-info-circle"></i>
            </button>
            <button class="btn btn-sm btn-light" onclick="ResultsMarks.goBackToSubjects(); return false;">
                <i class="bi bi-arrow-left"></i> Back
            </button>
        </div>
    </div>

    <div class="card-body">
        <!-- Instructions -->
        <div class="alert alert-info mb-4">
            <h6><i class="bi bi-info-circle"></i> Instructions:</h6>
            <ul class="mb-0">
                <li>Enter marks for each student (0-100)</li>
                <li>Grade will be calculated automatically</li>
                <li>Click "Save Marks" to submit for verification</li>
                <li>Status colors: <span class="badge bg-warning">Pending</span> <span class="badge bg-success">Verified</span> <span class="badge bg-danger">Rejected</span></li>
            </ul>
        </div>

        <!-- Marks Container -->
        <div id="marksContainer">
            <input type="hidden" id="currentClassId" value="<?php echo $class_id; ?>">
            <input type="hidden" id="currentSubjectId" value="<?php echo $subject_id; ?>">
            <input type="hidden" id="currentSubjectName" value="<?php echo htmlspecialchars($subject_name); ?>">
            <input type="hidden" id="currentTeacherId" value="<?php echo $_SESSION['teacher_id']; ?>">
            
            <!-- Navigation data -->
            <input type="hidden" id="currentFaculty" value="<?php echo htmlspecialchars($class_data['faculty'] ?? ''); ?>">
            <input type="hidden" id="currentSemester" value="<?php echo $class_data['semester'] ?? 0; ?>">
            

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th width="150">Marks (0-100)</th>
                            <th width="100">Grade</th>
                            <th width="120">Status</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    $counter = 1;
    while ($student = $students_result->fetch_assoc()):
        $existing = $existing_marks[$student['student_id']] ?? null;
        $is_verified = ($existing && $existing['verification_status'] == 'verified');
    ?>
    <tr>
        <td><?php echo $counter++; ?></td>
        <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
        <td><?php echo htmlspecialchars($student['student_name']); ?></td>
        
        <!-- Marks input -->
        <td>
            <div class="input-group">
                <input type="number" 
                       class="form-control marks-input <?php echo $is_verified ? 'bg-light text-muted' : ''; ?>" 
                       id="marks-<?php echo $student['student_id']; ?>"
                       data-student-id="<?php echo $student['student_id']; ?>"
                       value="<?php echo $existing['marks_obtained'] ?? ''; ?>" 
                       min="0" max="100" 
                       step="0.5"
                       <?php echo $is_verified ? 'readonly disabled' : ''; ?>
                       <?php echo $is_verified ? 'title="✓ Verified marks - Cannot edit"' : ''; ?>>
                <span class="input-group-text <?php echo $is_verified ? 'bg-light text-muted' : ''; ?>">/100</span>
            </div>
            <?php if ($is_verified): ?>
                <small class="text-success">
                    <i class="bi bi-check-circle"></i> Verified - Locked
                </small>
            <?php else: ?>
                <small class="text-muted">Enter marks (0-100)</small>
            <?php endif; ?>
        </td>
        
        <!-- Grade display -->
        <td>
            <?php if ($existing): ?>
                <span class="badge grade-badge grade-<?php echo str_replace('+', 'plus', $existing['grade']); ?>">
                    <?php echo $existing['grade']; ?>
                </span>
            <?php else: ?>
                <span class="badge bg-secondary" id="grade-<?php echo $student['student_id']; ?>">
                    --
                </span>
            <?php endif; ?>
        </td>
                <!-- Status -->
                <td>
                    <?php if ($existing): ?>
                        <?php
                        $status_class = '';
                        $status_text = '';
                        $rejection_reason = '';
                        
                        if ($existing['verification_status'] == 'verified') {
                            $status_class = 'bg-success';
                            $status_text = 'Verified';
                        } elseif ($existing['verification_status'] == 'rejected') {
                            $status_class = 'bg-danger';
                            $status_text = 'Rejected';
                            
                            // Fetch rejection reason from comments column
                            $reason_sql = "SELECT comments FROM result WHERE student_id = ? AND subject_id = ? AND verification_status = 'rejected' ORDER BY updated_at DESC LIMIT 1";
                            $reason_stmt = $connection->prepare($reason_sql);
                            $reason_stmt->bind_param("si", $student['student_id'], $subject_id);
                            $reason_stmt->execute();
                            $reason_result = $reason_stmt->get_result();
                            if ($reason_row = $reason_result->fetch_assoc()) {
                                $rejection_reason = $reason_row['comments'];
                                // Debug output in HTML comments
                                echo "<!-- Reason for {$student['student_id']}: " . htmlspecialchars($rejection_reason ?? '') . " -->";
                            }
                        } else {
                            $status_class = 'bg-warning';
                            $status_text = 'Pending';
                        }
                        ?>
                        <div>
                            <span class="badge <?php echo $status_class; ?> mb-1">
                                <?php echo $status_text; ?>
                            </span>
                            <?php if ($existing['verification_status'] == 'rejected'): ?>
    <button type="button" class="btn btn-link btn-sm p-0 ms-1" 
            onclick="alert('Rejection reason: <?php echo addslashes($rejection_reason ?? 'No reason provided'); ?>')">
        <i class="bi bi-info-circle text-danger"></i> Why?
    </button>
<?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="badge bg-light text-dark">Not Entered</span>
                    <?php endif; ?>
                </td>
    </tr>
    <?php endwhile; ?>
</tbody>
                </table>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="mt-4 border-top pt-3">
            <div class="d-flex justify-content-between">
                <div>
                    <!-- Cancel button -->
                    <button type="button" class="btn btn-secondary" 
                            onclick="ResultsSystem.loadClassSubjects(<?php echo $class_id; ?>, '<?php echo htmlspecialchars($class_data['faculty'] ?? ''); ?>', <?php echo $class_data['semester'] ?? 0; ?>); return false;">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                </div>
                <div>
                    <!-- Save button - ALWAYS enabled -->
                    <button type="button" class="btn btn-success" id="saveBtn" onclick="ResultsMarks.saveAllMarks(); return false;">
                        <i class="bi bi-check-circle"></i> Save All Marks
                    </button>
                    <small class="text-muted ms-2">
                        <i class="bi bi-info-circle"></i> Verified marks (✓) cannot be edited and will be skipped
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS for grades -->
<style>
.grade-badge {
    font-size: 0.85rem;
    padding: 4px 10px;
    border-radius: 4px;
}
.grade-Aplus { background-color: #28a745; color: white; }
.grade-A { background-color: #20c997; color: white; }
.grade-Bplus { background-color: #ffc107; color: black; }
.grade-B { background-color: #fd7e14; color: white; }
.grade-Cplus { background-color: #6f42c1; color: white; }
.grade-C { background-color: #e83e8c; color: white; }
.grade-F { background-color: #dc3545; color: white; }

/* Toast animations */
.toast {
    animation: slideInRight 0.3s ease-out;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast.fade-out {
    animation: slideOutRight 0.3s ease-in forwards;
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Status badges animation */
.badge.bg-warning {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>

