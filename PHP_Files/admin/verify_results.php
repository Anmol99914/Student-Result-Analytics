<?php
session_start();
require_once '../config.php';

// Admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Verification - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
        .status-badge { font-size: 0.75rem; }
        .pending { background-color: #ffc107; }
        .verified { background-color: #198754; }
        .rejected { background-color: #dc3545; }
        .clickable-row { cursor: pointer; }
        .clickable-row:hover { background-color: #f8f9fa; }
        #resultsChart { height: 300px; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><i class="bi bi-shield-check text-primary"></i> Result Verification</h1>
            <div>
                <button class="btn btn-outline-secondary" onclick="refreshStats()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <h5 class="text-primary"><i class="bi bi-hourglass-split"></i> Pending</h5>
                        <h2 id="pendingCount" class="display-6">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h5 class="text-success"><i class="bi bi-check-circle"></i> Verified</h5>
                        <h2 id="verifiedCount" class="display-6">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h5 class="text-danger"><i class="bi bi-x-circle"></i> Rejected</h5>
                        <h2 id="rejectedCount" class="display-6">0</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h5 class="text-info"><i class="bi bi-graph-up"></i> Total</h5>
                        <h2 id="totalCount" class="display-6">0</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Results Overview</h5>
            </div>
            <div class="card-body">
                <div id="resultsChart"></div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Results</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus" onchange="loadResults()">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterFaculty" onchange="loadResults()">
                            <option value="">All Faculty</option>
                            <option value="BCA">BCA</option>
                            <option value="BBM">BBM</option>
                            <option value="BIM">BIM</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchStudent" 
                               placeholder="Search student..." onkeyup="loadResults()">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="loadResults()">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Results for Verification</h5>
                <span class="badge bg-primary" id="tableCount">0 results</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="resultsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Faculty</th>
                                <th>Teacher</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                            <!-- Results will be loaded here -->
                        </tbody>
                    </table>
                </div>
                <div id="loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading results...</p>
                </div>
                <div id="noResults" class="text-center py-5" style="display:none;">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <h5 class="mt-3">No results found</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal fade" id="verifyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="resultDetails">
                        <!-- Result details will be loaded here -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" id="verifyComments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="rejectResult()">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success" onclick="approveResult()">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentResultId = null;
        const modal = new bootstrap.Modal(document.getElementById('verifyModal'));

        // Load results on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadResults();
            loadChart();
        });

        // Load statistics
        function loadStats() {
            fetch('ajax/get_result_stats.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pendingCount').textContent = data.pending || 0;
                    document.getElementById('verifiedCount').textContent = data.verified || 0;
                    document.getElementById('rejectedCount').textContent = data.rejected || 0;
                    document.getElementById('totalCount').textContent = data.total || 0;
                });
        }

        // Load results table
        function loadResults() {
            const status = document.getElementById('filterStatus').value;
            const faculty = document.getElementById('filterFaculty').value;
            const search = document.getElementById('searchStudent').value;
            
            document.getElementById('loading').style.display = 'block';
            document.getElementById('resultsBody').innerHTML = '';
            document.getElementById('noResults').style.display = 'none';
            
            fetch(`ajax/get_pending_results.php?status=${status}&faculty=${faculty}&search=${search}`)
                .then(response => response.json())
                .then(results => {
                    document.getElementById('loading').style.display = 'none';
                    
                    if (results.length === 0) {
                        document.getElementById('noResults').style.display = 'block';
                        document.getElementById('tableCount').textContent = '0 results';
                        return;
                    }
                    
                    let html = '';
                    results.forEach(result => {
                        const statusBadge = getStatusBadge(result.verification_status);
                        html += `
                        <tr class="clickable-row" onclick="viewResult(${result.result_id})">
                            <td>
                                <strong>${result.student_name}</strong><br>
                                <small class="text-muted">${result.student_id}</small>
                            </td>
                            <td>${result.subject_name}</td>
                            <td>
                                <span class="badge bg-primary">${result.marks_obtained}/${result.total_marks}</span><br>
                                <small>${result.percentage}%</small>
                            </td>
                            <td><span class="badge bg-info">${result.grade}</span></td>
                            <td>${result.faculty}</td>
                            <td>${result.teacher_name}</td>
                            <td>${formatDate(result.created_at)}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewResult(${result.result_id}); event.stopPropagation();">
                                    <i class="bi bi-eye"></i> Review
                                </button>
                            </td>
                        </tr>
                        `;
                    });
                    
                    document.getElementById('resultsBody').innerHTML = html;
                    document.getElementById('tableCount').textContent = `${results.length} results`;
                });
        }

        // Load chart
        function loadChart() {
            fetch('ajax/get_chart_data.php')
                .then(response => response.json())
                .then(data => {
                    Highcharts.chart('resultsChart', {
                        chart: { type: 'pie' },
                        title: { text: 'Results by Verification Status' },
                        series: [{
                            name: 'Results',
                            colorByPoint: true,
                            data: data
                        }]
                    });
                });
        }

        // View result details
        function viewResult(resultId) {
            currentResultId = resultId;
            fetch(`ajax/get_result_details.php?id=${resultId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('resultDetails').innerHTML = html;
                    modal.show();
                });
        }

        // Approve result
        function approveResult() {
            const comments = document.getElementById('verifyComments').value;

            console.log('Approving result ID:', currentResultId); // DEBUG
            
            fetch('ajax/verify_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    result_id: currentResultId,
                    action: 'approve',
                    comments: comments
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Approve response:', data); // DEBUG
                if (data.success) {
                    alert('Result approved successfully!');
                    modal.hide();
                    console.log('Calling refreshStats...'); // DEBUG
                    
                    refreshStats();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        // Reject result
        function rejectResult() {
            const comments = document.getElementById('verifyComments').value;
            
            if (!comments.trim()) {
                alert('Please provide a reason for rejection');
                return;
            }
            
            fetch('ajax/verify_result.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    result_id: currentResultId,
                    action: 'reject',
                    comments: comments
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Result rejected!');
                    modal.hide();
                    refreshStats();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        // Refresh all data
        function refreshStats() {
            loadStats();
            loadResults();
            loadChart();
        }

        // Helper functions
        function getStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning">Pending</span>',
                'verified': '<span class="badge bg-success">Verified</span>',
                'rejected': '<span class="badge bg-danger">Rejected</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }
    </script>
</body>
</html>