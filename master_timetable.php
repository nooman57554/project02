<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Timetable</title>
    <link rel="stylesheet" href="display.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch and display sections
            $.get('fetch_sections.php', function(data) {
                $('#section-checkboxes').html(data);
            });

            // Function to fetch and update timetable
            function updateTimetable() {
                var selectedSections = [];
                $('.section-checkbox:checked').each(function() {
                    selectedSections.push($(this).val());
                });

                $.ajax({
                    url: 'fetch_t.php',
                    method: 'GET',
                    data: { sections: selectedSections },
                    success: function(data) {
                        $('#timetable').html(data);
                    }
                });
            }

            // Event listener for checkbox changes
            $(document).on('change', '.section-checkbox', function() {
                updateTimetable();
            });

            // Initial timetable load
            updateTimetable();
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Master Timetable</h2>

        <!-- Section Selection Form -->
         <div id = "section_align">
        <div id="section-checkboxes" class="section-selector">
            <!-- Checkboxes will be loaded here by AJAX -->
        </div>
        </diV>

        <!-- Timetable Display -->
        <div id="timetable">
            <!-- Timetable will be loaded here by AJAX -->
        </div>

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
