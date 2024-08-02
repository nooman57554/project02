<?php include('header.php'); ?>

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

// Fetch subject names from subjects table
$subject_query = "SELECT * FROM subjects";
$subject_result = $conn->query($subject_query);
$subject_options = "";

if ($subject_result->num_rows > 0) {
    while ($row = $subject_result->fetch_assoc()) {
        $subject_id = $row['subject_id'];
        $subject_name = $row['subject_name'];
        $no_hrs_per_week = $row['no_hours_per_week'];
        $subject_options .= "<option value=\"$subject_name\">$subject_id - $subject_name - $no_hrs_per_week</option>";
    }
}

// Fetch teacher names from teachers table
$teacher_query = "SELECT teacher_name FROM teachers";
$teacher_result = $conn->query($teacher_query);
$teacher_options = "";

if ($teacher_result->num_rows > 0) {
    while ($row = $teacher_result->fetch_assoc()) {
        $teacher_name = $row['teacher_name'];
        $teacher_options .= "<option value=\"$teacher_name\">$teacher_name</option>";
    }
}

// Fetch class names from classes table
$class_query = "SELECT class_name FROM classes";
$class_result = $conn->query($class_query);
$class_options = "";

if ($class_result->num_rows > 0) {
    while ($row = $class_result->fetch_assoc()) {
        $class_name = $row['class_name'];
        $class_options .= "<option value=\"$class_name\">$class_name</option>";
    }
}

// Fetch section names from the Section table
$section_query = "SELECT SectionName FROM Section";
$section_result = $conn->query($section_query);
$section_options = "";

if ($section_result->num_rows > 0) {
    while ($row = $section_result->fetch_assoc()) {
        $section_name = $row['SectionName'];
        $section_options .= "<option value=\"$section_name\">$section_name</option>";
    }
}


// Fetch lab assistant names from lab_assistants table
$lab_assistant_query = "SELECT assistant_name FROM lab_assistants";
$lab_assistant_result = $conn->query($lab_assistant_query);
$lab_assistant_options = "";

if ($lab_assistant_result->num_rows > 0) {
    while ($row = $lab_assistant_result->fetch_assoc()) {
        $lab_assistant_name = $row['assistant_name'];
        $lab_assistant_options .= "<option value=\"$lab_assistant_name\">$lab_assistant_name</option>";
    }
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Timetable</title>
    <link rel="stylesheet" href="insert.css">
    
</head>
<body>
    <h1>Update Timetable</h1>

    <!-- Insert Class Form -->
    <form id="class-form" method="post" action="javascript:void(0);" onsubmit="handleFormSubmit()">
        <div id="warnings" class="warnings"></div>
        <div id="error-messages" class="error-messages"></div>
        <div id="message" class="message"></div>
        <div id="error-message" class="error-message"></div>
        <div class="checkbox-label">
            <label for="is_lab">Is it a Lab Class?:</label>
            <input type="checkbox" id="is_lab" name="is_lab" value="1" onclick="toggleLabFields()">
        </div>
        <br>
        <label for="day">Day:</label>
        <select id="day" name="day" required>
            <option value="" disabled selected>Select a day</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
        </select>
        <br>
        <label for="slot">Time Slot:</label>
        <select id="slot" name="slot" required>
            <option value="" disabled selected>Select a time slot</option>
            <option value="9AM - 10AM">9AM - 10AM</option>
            <option value="10AM - 11AM">10AM - 11AM</option>
            <option value="11AM - 12PM">11AM - 12PM</option>
            <option value="12PM - 1PM">12PM - 1PM</option>
            <option value="1PM - 2PM">1PM - 2PM</option>
            <option value="2PM - 3PM">2PM - 3PM</option>
            <option value="3PM - 4PM">3PM - 4PM</option>
        </select>
        <br>
        <label for="semester">Select Semester: </label>
        <select id="semester" name="semester" onchange="handleSemesterChange()">
            <option value="" disabled selected>Select a semester</option>
            <?php
            // Generating semester options dynamically
            for ($i = 1; $i <= 8; $i++) {
                echo "<option value=\"$i\">Semester $i</option>";
            }
            ?>
        </select>
        <label for="subject_name">Subject Name:</label>
        <select id="subject_name" name="subject_name" required>
            <option value="" disabled selected>Select a subject</option>
        <!-- Subject options will be populated here -->
        </select>
        <br>
        <label for="class_name">Class Name:</label>
        <select id="class_name" name="class_name" required>
            <option value="" disabled selected>Select a class</option>
            <?php echo $class_options; ?>
        </select>
        <br>
        <label for="teacher_name"></label>
        <select id="teacher_name" name="teacher_name" required>
            <option value="" disabled selected>Select a teacher</option>
            <?php echo $teacher_options; ?>
        </select>
        <br>
        <label for="section"></label>
        <select id="section" name="section" required>
            <option value="" disabled selected>Select a section</option>
            <?php echo $section_options; ?>
        </select>
        <br>
        <div id="batch_field" class="batch-field">
            <label for="batch"></label>
            <select id="batch" name="batch">
                <option value="" disabled selected>Select a batch</option>
                <option value="A1">A1</option>
                <option value="A2">A2</option>
                <option value="A3">A3</option>
                <option value="B1">B1</option>
                <option value="B2">B2</option>
                <option value="B3">B3</option>
            </select>
        </div>
        <br>
        <div id="lab_assistant_field" class="lab-assistant-field">
            <label for="lab_assistant_name">Lab Assistant Name:</label>
            <select id="lab_assistant_name" name="lab_assistant_name">
                <option value="" disabled selected>Select a lab assistant</option>
                <?php echo $lab_assistant_options; ?>
            </select>
        </div>
        <br>
        <button type="submit">Allot Class</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function toggleLabFields() {
        var isLabChecked = document.getElementById('is_lab').checked;
        document.getElementById('batch_field').style.display = isLabChecked ? 'block' : 'none';
        document.getElementById('lab_assistant_field').style.display = isLabChecked ? 'block' : 'none';
    }

    function handleFormSubmit() {
    var formData = $('#class-form').serialize(); // Serialize form data
    
    $('#error-messages').html('').hide();

    $.ajax({
        url: 'error_handling.php', // New PHP script to check errors
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            var hasWarnings = response.warnings.length > 0;
            var hasErrors = response.errors.length > 0;

            // Display warnings if any
            if (hasWarnings) {
                $('#warnings').html(
                    '<button class="close-btn" onclick="closeErrorMessages()">×</button>' +
                    '<ul>' + response.warnings.map(warning => `<li>${warning}</li>`).join('') + '</ul>'
                ).show();
            }

            // Display errors if any
            if (hasErrors) {
                $('#error-messages').html(
                    '<button class="close-btn" onclick="closeErrorMessages()">×</button>' +
                    '<ul>' + response.errors.map(error => `<li>${error}</li>`).join('') + '</ul>'
                ).show();
                return; // Stop further processing if there are errors
            }

            // Wait for a short period to ensure warnings are visible before showing confirm dialogs
            setTimeout(() => {
                var proceed1 = true;
                var proceed2 = true;

                // Check for consecutive classes
                if (response.consecutive) {
                    proceed1 = confirm('The teacher has a consecutive class. Do you want to continue?');
                    if (!proceed1) {
                        $('#message').text('The recent request was cancelled.').css('color', 'orange');
                        $('#warnings').html('').hide();
                        return;
                        
                    }
                }

                // Check for exceeding hours
                if (response.hours_exceeded) {
                    proceed2 = confirm('Maximum allowed hours reached for this section, continue?');
                    if (!proceed2) {
                        $('#message').text('The recent request was cancelled.').css('color', 'orange');
                        $('#warnings').html('').hide();
                        return;
                    }
                }

                // Proceed if allowed
                if (proceed1 && proceed2) {
                    insertClass(formData);
                    $('#warnings').html('').hide();
                }
            }, 100); // 100 ms delay to allow warnings to render
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Full Response:', jqXHR.responseText);
            $('#error-message').text('Error checking for errors: ' + textStatus + ' - ' + errorThrown).css('color', 'red');
        }
    });
}

function insertClass(formData) {
    $.ajax({
        url: 'insert_class.php', // Your existing insert script
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#message').text(response.message).css('color', 'green');
                $('#error-message').text('');
                document.getElementById('class-form').reset();
            } else {
                $('#error-message').text(response.error).css('color', 'red');
                $('#message').text('');
                document.getElementById('class-form').reset();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Full Response:', jqXHR.responseText);
            $('#error-message').text('Error inserting class: ' + textStatus + ' - ' + errorThrown).css('color', 'red');
            document.getElementById('class-form').reset();
        }
    });
}


function handleSemesterChange() {
            var semesterSelect = document.getElementById("semester");
            var selectedSemester = semesterSelect.value;

            // Make an AJAX request to the sem.php file
            fetch('sem.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ semester: selectedSemester })
            })
            .then(response => response.json())
            .then(data => {
                // Process the received data (subject variables)
                onSemesterSelected(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function onSemesterSelected(data) {
    // Get the subject dropdown element
    var subjectSelect = document.getElementById("subject_name");

    // Clear previous options
    subjectSelect.innerHTML = '<option value="" disabled selected>Select a subject</option>';

    // Populate the dropdown with new subjects
    data.subjects.forEach(subject => {
        var option = document.createElement("option");
        option.value = subject;
        option.textContent = subject;
        subjectSelect.appendChild(option);
    });
}

</script>
</body>
</html>