<?php
include('header.php'); 
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


$section_query = "SELECT SectionName FROM Section";
$section_result = $conn->query($section_query);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .section-box {
            width: 200px;
            height: 200px;
            background-color: #f2f2f2;
            border-radius: 10px;
            margin: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .section-box:hover {
            background-color: #e0e0e0;
            transform: scale(1.05);
        }

        .section-title {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
      
        if ($section_result->num_rows > 0) {
            while ($row = $section_result->fetch_assoc()) {
                $section_name = $row['SectionName'];
                echo '<a href="display_timetable.php?section=' . urlencode($section_name) . '" style="text-decoration: none; color: inherit;">';
                echo '<div class="section-box">';
                echo '<span class="section-title">' . htmlspecialchars($section_name) . '</span>';
                echo '</div>';
                echo '</a>';
            }
        } else {
            echo '<p>No sections available.</p>';
        }
        ?>
    </div>
</body>
</html>


