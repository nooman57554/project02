<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>header</title>
    <style>
        
        body {
            font-family: Arial, sans-serif; 
            background-color: #f4f4f4; 
            margin: 0; 
            padding: 0; 
        }

        header {
            background-color: #997f5e;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        nav ul li {
            display: inline;
            margin-right: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            position: relative;
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2); 
            border-radius: 5px;
            padding: 5px 10px;
        }

        .logout-btn {
            background-color: #d9534f;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <header>
        <span>CSE-A</span>
        <nav>
            <ul>
                <li><a href="admin_home.php">Home</a></li>
                <li><a href="admin_home.php">Options</a></li>
               
            </ul>
        </nav>
        <a href="logout.php" class="logout-btn">Log out</a>
    </header>
</body>
</html>
