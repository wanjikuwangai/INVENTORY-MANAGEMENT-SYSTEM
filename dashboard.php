<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$fullname = $_SESSION['fullname'];
$role = $_SESSION['role'];

// Role-based welcome messages
$welcome_messages = [
    "manager" => "Welcome to the Management Dashboard, $fullname! Oversee all operations.",
    "store_keeper" => "Welcome, Store Keeper $fullname! Manage inventory and stock levels here.",
    "shelf_attendant" => "Hello, $fullname! Keep the shelves organized and stocked efficiently."
];

$welcome_text = $welcome_messages[$role] ?? "Welcome to your dashboard!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background: url('background2.png') no-repeat center center fixed;
            background-size: cover;
            background-color: #121212;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .navbar {
            background: rgba(128, 135, 165, 0.97);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
        }
        .navbar img {
            height: 50px;
        }
        .nav-links {
            list-style: none;
            display: flex;
        }
        .nav-links li {
            margin: 0 10px;
        }
        .nav-links a {
            text-decoration: none;
            background: #00da00; /* Green container */
            color: white; /* White text */
            font-size: 18px;
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
            display: inline-block;
        }
        .nav-links a:hover {
            background: #007bff; /* Blue on hover */
        }
        .active {
            background: #00da00;
        }
        .dashboard-container {
            text-align: center;
            margin-top: 120px;
            padding: 20px;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }
        .dashboard-container h1 {
            font-size: 28px;
            margin-bottom: 15px;
        }
        .logout-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background: red;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <div class="navbar">
        <img src="logo.png" alt="Logo">
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>

            <?php if ($role == "manager"): ?>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="sales.php">Sales</a></li>
                <li><a href="reports.php">Reports</a></li>
            <?php elseif ($role == "store_keeper"): ?>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="sales.php">Sales</a></li>
            <?php elseif ($role == "shelf_attendant"): ?>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="sales.php">Sales</a></li>
            <?php endif; ?>

        </ul>
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <h1><?php echo $welcome_text; ?></h1>
        <p>Refreshing Every Shelf, Powering Every Sale!</p>
    </div>

</body>
</html>
