<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    die('Unauthorized');
}

// Get filters
$faculty = $_GET['faculty'] ?? '';
$semester = $_GET['semester'] ?? '';

// Build query - ONLY PUBLISHED RESULTS
$sql = "SELECT 
            s.student_id,
            s.student_name,
            c.faculty,
            c.semester,
            sub.subject_name,
            r.marks_obtained,
            r.total_marks,
            r.percentage,
            r.grade,
            r.published_date
        FROM result r
        JOIN student s ON r.student_id = s.student_id
        JOIN class c ON s.class_id = c.class_id
        JOIN subject sub ON r.subject_id = sub.subject_id
        WHERE r.status = 'published'";

if ($faculty) {
    $sql .= " AND c.faculty = '" . $connection->real_escape_string($faculty) . "'";
}
if ($semester) {
    $sql .= " AND c.semester = '" . $connection->real_escape_string($semester) . "'";
}

$sql .= " ORDER BY c.faculty, c.semester, s.student_name, sub.subject_name";

$results = $connection->query($sql);
$count = $results->num_rows;

// Generate HTML for PDF
?>
<!DOCTYPE html>
<html>
<head>
    <title>Published Results Export</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0073e6;
            padding-bottom: 10px;
        }
        h2 { 
            color: #0073e6; 
            margin: 0;
        }
        .filter-info {
            background: #f0f5ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .back-btn {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .back-btn:hover {
            background: #5a6268;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            font-size: 12px;
        }
        th { 
            background: #0073e6; 
            color: white; 
            padding: 10px; 
            text-align: left; 
            font-weight: 600;
        }
        td { 
            padding: 8px; 
            border: 1px solid #ddd; 
        }
        tr:nth-child(even) { 
            background: #f9f9f9; 
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 11px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .badge {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .no-data {
            text-align: center;
            padding: 50px;
            color: #999;
            font-size: 16px;
        }

    @media print {
        .back-btn, .print-btn, button, .no-print {
            display: none !important;
        }
        
        /* Optional: Remove URL and date from footer in print */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    }
    </style>
</head>
<body>
    
    <body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;" class="no-print">
        <a href="javascript:history.back()" class="back-btn no-print">← Back to Export Page</a>
        <button onclick="window.print()"  class="print-btn no-print" style="
            background: #0073e6;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        ">
            🖨️ Print / Save as PDF
        </button>
    </div>

    <div class="header">
        <h2>Student Results Export</h2>
        <p>Published Results Only</p>
    </div>
    
    <div class="filter-info">
        <strong>Filters Applied:</strong><br>
        Faculty: <?php echo $faculty ?: 'All'; ?> | 
        Semester: <?php echo $semester ?: 'All'; ?> |
        Total Records: <?php echo $count; ?> (Published Only)
    </div>
    
    <?php if($count > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Faculty</th>
                <th>Sem</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>Percentage%</th>
                <th>Grade</th>
                <th>Published</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $results->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['student_id']; ?></td>
                <td><?php echo $row['student_name']; ?></td>
                <td><?php echo $row['faculty']; ?></td>
                <td class="text-center"><?php echo $row['semester']; ?></td>
                <td><?php echo $row['subject_name']; ?></td>
                <td class="text-center"><?php echo $row['marks_obtained']; ?>/<?php echo $row['total_marks']; ?></td>
                <td class="text-center"><?php echo $row['percentage']; ?>%</td>
                <td class="text-center"><span class="badge"><?php echo $row['grade']; ?></span></td>
                <td class="text-center"><?php echo date('d M Y', strtotime($row['published_date'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">
        <h3>No Published Results Found</h3>
        <p>Try different filters or wait for results to be verified and published.</p>
    </div>
    <?php endif; ?>
    
    <div class="footer">
        <p>Generated by Student Result Analytics System on <?php echo date('F d, Y H:i:s'); ?></p>
        <p>This report contains ONLY published (verified) results.</p>
    </div>
    
    <script>
        // Auto-print? Uncomment if we want automatic print:)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>