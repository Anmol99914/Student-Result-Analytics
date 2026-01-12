<?php
session_start();
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
require_once '../../config.php';

// Also add MySQL query that doesn't use cache
$connection->query("SET SESSION query_cache_type = OFF");

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Get classes assigned to this teacher
    $sql = "SELECT 
                c.class_id, 
                c.faculty, 
                c.semester, 
                c.status, 
                c.created_at,
                (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count
            FROM class c
            INNER JOIN teacher_class_assignments tca ON c.class_id = tca.class_id
            WHERE tca.teacher_id = ?
            ORDER BY c.faculty, c.semester";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    
    if (empty($classes)) {
        echo '<div class="text-center py-5">
                <i class="bi bi-table display-4 text-muted mb-3"></i>
                <h5 class="text-muted">No Classes Assigned</h5>
                <p class="text-muted">You haven\'t been assigned any classes yet.</p>
                <p class="text-muted small">Contact the administrator to get classes assigned to you.</p>
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
                                <th width="120">Status</th>
                                <th width="150">Created</th>
                                <th width="150">Actions</th>
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
            
            echo '<tr>
                    <td><span class="badge bg-dark">#' . $class['class_id'] . '</span></td>
                    <td>
                        <div class="fw-bold">' . htmlspecialchars($class['faculty']) . '</div>
                    </td>
                    <td>
                        <span class="badge bg-info text-dark">
                            <i class="bi bi-calendar-week me-1"></i> Sem ' . $class['semester'] . '
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary rounded-pill me-2">
                                <i class="bi bi-people"></i>
                            </span>
                            <span class="fw-semibold">' . $class['student_count'] . ' students</span>
                        </div>
                    </td>
                    <td>' . $statusBadge . '</td>
                    <td>
                        <small class="text-muted">' . $createdDate . '</small>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewClassStudents(' . $class['class_id'] . ')" title="View Students">
                            <i class="bi bi-people"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="viewClassDetails(' . $class['class_id'] . ')" title="Class Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                  </tr>';
        }
        
        echo '</tbody></table></div></div>';
        
        // Add summary at bottom
        echo '<div class="alert alert-info mt-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle me-2 fs-4"></i>
                    <div>
                        <h6 class="mb-1">Quick Actions</h6>
                        <p class="mb-0 small">Click on action buttons to view students or class details. You can manage students from the "My Students" section.</p>
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