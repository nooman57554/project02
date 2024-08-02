<?php include('header.php'); ?>
<?php
session_start(); // Start the session
$user_id = $_SESSION['user_id'] ?? null; 
if (!$user_id) {
    // Redirect to the login page if no user is logged in
    header("Location: index.php");
    exit();
}
$role = $_SESSION['role'] ?? null; // Get the role of the logged-in user

if ($role === 'student') {
    // Redirect to the student dashboard if the logged-in user is a student
    header("Location: student_dashboard.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch teacher names from teachers table
$teacher_query = "SELECT teacher_name FROM teachers";
$teacher_result = $conn->query($teacher_query);
$teacher_options = "";

if ($teacher_result->num_rows > 0) {
    while ($row = $teacher_result->fetch_assoc()) {
        $teacher_name = $row['teacher_name'];
        $teacher_options .= "<option value=\"$teacher_name\">$teacher_name</option>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time table management - CSE </title>
    <style>
        /* Class options page styles */
        .class-options h1 {
            text-align: center; /* Center-align the heading */
        }

        .class-options ul {
            list-style-type: none; /* Remove default list styles */
            padding: 0; /* Remove default padding */
        }

        .class-options li {
            margin-bottom: 10px; 
        }

        .class-options a {
            text-decoration: none;
            color: #333; 
            display: block; 
            padding: 10px; 
            background-color: #fff; 
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .class-options a:hover {
            background-color: #f0f0f0; /* Change background color on hover */
        }
    </style>
</head>
<body>
    <div class="class-options">
        <h1>Time table management - CSE </h1>
        
        <ul>
            <li><a href="student_dashboard.php">Display Students Timetable</a></li>
            <li><a href="insert_class_form.php">Insert Class</a></li>
            <li><a href="delete_class.php">Delete Class</a></li>
            <li><a href="update_students_table.php">Update Students Table</a></li>
            <li><a>
    <form action="display_timetable.php" method="get">
        <label for="teacher_name">Display teacher timetable: </label>
        <select id="teacher_name" name="roll" required >
            <option value="" disabled selected></option>
            <?php echo $teacher_options; ?>
        </select>
        <button type="submit">Go</button>
    </form>
    </a></li>

    <li><a href="master_timetable.php">master Table</a></li>
    <li><a href="select_teachers.php">auto gen</a></li>
        </ul>
    </div>
</body>
</html>
