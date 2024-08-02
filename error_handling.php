<?php
session_start(); // Start the session

$admin_id = $_SESSION['user_id'] ?? null;

if (!$admin_id) {
    // Redirect to the login page if the admin is not logged in
    header("Location: index.php");
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

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function getIdFromName($conn, $table, $id_field, $name_field, $name) {
    $query = "SELECT $id_field FROM $table WHERE LOWER($name_field)='$name'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row[$id_field] ?? null;
}

function getNextSlot($slot) {
    $slots = ["9AM - 10AM", "10AM - 11AM", "11AM - 12PM", "12PM - 1PM", "1PM - 2PM", "2PM - 3PM", "3PM - 4PM"];
    $index = array_search($slot, $slots);
    return $index < count($slots) - 1 ? $slots[$index + 1] : null;
}

function getPreviousSlot($slot) {
    $slots = ["9AM - 10AM", "10AM - 11AM", "11AM - 12PM", "12PM - 1PM", "1PM - 2PM", "2PM - 3PM", "3PM - 4PM"];
    $index = array_search($slot, $slots);
    return $index > 0 ? $slots[$index - 1] : null;
}

function checkConsecutiveClasses($conn, $day, $slot, $teacher_id ,$is_lab) {
    $previous_slot = getPreviousSlot($slot);
    $next_slot = getNextSlot($slot);
    $next_of_next_slot = getNextSlot($next_slot);

    if ($is_lab){
        $consecutive_query = "SELECT * FROM schedule 
                          WHERE day='$day' 
                          AND teacher_id='$teacher_id' 
                          AND (slot='$previous_slot' OR slot='$next_of_next_slot')";
        $consecutive_result = $conn->query($consecutive_query);
        return $consecutive_result->num_rows > 0;
    }else{
    
        $consecutive_query = "SELECT * FROM schedule 
                              WHERE day='$day' 
                              AND teacher_id='$teacher_id' 
                              AND (slot='$previous_slot' OR slot='$next_slot')";
        $consecutive_result = $conn->query($consecutive_query);
        return $consecutive_result->num_rows > 0;
    }
    
}

function checkSubjectHoursLimit($conn, $subject_id, $section) {
    // Query to count the total hours for the subject and section
    $subject_hours_query = "
        SELECT COUNT(*) as total_hours
        FROM schedule
        WHERE subject_id='$subject_id' AND section='$section'
    ";

    $subject_hours_result = $conn->query($subject_hours_query);
    $subject_hours_row = $subject_hours_result->fetch_assoc();
    $total_hours = $subject_hours_row['total_hours'] ?? 0;

    // Get the maximum allowed hours per week for the subject
    $no_hours_query = "
        SELECT no_hours_per_week
        FROM subjects
        WHERE subject_id='$subject_id'
    ";

    $no_hours_result = $conn->query($no_hours_query);
    $no_hours_row = $no_hours_result->fetch_assoc();
    $max_hours_per_week = $no_hours_row['no_hours_per_week'] ?? 0;

    // Check if the total hours exceed the allowed limit
    return $total_hours >= $max_hours_per_week;
}


// Check if the form is submitted and all necessary fields are set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['day'], $_POST['slot'], $_POST['subject_name'], $_POST['class_name'], $_POST['teacher_name'], $_POST['section'])) {
    $day = sanitize_input($_POST['day']);
    $slot = sanitize_input($_POST['slot']);
    $subject_name = strtolower(sanitize_input($_POST['subject_name']));
    $class_name = strtolower(sanitize_input($_POST['class_name']));
    $teacher_name = strtolower(sanitize_input($_POST['teacher_name']));
    $section = strtoupper(sanitize_input($_POST['section']));
    $is_lab = isset($_POST['is_lab']) ? true : false;
    $batch = $is_lab ? strtoupper(sanitize_input($_POST['batch'])) : null;
    
    $errors = [];
    $warnings = [];

    // Get IDs for subject, class, teacher, and lab assistant
    $subject_id = getIdFromName($conn, 'subjects', 'subject_id', 'subject_name', $subject_name);
    $class_id = getIdFromName($conn, 'classes', 'class_id', 'class_name', $class_name);
    $teacher_id = getIdFromName($conn, 'teachers', 'teacher_id', 'teacher_name', $teacher_name);

    if (!$subject_id || !$class_id || !$teacher_id) {
        $errors[] = "Error: Invalid subject, class, or teacher.";
    }

    // check if it is a consecutive class
    $check_consecutive = checkConsecutiveClasses($conn, $day, $slot, $teacher_id, $is_lab);
    if ($check_consecutive){
        $warnings[] = "warning : The teacher has a consecutive class. but can continue!";
    }
    

    $subject_hours_exceeded = checkSubjectHoursLimit($conn, $subject_id, $section);
    if ($subject_hours_exceeded) {
        $warnings[] = "warnings: maximum allowed hours reached for this section, continue? .";
    }



    // Check if the teacher is already reserved for another class at the specified time slot
    $teacher_query = "SELECT * FROM schedule WHERE day='$day' AND slot='$slot' AND teacher_id='$teacher_id'";
    $teacher_result = $conn->query($teacher_query);

    if ($teacher_result->num_rows > 0) {
        $errors[] = "Error: The teacher is already reserved for another class at the specified time slot.";
    }



    // Check if the students are free if it's a lab class
    if ($is_lab) {
        $next_slot = getNextSlot($slot);

        // Check for student availability for both slots
        $stdbusy_query = "SELECT * FROM schedule WHERE (day='$day' AND slot='$slot' AND section='$section' AND batch='$batch') 
                            OR (day='$day' AND slot='$next_slot' AND section='$section' AND batch='$batch')";
        $stdbusy_result = $conn->query($stdbusy_query);

        if ($stdbusy_result->num_rows > 0) {
            $errors[] = "Error: The students are already engaged at the specified time slots.";
        }

        // Check if the class is already occupied at the specified time slot
        $class_query = "SELECT * FROM schedule WHERE (day='$day' AND slot='$slot' AND class_id='$class_id') OR (day='$day' AND slot='$next_slot' AND class_id='$class_id')";
        $class_result = $conn->query($class_query);

        if ($class_result->num_rows > 0) {
            $errors[] = "Error: The class is already occupied at the specified time slot.";
        }

        // Check if the lab assistant is free if it's a lab class
    
        
        $lab_assistant_name = $is_lab ? strtolower(sanitize_input($_POST['lab_assistant_name'])) : null;
        if ($lab_assistant_name){
        $lab_assistant_id = getIdFromName($conn, 'lab_assistants', 'assistant_id', 'assistant_name', strtolower($lab_assistant_name));
        $assistant_query = "SELECT * FROM schedule WHERE (day='$day' AND slot='$slot' AND assistant_id='$lab_assistant_id') OR (day='$day' AND slot='$next_slot' AND assistant_id='$lab_assistant_id')";
        $assistant_result = $conn->query($assistant_query);
        
        if ($assistant_result->num_rows > 0) {
            $errors[] = "Error: The lab assistant is already booked for another lab at the specified time slot.";
        }
    }

    } else {
        // Check if students are free if it's a regular class
        $stdbusy_query = "SELECT * FROM schedule WHERE day='$day' AND slot='$slot' AND section='$section'";
        $stdbusy_result = $conn->query($stdbusy_query);

        if ($stdbusy_result->num_rows > 0) {
            $errors[] = "Error: The students are already engaged at the specified time slot.";
        }

            // Check if the class is already occupied at the specified time slot
        $class_query = "SELECT * FROM schedule WHERE day='$day' AND slot='$slot' AND class_id='$class_id'";
        $class_result = $conn->query($class_query);

        if ($class_result->num_rows > 0) {
            $errors[] = "Error: The class is already occupied at the specified time slot.";
        }
    }


         
    

    // Return errors as JSON
    header('Content-Type: application/json');
    echo json_encode(['errors' => $errors, 'warnings' => $warnings, 'consecutive' => $check_consecutive, 'hours_exceeded' => $subject_hours_exceeded]);

    $conn->close();
} else {
    // Handle the case when the required POST fields are not set
    echo json_encode(['errors' => ['Required fields are missing.']]);
}
?>
