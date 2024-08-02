<?php
session_start();

// Ensure admin is logged in
$admin_id = $_SESSION['user_id'] ?? null;
if (!$admin_id) {
    header("Location: index.php");
    exit();
}

// Check if the form data is set
if (!isset($_POST['section']) || !isset($_POST['teachers'])) {
    die("Invalid form submission.");
}

$section = $_POST['section'];
$teacherAssignments = $_POST['teachers'];

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$database = "timetable_manage";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch subjects with no_hours_per_week for the specified section
$subject_query = "select subject_id,";
$stmt = $conn->prepare($subject_query);
$stmt->bind_param("s", $section);
$stmt->execute();
$subject_result = $stmt->get_result();

// Fetch all classrooms
$classroom_query = "SELECT class_id FROM classes";
$classroom_result = $conn->query($classroom_query);
$classrooms = [];
while ($row = $classroom_result->fetch_assoc()) {
    $classrooms[] = $row['class_id'];
}

// Fetch the entire existing timetable for conflict checking
$existing_timetable_query = "SELECT * FROM schedule";
$existing_timetable_result = $conn->query($existing_timetable_query);
$existing_timetable = [];
while ($row = $existing_timetable_result->fetch_assoc()) {
    $existing_timetable[$row['day']][$row['slot']][] = [
        'teacher_id' => $row['teacher_id'],
        'class_id' => $row['class_id'],
        'subject_id' => $row['subject_id'],
        'section' => $row['section'] // Include section data here
    ];
}

// Initialize timetable
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$time_slots = ['9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM'];
$timetable = [];

// Allocate subjects to the timetable
while ($subject = $subject_result->fetch_assoc()) {
    $subject_id = $subject['subject_id'];
    $no_hours_per_week = $subject['no_hours_per_week'];
    $teacher_id = $teacherAssignments[$subject_id];

    // Assign each subject its required hours
    $hours_allocated = 0;
    $attempted_slots = [];
    $max_attempts = count($days) * count($time_slots); // Max attempts to prevent infinite loop

    $attempts = 0; // Counter for attempts

    while ($hours_allocated < $no_hours_per_week && $attempts < $max_attempts) {
        $attempts++;

        foreach ($days as $day) {
            foreach ($time_slots as $slot) {
                if ($hours_allocated >= $no_hours_per_week) break;

                // Avoid repeating on the same day
                if (isset($timetable[$day][$slot][$subject_id]) || in_array("$day-$slot", $attempted_slots)) {
                    continue;
                }

                // Check if teacher is available
                $teacher_available = true;
                if (isset($existing_timetable[$day][$slot])) {
                    foreach ($existing_timetable[$day][$slot] as $entry) {
                        if ($entry['teacher_id'] == $teacher_id) {
                            $teacher_available = false;
                            break;
                        }
                    }
                }
                if (!$teacher_available) {
                    continue;
                }

                // Check if students are available for this section
                $students_available = true;
                if (isset($existing_timetable[$day][$slot])) {
                    foreach ($existing_timetable[$day][$slot] as $entry) {
                        if ($entry['section'] == $section) {
                            $students_available = false;
                            break;
                        }
                    }
                }
                if (!$students_available) {
                    continue;
                }

                // Check for available classroom
                $classroom_id = null;
                $classroom_available = false;
                foreach ($classrooms as $room) {
                    $room_occupied = false;
                    if (isset($existing_timetable[$day][$slot])) {
                        foreach ($existing_timetable[$day][$slot] as $entry) {
                            if ($entry['class_id'] == $room) {
                                $room_occupied = true;
                                break;
                            }
                        }
                    }
                    if (!$room_occupied) {
                        $classroom_id = $room;
                        $classroom_available = true;
                        break;
                    }
                }
                if (!$classroom_available) {
                    $attempted_slots[] = "$day-$slot";
                    continue; // No available classroom
                }

                // Assign subject to the timetable
                $timetable[$day][$slot] = [
                    'subject_id' => $subject_id,
                    'teacher_id' => $teacher_id,
                    'class_id' => $classroom_id,
                    'section' => $section
                ];

                $hours_allocated++;
                $attempted_slots[] = "$day-$slot";
            }
        }
    }

    // Check if the subject was not fully scheduled
    if ($hours_allocated < $no_hours_per_week) {
        echo "Warning: Could not schedule all required hours for subject: {$subject['subject_name']}<br>";
    }
}

// Insert the generated timetable into the database
$insert_query = "
    INSERT INTO schedule (day, slot, subject_id, teacher_id, class_id, section)
    VALUES (?, ?, ?, ?, ?, ?)";
$insert_stmt = $conn->prepare($insert_query);

foreach ($timetable as $day => $slots) {
    foreach ($slots as $slot => $details) {
        $insert_stmt->bind_param(
            "ssssss",
            $day,
            $slot,
            $details['subject_id'],
            $details['teacher_id'],
            $details['class_id'],
            $details['section']
        );
        $insert_stmt->execute();
    }
}

// Close database connection
$conn->close();

echo "Timetable generated successfully!";
?>
