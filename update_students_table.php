<?php

session_start(); // Start the session

$admin_id = $_SESSION['user_id'] ?? null; 

if (!$admin_id) {
    // Redirect to the login page if the admin is not logged in
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert student
if (isset($_POST['insert_student'])) {
    $student_username = $_POST['student_username'];
    $student_password = $_POST['student_password'];

    $sql = "INSERT INTO students (student_username, student_password) VALUES ('$student_username', '$student_password')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Student inserted successfully');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Delete student
if (isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];

    $sql = "DELETE FROM students WHERE student_id = $student_id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Student deleted successfully');</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Retrieve students
$sql = "SELECT * FROM students";
$result = $conn->query($sql);

$students = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>
<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Students Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container {
            margin-bottom: 20px;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
        }
        .form-container input[type="text"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .form-container button {
            padding: 8px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Update Students Table</h1>

        <!-- Insert Student Form -->
        <div class="form-container">
            <h2>Insert Student</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="student_username">Student Username:</label>
                    <input type="text" id="student_username" name="student_username" required>
                </div>

                <div class="form-group">
                    <label for="student_password">Student Password:</label>
                    <input type="password" id="student_password" name="student_password" required>
                </div>

                <div class="form-group">
                    <button type="submit" name="insert_student">Insert Student</button>
                </div>
            </form>
        </div>

        <!-- Delete Student Form -->
        <div class="form-container">
            <h2>Delete Student</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label for="student_id">Student ID:</label>
                    <input type="text" id="student_id" name="student_id" required>
                </div>

                <div class="form-group">
                    <button type="submit" name="delete_student">Delete Student</button>
                </div>
            </form>
        </div>

        <!-- Display Students Table -->
        <?php if (!empty($students)) : ?>
            <h2>All Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Username</th>
                        <th>Student Password</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td><?php echo $student['student_id']; ?></td>
                            <td><?php echo $student['student_username']; ?></td>
                            <td><?php echo $student['student_password']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
