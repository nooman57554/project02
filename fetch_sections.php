<?php
// fetch_sections.php

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

// Fetch section names
$sql = "SELECT sectionName FROM Section";
$result = $conn->query($sql);

// Check for SQL query errors
if (!$result) {
    die("SQL query failed: " . $conn->error);
}

// Output checkboxes for sections
while ($row = $result->fetch_assoc()) {
    $section_name = htmlspecialchars($row['sectionName']);
    echo "<label><input type='checkbox' class='section-checkbox' value='$section_name'> $section_name</label><br>";
}

// Close the database connection
$conn->close();
?>
