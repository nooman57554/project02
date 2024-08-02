<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$day = $_POST['day'];
$slot = $_POST['slot'];
$teacher_name = strtolower($_POST['teacher_name']);
$is_lab = isset($_POST['is_lab']) ? true : false;

// Get teacher ID
$teacher_query = "SELECT teacher_id FROM teachers WHERE LOWER(teacher_name)='$teacher_name'";
$teacher_result = $conn->query($teacher_query);
$teacher_row = $teacher_result->fetch_assoc();
$teacher_id = $teacher_row['teacher_id'];



$previous_slot = getPreviousSlot($slot);
$next_slot = getNextSlot($slot);
$next_of_next_slot = getNextSlot($next_slot);

if ($is_lab){
    $consecutive_query = "SELECT * FROM schedule 
                      WHERE day='$day' 
                      AND teacher_id='$teacher_id' 
                      AND (slot='$previous_slot' OR slot='$next_of_next_slot')";
    $consecutive_result = $conn->query($consecutive_query);
    $response = ['consecutive' => $consecutive_result->num_rows > 0];
    echo json_encode($response);
}else{

    $consecutive_query = "SELECT * FROM schedule 
                          WHERE day='$day' 
                          AND teacher_id='$teacher_id' 
                          AND (slot='$previous_slot' OR slot='$next_slot')";
    $consecutive_result = $conn->query($consecutive_query);

    $response = ['consecutive' => $consecutive_result->num_rows > 0];
    echo json_encode($response);
}
$conn->close();

function getPreviousSlot($slot) {
    $slots = ["9AM - 10AM", "10AM - 11AM", "11AM - 12PM", "12PM - 1PM", "1PM - 2PM", "2PM - 3PM", "3PM - 4PM"];
    $index = array_search($slot, $slots);
    return $index > 0 ? $slots[$index - 1] : null;
}

function getNextSlot($slot) {
    $slots = ["9AM - 10AM", "10AM - 11AM", "11AM - 12PM", "12PM - 1PM", "1PM - 2PM", "2PM - 3PM", "3PM - 4PM"];
    $index = array_search($slot, $slots);
    return $index < count($slots) - 1 ? $slots[$index + 1] : null;
}
?>
