<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $role = sanitize_input($_POST['role']);

    $query = "";

    if ($role === 'admin') {
        $query = "SELECT * FROM admins WHERE admin_username='$username' AND admin_password='$password'";
        $redirect_url = "admin_home.php";
        $id_column = "admin_id";
        
    } elseif ($role === 'student') {
        $query = "SELECT * FROM students WHERE student_username='$username' AND student_password='$password'";
        $redirect_url = "student_dashboard.php";
        $id_column = "student_id";
    }

    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row[$id_column];
        $_SESSION['role'] = $role;
        header("Location: $redirect_url");
    } else {
        $error_message = "Invalid credentials. Please try again.";
        header("Location: index.php?error_message=" . urlencode($error_message));
    }

    $conn->close();
}
?>
