<?php
session_start(); // Start the session

// Placeholder for the logged-in admin's ID 
$admin_id = $_SESSION['user_id'] ?? null; 

if (!$admin_id) {
    // Redirect to the login page if the admin is not logged in
    header("Location: index.php");
    exit();
}

// Your database connection logic here (replace with your actual database connection code)
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Placeholder for processing form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['delete_single'])) {
        // Retrieve form data
        $day = $_POST['day'];
        $slot = $_POST['slot'];
        $section = $_POST['section'];

        // Placeholder for obtaining section from schedule table
        $section_query = "SELECT section FROM schedule WHERE section='$section'";
        $section_result = $conn->query($section_query);
        $section_row = $section_result->fetch_assoc();
        $section_id = $section_row['section'];

        // Check if the class is scheduled at the specified time slot
        $scheduled_query = "SELECT * FROM schedule WHERE day='$day' AND slot='$slot' AND section='$section'";
        $scheduled_result = $conn->query($scheduled_query);

        if ($scheduled_result->num_rows == 0) {
            echo "Error: No class is scheduled at the specified time slot.";
        } else {
            // Delete the scheduled class from the timetable
            $delete_query = "DELETE FROM schedule WHERE day='$day' AND slot='$slot' AND section='$section'";
            
            if ($conn->query($delete_query) === TRUE) {
                echo "Class deleted from the timetable successfully!";
            } else {
                echo "Error: " . $delete_query . "<br>" . $conn->error;
            }
        }
    } elseif (isset($_POST['delete_all'])) {
        // Retrieve section from form data
        $section = $_POST['section'];

        // Delete all classes for the specified section from the timetable
        $delete_all_query = "DELETE FROM schedule WHERE section='$section'";
        
        if ($conn->query($delete_all_query) === TRUE) {
            echo "All classes for section $section deleted from the timetable successfully!";
        } else {
            echo "Error: " . $delete_all_query . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>
<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Scheduled Class</title>
    <style>

            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f2f2f2;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            h1, h2 {
                color: #333;
            }

            form {
                margin-bottom: 20px;
            }

            label {
                font-weight: bold;
            }

            input[type="text"], select {
                width: 100%;
                padding: 8px;
                margin-top: 6px;
                margin-bottom: 12px;
                border: 1px solid #ccc;
                border-radius: 4px;
                box-sizing: border-box;
            }

            button {
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            button:hover {
                background-color: #45a049;
            }

    </style>
</head>
<body>
<div class="container">
        <h1>Delete Scheduled Class</h1>

        <!-- Delete Single Class Form -->
        <h2>Delete Single Class</h2>
        <form action="" method="post">
            <label for="day">Day:</label>
            <input type="text" id="day" name="day" required>
            <br>
            <label for="slot">Time Slot:</label>
            <select id="slot" name="slot" required>
                <option value="9AM - 10AM">9AM - 10AM</option>
                <option value="10AM - 11AM">10AM - 11AM</option>
                <option value="11AM - 12PM">11AM - 12PM</option>
                <option value="12PM - 1PM">12PM - 1PM</option>
                <option value="1PM - 2PM">1PM - 2PM</option>
                <option value="2PM - 3PM">2PM - 3PM</option>
                <option value="3PM - 4PM">3PM - 4PM</option>
                <!-- Add more options as needed -->
            </select>
            <br>
            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option value="CSE-A">CSE-A</option>
                <option value="CSE-B">CSE-B</option>
            </select>
            <br>
            <button type="submit" name="delete_single">Delete Class</button>
        </form>

        <!-- Delete All Classes Form -->
        <h2>Delete All Classes</h2>
        <form action="" method="post">
            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option value="CSE-A">CSE-A</option>
                <option value="CSE-B">CSE-B</option>
            </select>
            <br>
            <button type="submit" name="delete_all">Delete All Classes</button>
        </form>
    </div>
</body>
</html>
