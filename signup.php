<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            width: 300px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"],
        button {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        input[type="radio"] + label {
            margin-right: 15px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body onload="clearFields()">
    <div class="signup-container">
        <h2>Sign Up</h2>
        <form action="register.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
	    <input type="password" id="password" name="password" required>
	 <br>
	    <input type="checkbox" id="showPassword"> Show Password<br><br>
            <div class="input-group">
                <input type="radio" id="admin-signup" name="role" value="admin" required>
                <label for="admin-signup">Admin</label>
                <input type="radio" id="student-signup" name="role" value="student" required>
                <label for="student-signup">Student</label>
            </div>
            <div id="signup-error-message" class="error-message">
                <?php if(isset($_GET['signup_error_message'])) { echo $_GET['signup_error_message']; } ?>
            </div>
            <button type="submit">Sign Up</button>
        </form>
    </div>

<script>        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordField = document.getElementById('password');
            passwordField.type = this.checked ? 'text' : 'password';
        });
        function clearFields() {
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
        }
    </script>
</body>
</html>

