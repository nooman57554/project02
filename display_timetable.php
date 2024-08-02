<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        td {
            background-color: #fff;
        }

        td.empty {
            background-color: #f7f7f7; /* Lighter shade for empty cells */
        }

        .back-button {
            text-align: center;
            margin-top: 20px;
        }

        .back-button button {
            padding: 10px 20px;
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-button button:hover {
            background-color: #45a049; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <div class="container">
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

        $role = $_GET['roll'] ?? null;
      
if ($role) {
    echo "<h2>Timetable - $role</h2>";
    // Query for teacher's timetable
    $sql = "SELECT schedule.*, subjects.subject_name, classes.class_name
            FROM schedule 
            INNER JOIN subjects ON schedule.subject_id = subjects.subject_id
            INNER JOIN classes ON schedule.class_id = classes.class_id
            INNER JOIN teachers ON schedule.teacher_id = teachers.teacher_id
            WHERE teachers.teacher_name = '$role'
           
            ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
                     FIELD(slot, '9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM')";
}
else{
    
        
        $section = $_GET['section'] ?? 'CSE-A'; 
        echo "<h2>Timetable - $section</h2>";
        $sql = "SELECT schedule.*, subjects.subject_name, classes.class_name
                FROM schedule 
                INNER JOIN subjects ON schedule.subject_id = subjects.subject_id
                INNER JOIN classes ON schedule.class_id = classes.class_id
                WHERE schedule.section = '$section'
                ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), FIELD(slot, '9AM - 10AM', '10AM - 11AM', '11AM - 12PM', '12PM - 1PM', '1PM - 2PM', '2PM - 3PM', '3PM - 4PM')";
}

        $result = $conn->query($sql);

        // Check for SQL query errors
        if (!$result) {
            die("SQL query failed: " . $conn->error);
        }

        // Initialize an array to hold the schedule data
        $timetable = array(
            "Monday" => array_fill(1, 7, []), // Initialize each day with an array of 7 empty arrays for slots
            "Tuesday" => array_fill(1, 7, []),
            "Wednesday" => array_fill(1, 7, []),
            "Thursday" => array_fill(1, 7, []),
            "Friday" => array_fill(1, 7, [])
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

        // Loop through the result set and populate the timetable array
        while ($row = $result->fetch_assoc()) {
            // Extract schedule details and normalize case
            $day = ucfirst(strtolower($row['day'])); // Convert to lowercase and then uppercase first character
            $time_slot = $row['slot']; // Time slot from the database

            $subject_name = $row['subject_name'];
            $class_name = $row['class_name'];
            $assistant_id = $row['assistant_id'];
            $batch = $row['batch'];

            // Combine subject, class, and batch info
            $schedule_entry = $subject_name . "<br>" . $class_name;
            if (!empty($batch)) {
                $schedule_entry .= " (Batch $batch)";
            }
           
            $slot_number = $time_slot_mapping[$time_slot];

            // Check if it is a lab class
            if (!is_null($assistant_id) && !is_null($batch)) {
                // Lab class logic: Merge slots and handle batches
                $next_slot_number = $slot_number + 1;

                // Ensure both slots are arrays
                if (!is_array($timetable[$day][$slot_number])) {
                    $timetable[$day][$slot_number] = [];
                }
                if (!is_array($timetable[$day][$next_slot_number])) {
                    $timetable[$day][$next_slot_number] = [];
                }

                // Add schedule entry to the current slot
                if (!in_array($schedule_entry, $timetable[$day][$slot_number])) {
                    $timetable[$day][$slot_number][] = [
                        'entry' => $schedule_entry,
                        'assistant_id' => $assistant_id,
                        'batch' => $batch
                    ];
                }

                // Mark the next slot as part of a merged cell
                $timetable[$day][$next_slot_number] = 'merged';
            } else {
                // Regular class logic
                if (!is_array($timetable[$day][$slot_number])) {
                    $timetable[$day][$slot_number] = [];
                }

                $timetable[$day][$slot_number][] = [
                    'entry' => $schedule_entry,
                    'assistant_id' => $assistant_id,
                    'batch' => $batch
                ];
            }
        }

        // Close the database connection
        $conn->close();

        // Display timetable
        echo "<table border='1'>";
        echo "<tr><th>Day</th>";

        // Define time slots
        $time_slots = array_keys($time_slot_mapping);
        foreach ($time_slots as $time_slot) {
            echo "<th>$time_slot</th>";
        }
        echo "</tr>";

        // Loop through each day to display the timetable
        foreach ($timetable as $day => $slots) {
            echo "<tr><td>$day</td>";
            for ($slot_number = 1; $slot_number <= count($time_slots); $slot_number++) {
                
                if ($slots[$slot_number] === 'merged') {
                    echo "<td></td>";
                    continue; 
                }

                if (!empty($slots[$slot_number])) {
                    //
                    $schedules = array_map(function ($slot) {
                        return $slot['entry'];
                    }, $slots[$slot_number]);

                    $schedules_str = implode("<br>", $schedules);

                    
                    $colspan = (!is_null($slots[$slot_number][0]['assistant_id']) && !is_null($slots[$slot_number][0]['batch'])) ? 2 : 1;

                    echo "<td colspan='$colspan'>$schedules_str</td>";

                    if ($colspan === 2) {
                        // Skip the next slot as it's part of the merged cell
                        $slot_number++;
                    }
                } else {
                    echo "<td class='empty'>-</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
        ?>
    </div>

    <!-- Back Button -->
    <div class="back-button">
        <button onclick="goBack()">Back</button>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
