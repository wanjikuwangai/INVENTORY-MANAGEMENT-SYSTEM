<?php
// inventory.php - Inventory Page
session_start();
require 'connect.php';

// Ensure the database connection is valid
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch user role from session
$user_role = $_SESSION['role'] ?? 'guest';

// Navigation bar based on user role
function getNavbar($role, $current_page) {
    $nav = '<nav class="navbar">';
    $nav .= '<img src="logo.png" alt="Logo">';

    $nav .= '<div class="center-nav">';
    
    // Dashboard link with green container
    $nav .= '<div class="nav-link-container">';
    $nav .= '<a href="dashboard.php"' . ($current_page == 'dashboard.php' ? ' class="active"' : '') . '>Dashboard</a>';
    $nav .= '</div>';

    $nav .= '</div>';
    $nav .= '<a href="logout.php" class="logout">Logout</a>';
    $nav .= '</nav>';

    return $nav;
}

// Handle search
$search = $_GET['search'] ?? '';
$search_query = "SELECT * FROM inventory";
if (!empty($search)) {
    $search_query .= " WHERE product_name LIKE ?";
}

$stmt = $conn->prepare($search_query);
if (!empty($search)) {
    $like_search = "%$search%";
    $stmt->bind_param("s", $like_search);
}
$stmt->execute();
$inventory_result = $stmt->get_result();

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory Page</title>
    <style>
        body {
            background: url('background2.png') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: #fff;
        }
        .navbar {
            background-color: rgba(128, 135, 165, 0.97);
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar img {
            width: 50px;
            height: auto;
        }
        .center-nav {
            display: flex;
            justify-content: center;
            flex-grow: 1;
            align-items: center;
        }
        .center-nav a {
            margin: 0 15px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 5px;
        }
        .center-nav a.active {
            background-color: rgb(0, 218, 0);
        }
        .nav-link-container {
            background-color: rgba(0, 218, 0, 0.7); /* Light green background */
            padding: 5px 10px;
            border-radius: 5px;
            margin: 0 5px;
        }
        .logout {
            text-decoration: none;
            background-color: red;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .logout:hover {
            background-color: darkred;
        }
        .container {
            margin: 20px auto;
            max-width: 900px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            color: black;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: rgba(128, 135, 165, 0.97);
            color: white;
        }
        input[type="text"] {
            padding: 8px;
            margin-bottom: 10px;
            width: 300px;
        }
        button {
            padding: 8px 12px;
            background-color: rgb(0, 218, 0);
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?= getNavbar($user_role, $current_page); ?>

    <div class="container">
        <h1>Inventory</h1>

        <!-- Search Form -->
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by product" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Inventory Table -->
        <table>
            <tr>
                <th>Vendor</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Date Added</th>
            </tr>
            <?php if ($inventory_result->num_rows > 0): ?>
                <?php while ($row = $inventory_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['vendor']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                        <td><?= htmlspecialchars($row['date_added']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No inventory found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>