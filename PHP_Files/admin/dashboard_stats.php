<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header('Location: admin_login.php');
    exit();
}
// Get result stats - using verification_status column
$pending_results = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'pending'")->fetch_assoc()['count'];
$verified_results = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'verified'")->fetch_assoc()['count'];
$rejected_results = $connection->query("SELECT COUNT(*) as count FROM result WHERE verification_status = 'rejected'")->fetch_assoc()['count'];

// Get monthly results for chart
$monthly_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $count = $connection->query("
        SELECT COUNT(*) as count 
        FROM result 
        WHERE DATE_FORMAT(published_date, '%Y-%m') = '$month'
        AND status = 'published'
    ")->fetch_assoc()['count'];
    
    $monthly_data[] = [
        'month' => $month_name,
        'count' => (int)$count
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .stats-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #0073e6 0%, #005bb5 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
    <div class="stats-container">
        <!-- Header -->
        <div class="header d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-bar-chart-fill me-2"></i>Results Analytics</h2>
                <p class="mb-0">Visual overview of result statistics</p>
            </div>
            <a href="admin_main_page.php" class="back-btn">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="bi bi-bar-chart text-primary me-2"></i>
                        Results Published (Last 6 Months)
                    </h5>
                    <div id="monthlyChart" style="height: 350px;"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="bi bi-pie-chart text-primary me-2"></i>
                        Current Result Status
                    </h5>
                    <div id="statusChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Monthly Results Chart
        Highcharts.chart('monthlyChart', {
            chart: { 
                type: 'column',
                backgroundColor: 'white'
            },
            title: { text: null },
            xAxis: {
                categories: <?= json_encode(array_column($monthly_data, 'month')) ?>,
                title: { text: 'Month' }
            },
            yAxis: {
                min: 0,
                title: { text: 'Number of Results' },
                allowDecimals: false
            },
            tooltip: {
                pointFormat: '<b>{point.y}</b> results published'
            },
            plotOptions: {
                column: {
                    color: '#0073e6',
                    dataLabels: {
                        enabled: true,
                        format: '{point.y}'
                    }
                }
            },
            series: [{
                name: 'Published Results',
                data: <?= json_encode(array_column($monthly_data, 'count')) ?>,
                color: '#0073e6'
            }],
            credits: { enabled: false }
        });

        // Result Status Pie Chart
        Highcharts.chart('statusChart', {
            chart: { 
                type: 'pie',
                backgroundColor: 'white'
            },
            title: { text: null },
            tooltip: {
                pointFormat: '<b>{point.y}</b> results ({point.percentage:.1f}%)'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y}',
                        style: {
                            fontSize: '12px'
                        }
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Results',
                data: [
                    { 
                        name: 'Pending', 
                        y: <?= $pending_results ?>, 
                        color: '#ffc107' 
                    },
                    { 
                        name: 'Verified', 
                        y: <?= $verified_results ?>, 
                        color: '#28a745' 
                    },
                    { 
                        name: 'Rejected', 
                        y: <?= $rejected_results ?>, 
                        color: '#dc3545' 
                    }
                ]
            }],
            credits: { enabled: false }
        });
    </script>
</body>
</html>