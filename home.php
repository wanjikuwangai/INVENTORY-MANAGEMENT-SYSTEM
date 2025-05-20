<?php
// Get the active page for styling
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Wholesale Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: url('background.png') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
        }
        .navbar {
            background-color:rgba(128, 135, 165, 0.97);
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar img {
            width: 50px;
            height: auto;
        }
        .nav-links {
            list-style: none;
            display: flex;
        }
        .nav-links li {
            margin: 0 15px;
        }
        .nav-links a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 5px;
        }
        .nav-links a.active {
            background-color:rgb(0, 218, 0);
        }
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        .auth-buttons a {
            text-decoration: none;
            background-color: rgb(0, 218, 0);
            color:white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            border: 2px solid #007bff;
            transition: 0.3s;
        }
        .auth-buttons a:hover {
            background-color: #007bff;
            color: white;
        }
        .container {
            margin-top: 50px;
            font-size: 24px;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <img src="logo.png" alt="Logo">
        <ul class="nav-links">
            <li><a href="home.php" class="<?= $current_page == 'home.php' ? 'active' : '' ?>">Home</a></li>
        </ul>
        <div class="auth-buttons">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </div>
    </nav>

    <!-- Home Page Content -->
    <div class="container">
        <h1>KIKWETU DISTRIBUTORS</h1>
        <p>Refreshing Every Shelf, Powering Every Sale!</p>
    </div>

</body>
</html>