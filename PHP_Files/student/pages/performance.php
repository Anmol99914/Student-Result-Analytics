<?php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];
$student_name = $_SESSION['student_name'];

// Get student data
$stmt = $connection->prepare("
    SELECT s.student_id, s.student_name, 
           CONCAT(c.faculty, ' Semester ', c.semester) as class_display,
           sem.semester_name, c.semester as current_semester
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    LEFT JOIN semester sem ON s.semester_id = sem.semester_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Get semester performance data
// Get semester performance data - FIXED
$semester_sql = "SELECT 
                    sem.semester_name,
                    AVG(r.percentage) as avg_percentage
                FROM result r
                JOIN semester sem ON r.semester_id = sem.semester_id
                WHERE r.student_id = ? AND r.verification_status = 'verified'
                GROUP BY r.semester_id
                ORDER BY r.semester_id";
$semester_stmt = $connection->prepare($semester_sql);
$semester_stmt->bind_param("s", $student_id);
$semester_stmt->execute();
$semester_result = $semester_stmt->get_result();

$semester_data = [];
while($row = $semester_result->fetch_assoc()) {
    $semester_data[] = [
        'name' => $row['semester_name'],
        'percentage' => round($row['avg_percentage'], 1)
    ];
}

// Get subject performance data for current semester
$current_semester = $student['current_semester'] ?? 1;
$subject_sql = "SELECT 
                    s.subject_name,
                    r.percentage
                FROM result r
                JOIN subject s ON r.subject_id = s.subject_id
                WHERE r.student_id = ? AND r.semester_id = ? AND r.verification_status = 'verified'
                ORDER BY s.subject_name";
$subject_stmt = $connection->prepare($subject_sql);
$subject_stmt->bind_param("si", $student_id, $current_semester);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();

$subject_data = [];
while($row = $subject_result->fetch_assoc()) {
    $subject_data[] = [
        'name' => $row['subject_name'],
        'percentage' => round($row['percentage'], 1)
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance - <?php echo $student_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .performance-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .student-header {
            background: linear-gradient(135deg, #0073e6 0%, #005bb5 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,115,230,0.3);
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-card h6 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stats-card h3 {
            color: #0073e6;
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }
        .btn-view {
            background: white;
            color: #0073e6;
            border: 2px solid #0073e6;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-view:hover, .btn-view.active {
            background: #0073e6;
            color: white;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: white;
            color: #0073e6;
        }
    </style>
</head>
<body>
    <div class="performance-container">
        <!-- Student Header with Back Button -->
        <div class="student-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="bi bi-bar-chart-fill me-2"></i> <?php echo htmlspecialchars($student_name); ?></h2>
                    <p class="mb-0 opacity-75">
                        <i class="bi bi-person-badge me-2"></i> <?php echo $student_id; ?> | 
                        <i class="bi bi-book me-2"></i> <?php echo $student['class_display']; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="dashboard.php" class="back-btn">
                        <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>Overall Average</h6>
                    <h3 id="avg-mark">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>Pass Rate</h6>
                    <h3 id="pass-rate">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>Highest Score</h6>
                    <h3 id="max-mark">-</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6>Lowest Score</h6>
                    <h3 id="min-mark">-</h3>
                </div>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="text-center mb-4">
            <div class="btn-group" role="group">
                <button class="btn btn-view active" data-view="semester">By Semester</button>
                <button class="btn btn-view" data-view="subject">By Subject</button>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <div id="performanceChart" style="height: 400px;"></div>
        </div>
    </div>

    <!-- Pass Data to JavaScript -->
    <script>
        window.studentData = {
            semester: <?= json_encode($semester_data) ?>,
            subject: <?= json_encode($subject_data) ?>
        };
        console.log('Student data loaded:', window.studentData);
    </script>

    <!-- Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set up view buttons
            const buttons = document.querySelectorAll('.btn-view');
            buttons.forEach(btn => {
                btn.addEventListener('click', function() {
                    buttons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    renderView(this.dataset.view);
                });
            });
            
            // Initial render
            renderView('semester');
        });

        function renderView(view) {
            const data = window.studentData[view];
            const chartContainer = document.getElementById('performanceChart');
            
            if (!data || data.length === 0) {
                chartContainer.innerHTML = '<div style="text-align: center; padding: 100px;"><i class="bi bi-bar-chart" style="font-size: 48px; color: #ccc;"></i><h5 style="color: #666; margin-top: 20px;">No performance data available</h5></div>';
                
                // Reset stats
                document.getElementById('avg-mark').textContent = '-';
                document.getElementById('pass-rate').textContent = '-';
                document.getElementById('max-mark').textContent = '-';
                document.getElementById('min-mark').textContent = '-';
                return;
            }
            
            // Calculate stats
            const percentages = data.map(item => item.percentage);
            const validPercentages = percentages.filter(p => p > 0);
            const avg = validPercentages.length ? 
                (validPercentages.reduce((a,b) => a + b, 0) / validPercentages.length).toFixed(1) : 0;
            const pass = validPercentages.filter(p => p >= 40).length;
            const passRate = validPercentages.length ? 
                ((pass / validPercentages.length) * 100).toFixed(0) : 0;
            const max = validPercentages.length ? Math.max(...validPercentages) : 0;
            const min = validPercentages.length ? Math.min(...validPercentages) : 0;
            
            // Update stats
            document.getElementById('avg-mark').textContent = avg;
            document.getElementById('pass-rate').textContent = passRate + '%';
            document.getElementById('max-mark').textContent = max;
            document.getElementById('min-mark').textContent = min;
            
            // Create chart
            Highcharts.chart('performanceChart', {
                chart: {
                    type: view === 'semester' ? 'column' : 'bar',
                    backgroundColor: 'white'
                },
                title: {
                    text: view === 'semester' ? 'Semester-wise Performance' : 'Subject-wise Performance',
                    style: { color: '#0073e6', fontSize: '18px' }
                },
                xAxis: {
                    categories: data.map(item => item.name),
                    labels: {
                        rotation: view === 'semester' ? -30 : 0,
                        style: { fontSize: '12px' }
                    }
                },
                yAxis: {
                    min: 0,
                    max: 100,
                    title: { text: 'Percentage (%)' },
                    plotLines: [{
                        value: 40,
                        color: '#ffc107',
                        dashStyle: 'dash',
                        width: 2,
                        label: { text: 'Passing Mark' }
                    }]
                },
                series: [{
                    name: view === 'semester' ? 'Average Percentage' : 'Percentage',
                    data: data.map(item => item.percentage),
                    color: '#0073e6'
                }],
                credits: { enabled: false }
            });
        }
    </script>
</body>
</html>