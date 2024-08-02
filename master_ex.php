<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Timetable</title>
    <link rel="stylesheet" href="display.css">
</head>
<body>
    <div class="container">
        <h2>Master Timetable</h2>

        <!-- Section Selection Form -->
        <div class="section-selector">
            <form method="GET" action="master_timetable.php">
                <label><input type="checkbox" name="sections[]" value="CSE-2A"> 2A</label>
                <label><input type="checkbox" name="sections[]" value="CSE-2B"> 2B</label>
                <label><input type="checkbox" name="sections[]" value="CSE-4A"> 4A</label>
                <label><input type="checkbox" name="sections[]" value="CSE-4B"> 4B</label>
                <label><input type="checkbox" name="sections[]" value="CSE-6A"> 6A</label>
                <label><input type="checkbox" name="sections[]" value="CSE-6B"> 6B</label>
                <label><input type="checkbox" name="sections[]" value="CSE-8A"> 8A</label>
                <label><input type="checkbox" name="sections[]" value="CSE-8B"> 8B</label>
                <input type="submit" value="Show Timetable">
            </form>
        </div>

        <?php
        session_start();

        // Database configuration
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "timetable_manage";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get selected sections
        $selected_sections = $_GET['sections'] ?? [];

        // Initialize an array to hold the schedule data
        $timetable = array(
            "Monday" => [],
            "Tuesday" => [],
            "Wednesday" => [],
            "Thursday" => [],
            "Friday" => [],
            "Saturday" => []
        );

        // Define time slot mapping
        $time_slot_mapping = array(
            "9AM - 10AM" => 1,
            "10AM - 11AM" => 2,
            "11AM - 12PM" => 3,
            "12PM - 1PM" => 4,
            "1PM - 2PM" => 5,
            "2PM - 3PM" => 6,
            "3PM - 4PM" => 7
        );

        // Fetch schedules for the selected sections
        if (!empty($selected_sections)) {
            // Prepare a SQL query to get schedules for selected sections
            $sections_list = "'" . implode("', '", $selected_sections) . "'";
            $sql = "SELECT schedule.*, subjects.subject_name, classes.class_name
                    FROM schedule 
                    INNER JOIN subjects ON schedule.subject_id = subjects.subject_id
                    INNER JOIN classes ON schedule.class_id = classes.class_id
                    WHERE schedule.section IN ($sections_list)
                    ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), 
                             FIELD(slot, '9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM')";

            $result = $conn->query($sql);

            // Check for SQL query errors
            if (!$result) {
                die("SQL query failed: " . $conn->error);
            }

            // Loop through the result set and populate the timetable array
            while ($row = $result->fetch_assoc()) {
                // Extract schedule details and normalize case
                $day = ucfirst(strtolower($row['day'])); // Convert to lowercase and then uppercase first character
                $time_slot = $row['slot']; // Time slot from the database

                $subject_name = $row['subject_name'];
                $class_name = $row['class_name'];
                $assistant_id = $row['assistant_id'];
                $batch = $row['batch'];
                $section = $row['section'];

                // Combine subject, class, and batch info
                $schedule_entry = $subject_name . "<br>" . $class_name;
                if (!empty($batch)) {
                    $schedule_entry .= " (Batch $batch)";
                }

                $slot_number = $time_slot_mapping[$time_slot];

                // Initialize section in timetable if not already set
                if (!isset($timetable[$day][$section])) {
                    $timetable[$day][$section] = array_fill(1, 7, []); // Initialize each section with an array of 7 empty arrays for slots
                }

                // Check if it is a lab class
                if (!is_null($assistant_id) && !is_null($batch)) {
                    // Lab class logic: Merge slots and handle batches
                    $next_slot_number = $slot_number + 1;

                    // Ensure both slots are arrays
                    if (!is_array($timetable[$day][$section][$slot_number])) {
                        $timetable[$day][$section][$slot_number] = [];
                    }
                    if (!is_array($timetable[$day][$section][$next_slot_number])) {
                        $timetable[$day][$section][$next_slot_number] = [];
                    }

                    // Add schedule entry to the current slot
                    if (!in_array($schedule_entry, $timetable[$day][$section][$slot_number])) {
                        $timetable[$day][$section][$slot_number][] = [
                            'entry' => $schedule_entry,
                            'assistant_id' => $assistant_id,
                            'batch' => $batch
                        ];
                    }

                    // Mark the next slot as part of a merged cell
                    $timetable[$day][$section][$next_slot_number] = 'merged';
                } else {
                    // Regular class logic
                    if (!is_array($timetable[$day][$section][$slot_number])) {
                        $timetable[$day][$section][$slot_number] = [];
                    }

                    $timetable[$day][$section][$slot_number][] = [
                        'entry' => $schedule_entry,
                        'assistant_id' => $assistant_id,
                        'batch' => $batch
                    ];
                }
            }

            // Close the database connection
            $conn->close();
        }

        // Render the timetable
        echo "<table>";
        echo "<tr>";
        echo "<th>Day/Slot</th>";
        $time_slots = array_keys($time_slot_mapping);
        // Output time slots as column headers
        foreach ($time_slots as $slot) {
            echo "<th>$slot</th>";
        }
        echo "</tr>";

        // Output the timetable rows
        foreach ($timetable as $day => $day_schedule) {
            // Add a row for the day
            echo "<tr><td class='day-header' colspan='" . count($time_slots) . "'>$day</td></tr>";

            // Output rows for each section
            foreach ($selected_sections as $section) {
                echo "<tr class='sub-row'>";
                echo "<td>$section</td>";

                foreach ($time_slots as $slot) {
                    $slot_number = $time_slot_mapping[$slot];

                    if (isset($day_schedule[$section][$slot_number])) {
                        $schedule_entries = $day_schedule[$section][$slot_number];

                        if ($schedule_entries === 'merged') {
                            // Skip merged slots
                            echo "<td></td>";
                            continue;
                        }

                        // Prepare the schedule output
                        $schedule_output = '';
                        foreach ($schedule_entries as $entry_data) {
                            $schedule_output .= $entry_data['entry'] . "<br>";
                        }

                        // Output the schedule
                        echo "<td>$schedule_output</td>";
                    } else {
                        // No schedule found for this slot
                        echo "<td class='empty'>-</td>";
                    }
                }

                echo "</tr>";
            }
        }

        echo "</table>";
        ?>

        <div class="back-button">
            <button onclick="goBack()">Back</button>
        </div>
    </div>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
