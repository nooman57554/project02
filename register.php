<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

// Password validation
$errors = [];
if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}
if (!preg_match('/[A-Z]/', $password)) {
    $errors[] = "Password must contain at least one uppercase letter.";
}
if (!preg_match('/[a-z]/', $password)) {
    $errors[] = "Password must contain at least one lowercase letter.";
}
if (!preg_match('/\d/', $password)) {
    $errors[] = "Password must contain at least one digit.";
}
if (!preg_match('/[!@#$%^&*]/', $password)) {
    $errors[] = "Password must contain at least one special character.";
}

if (!empty($errors)) {
    $error_message = implode(" ", $errors);
    header("Location: signup.php?signup_error_message=$error_message");
    exit();
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Insert user into the respective table
if ($role == 'admin') {
    $sql = "INSERT INTO admins (admin_username, admin_password) VALUES ('$email', '$hashedPassword')";
} elseif ($role == 'student') {
    $sql = "INSERT INTO students (student_username, student_password) VALUES ('$email', '$hashedPassword')";
}

if ($conn->query($sql) === TRUE) {
    echo "Registration successful.";
    // Redirect to login page or any other page
    header("Location: index.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

