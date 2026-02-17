<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header('Location: ../admin_login.php');
    exit();
}

// Get faculties for dropdown
$faculties = $connection->query("SELECT faculty_code, faculty_name FROM faculty WHERE status = 'active' ORDER BY faculty_code");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://code.highcharts.com/highcharts.js"></script>
</head>
<body>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="bi bi-bar-chart-fill me-2"></i>Student Performance Viewer</h2>
    
    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Select Student</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Faculty</label>
                    <select class="form-select" id="facultyFilter">
                        <option value="">-- Select Faculty --</option>
                        <?php while($f = $faculties->fetch_assoc()): ?>
                        <option value="<?= $f['faculty_code'] ?>"><?= $f['faculty_name'] ?> (<?= $f['faculty_code'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select class="form-select" id="semesterFilter" disabled>
                        <option value="">-- First select faculty --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Student</label>
                    <select class="form-select" id="studentFilter" disabled>
                        <option value="">-- First select semester --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance Chart Container (initially hidden) -->
    <div id="performanceContainer" style="display: none;">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0" id="studentNameHeader">Student Performance</h5>
            </div>
            <div class="card-body">
                <div id="performanceChart" style="height: 400px;"></div>
            </div>
        </div>
    </div>
    
    <!-- No data message -->
    <div id="noDataMessage" class="alert alert-info text-center" style="display: none;">
        <i class="bi bi-info-circle"></i> Select a student to view performance
    </div>
</div>

<script>
    // ===== SILENCE HARMLESS ERRORS =====
window.addEventListener('error', function(e) {
    // Ignore script errors (they're usually from extensions or cross-origin)
    if (e.message === 'Script error.' || e.message === 'Script error') {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
    return true;
}, true);

window.addEventListener('unhandledrejection', function(e) {
    // Optionally log but don't show as error
    console.debug('Promise rejection (ignored):', e.reason);
    e.preventDefault();
});

// Disable Highcharts accessibility warnings
if (typeof Highcharts !== 'undefined') {
    Highcharts.setOptions({
        accessibility: { enabled: false }
    });
}

// Filter console warnings
const originalWarn = console.warn;
console.warn = function(msg) {
    if (msg && typeof msg === 'string' && 
        (msg.includes('Highcharts') || msg.includes('accessibility'))) {
        return; // Ignore Highcharts warnings
    }
    originalWarn.apply(console, arguments);
};

// Load semesters when faculty selected
document.getElementById('facultyFilter').addEventListener('change', function() {
    const faculty = this.value;
    const semesterSelect = document.getElementById('semesterFilter');
    const studentSelect = document.getElementById('studentFilter');
    
    // Reset
    semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';
    semesterSelect.disabled = !faculty;
    studentSelect.innerHTML = '<option value="">-- First select semester --</option>';
    studentSelect.disabled = true;
    document.getElementById('performanceContainer').style.display = 'none';
    
    if (faculty) {
        // Load semesters 1-8
        for (let i = 1; i <= 8; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = 'Semester ' + i;
            semesterSelect.appendChild(option);
        }
    }
});

// Load students when semester selected
document.getElementById('semesterFilter').addEventListener('change', function() {
    const faculty = document.getElementById('facultyFilter').value;
    const semester = this.value;
    const studentSelect = document.getElementById('studentFilter');
    
    studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
    studentSelect.disabled = !semester;
    document.getElementById('performanceContainer').style.display = 'none';
    
    if (faculty && semester) {
        const url = `/Student_Result_Analytics/PHP_Files/admin/api/get_students_by_class.php?faculty=${faculty}&semester=${semester}&t=${Date.now()}`;
        console.log('Fetching students from:', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(students => {
                console.log('Students received:', students);
                if (students.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = '-- No students found --';
                    studentSelect.appendChild(option);
                } else {
                    students.forEach(s => {
                        const option = document.createElement('option');
                        option.value = s.student_id;
                        option.textContent = s.student_name + ' (' + s.student_id + ')';
                        studentSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading students:', error);
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '-- Error loading students --';
                studentSelect.appendChild(option);
            });
    }
});

// Load performance when student selected
document.getElementById('studentFilter').addEventListener('change', function() {
    const studentId = this.value;
    if (!studentId) {
        document.getElementById('performanceContainer').style.display = 'none';
        return;
    }
    
    // Show loading
    document.getElementById('performanceContainer').style.display = 'block';
    document.getElementById('performanceChart').innerHTML = '<div class="text-center py-5"><div class="spinner-border"></div><p>Loading...</p></div>';
    
    // Check if Highcharts is loaded
    if (typeof Highcharts === 'undefined') {
        console.error('Highcharts not loaded yet');
        document.getElementById('performanceChart').innerHTML = '<div class="text-center py-5 text-danger">Error: Chart library not loaded. Please refresh.</div>';
        return;
    }
    
    // Fetch student data
    fetch(`/Student_Result_Analytics/PHP_Files/student/api/get_student_chart_data.php?student_id=${studentId}&type=semester&t=${Date.now()}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            console.log('Chart data:', data);
            if (data.success && data.categories && data.categories.length > 0) {
                createChart(data);
                document.getElementById('studentNameHeader').textContent = 'Performance - ' + studentId;
            } else {
                document.getElementById('performanceChart').innerHTML = '<div class="text-center py-5"><i class="bi bi-bar-chart display-1 text-muted"></i><h5>No performance data available</h5></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('performanceChart').innerHTML = '<div class="text-center py-5 text-danger">Error loading data</div>';
        });
});

// Make sure createChart is defined AFTER Highcharts is loaded
function createChart(data) {
    try {
        Highcharts.chart('performanceChart', {
            chart: { type: 'column' },
            title: { text: 'Semester-wise Performance' },
            xAxis: { categories: data.categories },
            yAxis: { min: 0, max: 100, title: { text: 'Percentage (%)' } },
            series: [{
                name: 'Average Percentage',
                data: data.values,
                color: '#0073e6'
            }],
            accessibility: { enabled: false },
            credits: { enabled: false }
        });
    } catch (e) {
        console.error('Chart creation error:', e);
        document.getElementById('performanceChart').innerHTML = '<div class="text-center py-5 text-danger">Error creating chart</div>';
    }
}
</script>
</body>
</html>
