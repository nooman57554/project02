<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$section = $_POST['section'] ?? null;

if ($section) {
    // Fetch the semester for the selected section
    $sem_query = "SELECT Sem FROM Section WHERE SectionName = ?";
    $stmt = $conn->prepare($sem_query);

    if ($stmt) {
        $stmt->bind_param('s', $section);
        $stmt->execute();
        $stmt->bind_result($sem);
        $stmt->fetch();
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit();
    }

    $subjects = [];

    if ($sem) {
        // Fetch subjects based on semester
        $subject_query = "SELECT subject_id, subject_name FROM subjects WHERE Sem_id = ?";
        $stmt = $conn->prepare($subject_query);
        if ($stmt) {
            $stmt->bind_param('i', $sem);
            $stmt->execute();
            $stmt->bind_result($subject_id, $subject_name);
            while ($stmt->fetch()) {
                $subjects[] = ['subject_id' => $subject_id, 'subject_name' => $subject_name];
            }
            $stmt->close();
        } else {
            echo json_encode(['error' => 'Failed to prepare statement for subjects']);
            exit();
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['subjects' => $subjects]);
}

$conn->close();
?>
