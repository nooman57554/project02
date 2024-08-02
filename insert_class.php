
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

// defining message and error-message variable
$message = null;
$error_message = null;

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
    $lab_assistant_name = $is_lab ? strtolower(sanitize_input($_POST['lab_assistant_name'])) : null;

    function getIdFromName($conn, $table, $id_field, $name_field, $name) {
        $query = "SELECT $id_field FROM $table WHERE LOWER($name_field)='$name'";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        return $row[$id_field] ?? null;
    }

    // Get IDs for subject, class, teacher, and lab assistant
    $subject_id = getIdFromName($conn, 'subjects', 'subject_id', 'subject_name', $subject_name);
    $class_id = getIdFromName($conn, 'classes', 'class_id', 'class_name', $class_name);
    $teacher_id = getIdFromName($conn, 'teachers', 'teacher_id', 'teacher_name', $teacher_name);
    $lab_assistant_id = $is_lab ? getIdFromName($conn, 'lab_assistants', 'assistant_id', 'assistant_name', $lab_assistant_name) : null;



     // Set the header to JSON format for the response
     header('Content-Type: application/json');

    function getNextSlot($slot) {
        $slots = ["9AM - 10AM", "10AM - 11AM", "11AM - 12PM", "12PM - 1PM", "1PM - 2PM", "2PM - 3PM", "3PM - 4PM"];
        $index = array_search($slot, $slots);
        return $index < count($slots) - 1 ? $slots[$index + 1] : null;
    }

    function insert_class($conn, $day, $slot, $subject_id, $class_id, $teacher_id, $section, $is_lab = false, $batch = null, $lab_assistant_id = null) {
        global $error_message, $message;

        

        if ($is_lab) {
            $next_slot = getNextSlot($slot);

            $query = "INSERT INTO schedule (day, slot, subject_id, class_id, teacher_id, section, batch, assistant_id)
                      VALUES ('$day', '$slot', '$subject_id', '$class_id', '$teacher_id', '$section', '$batch', '$lab_assistant_id')";
            $next_slot_query = "INSERT INTO schedule (day, slot, subject_id, class_id, teacher_id, section, batch, assistant_id)
                                VALUES ('$day', '$next_slot', '$subject_id', '$class_id', '$teacher_id', '$section', '$batch', '$lab_assistant_id')";

            if ($conn->query($query) === TRUE && $conn->query($next_slot_query) === TRUE) {
                $message = "Lab class scheduled successfully.";
                return ['success' => true, 'message' => $message];
            } else {
                $error_message = "Error: " . $conn->error;
                return ['success' => false, 'error' => $error_message];
            }
        } else {

            $query = "INSERT INTO schedule (day, slot, subject_id, class_id, teacher_id, section) 
                      VALUES ('$day', '$slot', '$subject_id', '$class_id', '$teacher_id', '$section')";

            if ($conn->query($query) === TRUE) {
                $message = "Class scheduled successfully.";
                return ['success' => true, 'message' => $message];
            } else {
                $error_message = "Error: " . $conn->error;
                return ['success' => false, 'error' => $error_message];
            }
        }
    }

    $response = insert_class($conn, $day, $slot, $subject_id, $class_id, $teacher_id, $section, $is_lab, $batch, $lab_assistant_id);

    echo json_encode($response);
} else {
    // Handle the case when the required POST fields are not set
    echo json_encode(['success' => false, 'error' => 'Required fields are missing.']);
}

$conn->close();
?>
