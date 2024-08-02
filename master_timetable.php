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
    <div class="export-form" style="text-align: center; margin-top: 20px; font-family: Arial, sans-serif;">
    <form action="export_timetable.php" method="get" style="display: inline-block; background-color: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
        <input type="hidden" name="roll" value="<?php echo htmlspecialchars($role); ?>">
        <input type="hidden" name="section" value="<?php echo htmlspecialchars($section); ?>">
        <label for="format" style="margin-right: 10px;">Export as:</label>
        <select name="format" id="format" style="padding: 5px 10px; border-radius: 3px; border: 1px solid #ccc;">
            <option value="csv">CSV</option>
        </select>
        <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Export
        </button>
    </form>
</div>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
