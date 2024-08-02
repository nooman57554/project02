<?php
header('Content-Type: application/json');

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

// Create a new connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Get the semester from the input
$semester = $data['semester'] ?? null;

$response = ['subjects' => []];
if ($semester !== null) {
    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT subject_name FROM subjects WHERE Sem_id = ?");
    $stmt->bind_param("i", $semester);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch subjects
    while ($row = $result->fetch_assoc()) {
        $response['subjects'][] = $row['subject_name'];
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();

// Encode the response as JSON
echo json_encode($response);