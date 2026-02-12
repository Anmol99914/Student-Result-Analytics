<?php
// test_db_structure.php
// Place this file in: Student_Result_Analytics/test_db_structure.php

require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Structure Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { border-collapse: collapse; width: 100%; }
        th { background: #007bff; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1>🔍 Database Structure Diagnostic</h1>";

// Test connection
echo "<div class='card'>";
echo "<h2>✅ Database Connection</h2>";
if ($connection) {
    echo "<p class='success'>✓ Connected successfully to database</p>";
    echo "<p>Database: <strong>" . $connection->query("SELECT DATABASE()")->fetch_row()[0] . "</strong></p>";
} else {
    echo "<p class='error'>✗ Connection failed</p>";
}
echo "</div>";

// Get all tables
echo "<div class='card'>";
echo "<h2>📋 All Tables in Database</h2>";
$tables = $connection->query("SHOW TABLES");
echo "<table>";
echo "<tr><th>#</th><th>Table Name</th></tr>";
$count = 1;
$table_list = [];
while ($row = $tables->fetch_row()) {
    echo "<tr><td>" . $count++ . "</td><td><strong>" . $row[0] . "</strong></td></tr>";
    $table_list[] = $row[0];
}
echo "</table>";
echo "</div>";

// Check specific tables we care about
$important_tables = ['teacher', 'subject', 'student', 'class', 'result', 'teacher_subjects', 'teacher_classes', 'admin'];

foreach ($important_tables as $table) {
    echo "<div class='card'>";
    echo "<h2>📊 Table: " . $table . "</h2>";
    
    // Check if table exists
    $check = $connection->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows == 0) {
        echo "<p class='error'>✗ Table '$table' does NOT exist!</p>";
        echo "</div>";
        continue;
    }
    
    echo "<p class='success'>✓ Table exists</p>";
    
    // Get table structure
    $columns = $connection->query("DESCRIBE $table");
    
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($col = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show row count
    $count = $connection->query("SELECT COUNT(*) as total FROM $table")->fetch_assoc()['total'];
    echo "<p>Total rows: <strong>" . $count . "</strong></p>";
    
    // Show sample data (first 3 rows)
    if ($count > 0) {
        $sample = $connection->query("SELECT * FROM $table LIMIT 3");
        echo "<h4>Sample Data (first 3 rows):</h4>";
        echo "<table>";
        
        // Headers
        echo "<tr>";
        $first_row = $sample->fetch_assoc();
        foreach (array_keys($first_row) as $field) {
            echo "<th>" . $field . "</th>";
        }
        echo "</tr>";
        
        // First row
        echo "<tr>";
        foreach ($first_row as $value) {
            echo "<td>" . htmlspecialchars(substr($value ?? 'NULL', 0, 50)) . "</td>";
        }
        echo "</tr>";
        
        // Next 2 rows
        while ($row = $sample->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars(substr($value ?? 'NULL', 0, 50)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "</div>";
}

// Check foreign key relationships
echo "<div class='card'>";
echo "<h2>🔗 Foreign Key Relationships</h2>";
$fk_query = "
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM 
    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE 
    REFERENCED_TABLE_SCHEMA = DATABASE()
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY 
    TABLE_NAME, CONSTRAINT_NAME";

$fk_result = $connection->query($fk_query);
if ($fk_result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Table</th><th>Column</th><th>References</th><th>References Column</th></tr>";
    while ($fk = $fk_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $fk['TABLE_NAME'] . "</td>";
        echo "<td>" . $fk['COLUMN_NAME'] . "</td>";
        echo "<td>" . $fk['REFERENCED_TABLE_NAME'] . "</td>";
        echo "<td>" . $fk['REFERENCED_COLUMN_NAME'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No foreign keys defined</p>";
}
echo "</div>";

// Check for teacher-subject assignment tables
echo "<div class='card'>";
echo "<h2>⚠️ Teacher-Subject Assignment Issue</h2>";

if (!in_array('teacher_subjects', $table_list)) {
    echo "<p class='error'>✗ 'teacher_subjects' table is MISSING!</p>";
    echo "<h3>✅ SQL to create it:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subject(subject_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id)
);";
    echo "</pre>";
} else {
    echo "<p class='success'>✓ 'teacher_subjects' table exists</p>";
}

if (!in_array('teacher_classes', $table_list)) {
    echo "<p class='error'>✗ 'teacher_classes' table is MISSING!</p>";
    echo "<h3>✅ SQL to create it:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "CREATE TABLE IF NOT EXISTS teacher_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    class_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES class(class_id) ON DELETE CASCADE,
    UNIQUE KEY unique_teacher_class (teacher_id, class_id)
);";
    echo "</pre>";
}
echo "</div>";

echo "</div></body></html>";
?>