<?php include('header.php'); ?>

<?php
session_start();

// Ensure admin is logged in
$admin_id = $_SESSION['user_id'] ?? null;
if (!$admin_id) {
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

// Fetch sections
$section_query = "SELECT SectionName FROM Section";
$section_result = $conn->query($section_query);

// Fetch all teachers
$teacher_query = "SELECT teacher_id, teacher_name FROM teachers";
$teacher_result = $conn->query($teacher_query);
$teachers = [];
while ($row = $teacher_result->fetch_assoc()) {
    $teachers[$row['teacher_id']] = $row['teacher_name'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Teachers to Subjects</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Assign Teachers to Subjects</h1>

    <form id="assignment-form" method="post" action="generate_timetable.php">
        <label for="section">Select Section:</label>
        <select id="section" name="section" required>
            <option value="" disabled selected>Select a section</option>
            <?php while ($row = $section_result->fetch_assoc()): ?>
                <option value="<?php echo $row['SectionName']; ?>"><?php echo $row['SectionName']; ?></option>
            <?php endwhile; ?>
        </select>
        <br>

        <div id="subject-teacher-assignment"></div>

        <button type="submit">Generate Timetable</button>
    </form>

    <script>
        $(document).ready(function() {
            $('#section').change(function() {
                var section = $(this).val();
                if (section) {
                    $.ajax({
                        url: 'fetch_subjects.php',
                        type: 'POST',
                        data: { section: section },
                        dataType: 'json',
                        success: function(response) {
                            var subjectAssignments = '';

                            // Display subjects and teacher selection
                            response.subjects.forEach(function(subject) {
                                subjectAssignments += `
                                    <div>
                                        <label>${subject.subject_name}:</label>
                                        <select name="teachers[${subject.subject_id}]" required>
                                            <option value="" disabled selected>Select a teacher</option>
                                            <?php foreach ($teachers as $teacher_id => $teacher_name): ?>
                                                <option value="<?php echo $teacher_id; ?>"><?php echo $teacher_name; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>`;
                            });

                            $('#subject-teacher-assignment').html(subjectAssignments);
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
