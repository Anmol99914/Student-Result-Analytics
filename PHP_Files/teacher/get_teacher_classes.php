<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
require_once '../../config.php';

$connection->query("SET SESSION query_cache_type = OFF");

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

try {
    $sql = "SELECT DISTINCT 
            c.class_id, 
            c.faculty, 
            c.semester, 
            c.status, 
            c.created_at,
            c.batch_year,
            (SELECT COUNT(*) FROM student WHERE class_id = c.class_id AND is_active = 1) as student_count,
            (SELECT COUNT(*) FROM teacher_subject_assignment tsa 
             WHERE tsa.teacher_id = ? 
             AND tsa.class_id = c.class_id) as subject_count
        FROM class c
        INNER JOIN teacher_subject_assignment tsa ON c.class_id = tsa.class_id
        WHERE tsa.teacher_id = ?
        ORDER BY c.faculty, c.semester";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ii", $teacher_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    
    if (empty($classes)) {
        echo '<div class="text-center py-5">
                <i class="bi bi-table display-4 text-muted mb-3"></i>
                <h5 class="text-muted">No Classes Assigned</h5>
                <p class="text-muted">You haven\'t been assigned to teach any subjects yet.</p>
                <p class="text-muted small">Contact the administrator to assign subjects to you.</p>
              </div>';
    } else {
        echo '<div class="table-container">
                <div class="table-header d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 text-primary"><i class="bi bi-info-circle me-2"></i> ' . count($classes) . ' Classes Found</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="100">Class ID</th>
                                <th>Faculty</th>
                                <th width="120">Semester</th>
                                <th width="150">Students</th>
                                <th width="150">Subjects</th>
                                <th width="120">Status</th>
                                <th width="150">Batch</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($classes as $class) {
            $statusBadge = $class['status'] === 'active' 
                ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>'
                : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Inactive</span>';
            
            $createdDate = $class['created_at'] 
                ? date('M d, Y', strtotime($class['created_at']))
                : 'N/A';
            
            $batchYear = $class['batch_year'] ?? date('Y');
            $subjectCount = $class['subject_count'] ?? 0;
            
            $faculty_name = htmlspecialchars($class['faculty']);
            $class_id_val = $class['class_id'];
            $semester_val = $class['semester'];
            $student_count_val = $class['student_count'];
            
            echo '<tr>
                    <td><span class="badge bg-dark">#' . $class_id_val . '</span></td>
                    <td>
                        <div class="fw-bold">' . $faculty_name . '</div>
                    </td>
                    <td>
                        <span class="badge bg-info text-dark">
                            <i class="bi bi-calendar-week me-1"></i> Sem ' . $semester_val . '
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary rounded-pill me-2">
                                <i class="bi bi-people"></i> ' . $student_count_val . '
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success rounded-pill me-2">
                                <i class="bi bi-book"></i> ' . $subjectCount . '
                            </span>
                            <span class="text-muted small">subjects</span>
                        </div>
                    </td>
                    <td>' . $statusBadge . '</td>
                    <td>
                        <small class="text-muted">' . $batchYear . '</small>
                    </td>
                    <td>  
                        <div class="btn-group btn-group-sm" role="group">
                            <!-- Class Details Button -->
                            <button class="btn btn-outline-success" 
                                    onclick="viewClassDetails(' . $class_id_val . ')" 
                                    title="Class Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            
                            <!-- PERFORMANCE BUTTON - FIXED -->
                            <button class="btn btn-outline-info" 
                                    onclick="loadPerformancePage(' . $class_id_val . ', \'' . addslashes($faculty_name) . ' Sem ' . $semester_val . '\')" 
                                    title="View Performance">
                                <i class="bi bi-bar-chart-fill"></i>
                            </button>
                        </div>
                    </td>
                  </tr>';
        }
        
        echo '</tbody>
                    </table>
                </div>
            </div>';
        
        echo '<div class="alert alert-info mt-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle me-2 fs-4"></i>
                    <div>
                        <h6 class="mb-1">Your Assigned Subjects</h6>
                        <p class="mb-0 small">These are the classes where you have been assigned to teach specific subjects.</p>
                    </div>
                </div>
              </div>';
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2 fs-4"></i>
                <div>
                    <h6 class="mb-1">Error Loading Classes</h6>
                    <p class="mb-0">' . htmlspecialchars($e->getMessage()) . '</p>
                    <button class="btn btn-sm btn-outline-danger mt-2" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i> Try Again
                    </button>
                </div>
            </div>
          </div>';
}
?>