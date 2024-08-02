<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$role = $_GET['roll'] ?? null;
$section = $_GET['section'] ?? 'CSE-A';
$format = $_GET['format'] ?? 'csv';

// Prepare SQL query based on role or section
if ($role) {
    $sql = "SELECT schedule.*, subjects.subject_name, classes.class_name
            FROM schedule 
            INNER JOIN subjects ON schedule.subject_id = subjects.subject_id
            INNER JOIN classes ON schedule.class_id = classes.class_id
            INNER JOIN teachers ON schedule.teacher_id = teachers.teacher_id
            WHERE teachers.teacher_name = '$role'
            ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
                     FIELD(slot, '9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM')";
} else {
    $sql = "SELECT schedule.*, subjects.subject_name, classes.class_name
            FROM schedule 
            INNER JOIN subjects ON schedule.subject_id = subjects.subject_id
            INNER JOIN classes ON schedule.class_id = classes.class_id
            WHERE schedule.section = '$section'
            ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), FIELD(slot, '9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM')";
}

$result = $conn->query($sql);

// Check for SQL query errors
if (!$result) {
    die("SQL query failed: " . $conn->error);
}

// Define days and time slots
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$time_slots = ['9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM'];

// Initialize timetable array with '-' for empty slots
$timetable = [];
foreach ($days as $day) {
    foreach ($time_slots as $slot) {
        $timetable[$day][$slot] = '-';
    }
}

// Populate timetable array with data from database
while ($row = $result->fetch_assoc()) {
    $day = ucfirst(strtolower($row['day']));
    $time_slot = $row['slot'];
    $subject_name = $row['subject_name'];
    $class_name = $row['class_name'];
    $batch = $row['batch'];

    $entry = $subject_name . " " . $class_name;
    if (!empty($batch)) {
        $entry .= " (Batch $batch)";
    }

    $timetable[$day][$time_slot] = $entry;
}

// Export to CSV
if ($format === 'csv') {
    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=timetable_export.csv');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write CSV headers
    $headers = array_merge(['Day'], $time_slots);
    fputcsv($output, $headers);

    // Write timetable data to CSV
    foreach ($days as $day) {
        $row = [$day];
        foreach ($time_slots as $slot) {
            $row[] = $timetable[$day][$slot];
        }
        fputcsv($output, $row);
    }

    // Close the output stream
    fclose($output);
} else {
    echo "Format not supported.";
}

// Close the database connection
$conn->close();
?>