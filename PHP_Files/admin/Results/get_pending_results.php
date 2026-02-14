<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$faculty = isset($_POST['faculty']) ? $connection->real_escape_string($_POST['faculty']) : '';
$semester = isset($_POST['semester']) ? intval($_POST['semester']) : 0;

// Build query with filters
$sql = "SELECT r.*, 
               s.subject_name,
               stu.student_name,
               c.faculty,
               c.semester,
               t.name as teacher_name,
               t.email as teacher_email
        FROM result r
        JOIN subject s ON r.subject_id = s.subject_id
        JOIN student stu ON r.student_id = stu.student_id
        JOIN class c ON stu.class_id = c.class_id
        JOIN teacher t ON r.entered_by_teacher_id = t.teacher_id
        WHERE r.verification_status = 'pending'";
        
if (!empty($faculty)) {
    $sql .= " AND c.faculty = '$faculty'";
}
if ($semester > 0) {
    $sql .= " AND c.semester = $semester";
}

$sql .= " ORDER BY r.published_date DESC, c.faculty, c.semester";

$result = $connection->query($sql);

if (!$result || $result->num_rows === 0) {
    echo '<div class="text-center py-5">
            <div class="alert alert-success">
                <i class="bi bi-check-circle display-4"></i>
                <h4 class="mt-3">No Pending Results!</h4>
                <p>All results have been verified. Great work!</p>
            </div>
          </div>';
    exit();
}
?>

<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Submitted By</th>
                <th>Class</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr class="result-row" data-result-id="<?php echo $row['result_id']; ?>">
                <td>
                    <div class="fw-bold"><?php echo htmlspecialchars($row['student_id']); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($row['student_name']); ?></small>
                </td>
                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                <td>
                    <span class="badge bg-primary">
                        <?php echo $row['marks_obtained']; ?>/<?php echo $row['total_marks']; ?>
                    </span>
                </td>
                <td>
                    <span class="badge bg-info"><?php echo $row['grade']; ?></span>
                </td>
                <td>
                    <div><?php echo htmlspecialchars($row['teacher_name']); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($row['teacher_email']); ?></small>
                </td>
                <td>
                    <span class="badge bg-secondary">
                        <?php echo htmlspecialchars($row['faculty']); ?> - Sem <?php echo $row['semester']; ?>
                    </span>
                </td>
                <td>
                    <small><?php echo date('M d, Y', strtotime($row['published_date'])); ?></small>
                </td>
                <td class="verification-actions">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-view" 
                                data-result-id="<?php echo $row['result_id']; ?>"
                                onclick="viewResultDetails(<?php echo $row['result_id']; ?>); return false;"
                                title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-success btn-verify"
                                data-result-id="<?php echo $row['result_id']; ?>"
                                onclick="verifyResult(<?php echo $row['result_id']; ?>); return false;"
                                title="Verify">
                            <i class="bi bi-check"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-reject"
                                data-result-id="<?php echo $row['result_id']; ?>"
                                onclick="rejectResult(<?php echo $row['result_id']; ?>); return false;"
                                title="Reject">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<!-- Your existing table HTML... -->

<!-- ADD THIS AT THE BOTTOM OF THE FILE -->
<script>
// ===== VERIFICATION FUNCTIONS - LOADED WITH CONTENT =====
// These functions are available immediately because they're in the same file

console.log('✅ Verification functions loaded with table');

// View result details
function viewResultDetails(resultId) {
    console.log('Viewing:', resultId);
    
    fetch('Results/get_result_details.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'result_id=' + resultId
    })
    .then(response => response.text())
    .then(html => {
        // Create and show modal
        const modalDiv = document.createElement('div');
        modalDiv.innerHTML = html;
        document.body.appendChild(modalDiv);
        
        const modal = new bootstrap.Modal(modalDiv.querySelector('.modal'));
        modal.show();
        
        // Clean up when modal closes
        modalDiv.addEventListener('hidden.bs.modal', function() {
            modalDiv.remove();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading details');
    });
}

// Verify result
function verifyResult(resultId) {
    console.log('Verifying:', resultId);
    
    if (confirm('Verify this result? Once verified, teacher cannot edit it.')) {
        // Show loading on button
        const btn = document.querySelector(`[onclick*="verifyResult(${resultId})"]`);
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btn.disabled = true;
        
        fetch('Results/update_verification.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'result_id=' + resultId + '&status=verified'
        })
        .then(response => response.json())
        .then(data => {
            // Restore button
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            if (data.success) {
                alert('✅ Result verified successfully!');
                // Remove the row
                const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
                if (row) {
                    row.remove();
                    
                    // Update counts
                    updateCounters();
                    
                    // Show message if no results left
                    if (document.querySelectorAll('#pending-results-table tbody tr').length === 0) {
                        document.querySelector('#pending-results-table tbody').innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-check-circle display-4 text-success"></i>
                                    <h5 class="mt-3">All pending results verified!</h5>
                                </td>
                            </tr>
                        `;
                    }
                }
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            alert('Failed to verify result');
        });
    }
}

// Reject result
function rejectResult(resultId) {
    console.log('Rejecting:', resultId);
    
    const reason = prompt('Enter rejection reason (optional):');
    
    // If user cancels (null), do nothing
    if (reason === null) {
        return;
    }
    
    // Show loading on button
    const btn = document.querySelector(`[onclick*="rejectResult(${resultId})"]`);
    if (!btn) return;
    
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;
    
    fetch('Results/update_verification.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'result_id=' + resultId + '&status=rejected&reason=' + encodeURIComponent(reason || '') // Send empty string if no reason
    })
    .then(response => response.json())
    .then(data => {
        // Restore button
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (data.success) {
            alert('Result rejected!');
            // Remove the row
            const row = document.querySelector(`tr[data-result-id="${resultId}"]`);
            if (row) row.remove();
            
            // Update counts
            updateCounters();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        alert('Failed to reject result');
    });
}

// Update counters (pending count, stats)
function updateCounters() {
    console.log('Updating counters...');
    
    // Update pending count badge
    fetch('Results/get_pending_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('pending-count');
                if (badge) {
                    badge.textContent = data.count;
                    badge.className = `badge ${data.count > 0 ? 'bg-danger' : 'bg-success'}`;
                }
                
                // Update pending display on page
                const pendingDisplay = document.getElementById('pending-count-display');
                if (pendingDisplay) {
                    pendingDisplay.textContent = data.count;
                }
            }
        });
    
    // Update stats
    fetch('Results/get_verification_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ['verified-today', 'rejected-today', 'total-verified'].forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        const key = id.replace('-', '_');
                        element.textContent = data[key] || 0;
                    }
                });
            }
        });
}

// Make functions available globally
window.viewResultDetails = viewResultDetails;
window.verifyResult = verifyResult;
window.rejectResult = rejectResult;
window.updateCounters = updateCounters;

console.log('All functions ready:', {
    viewResultDetails: typeof viewResultDetails,
    verifyResult: typeof verifyResult,
    rejectResult: typeof rejectResult
});
</script>