<?php
// orders.php - Orders Page
session_start();
require 'connect.php';

// Ensure the database connection is valid
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if the table exists and query is valid
if (!$conn->query("DESCRIBE orders")) {
    die("Table check failed: " . $conn->error);
}

// Fetch user role from session
$user_role = $_SESSION['role'] ?? 'guest';

// Navigation bar based on user role
function getNavbar($role, $current_page) {
    $nav = '<nav class="navbar">';
    $nav .= '<img src="logo.png" alt="Logo">';

    $nav .= '<div class="center-nav">';
    // Dashboard link with light green container
    $nav .= '<div class="dashboard-container">';
    $nav .= '<a href="dashboard.php"' . ($current_page == 'dashboard.php' ? ' class="active"' : '') . '>Dashboard</a>';
    $nav .= '</div>';

    if ($role === 'Shelf Attendant') {
        $nav .= '<a href="inventory.php"' . ($current_page == 'inventory.php' ? ' class="active"' : '') . '>Inventory</a>';
    } elseif ($role === 'Store Keeper') {
        $nav .= '<a href="orders.php"' . ($current_page == 'orders.php' ? ' class="active"' : '') . '>Orders</a>';
        $nav .= '<a href="inventory.php"' . ($current_page == 'inventory.php' ? ' class="active"' : '') . '>Inventory</a>';
    } elseif ($role === 'Manager') {
        $nav .= '<a href="orders.php"' . ($current_page == 'orders.php' ? ' class="active"' : '') . '>Orders</a>';
        $nav .= '<a href="inventory.php"' . ($current_page == 'inventory.php' ? ' class="active"' : '') . '>Inventory</a>';
        $nav .= '<a href="reports.php"' . ($current_page == 'reports.php' ? ' class="active"' : '') . '>Reports</a>';
    }
    $nav .= '</div>';

    $nav .= '<a href="logout.php" class="logout">Logout</a>';
    $nav .= '</nav>';
    return $nav;
}

$order_success_message = "";

// Handle new order submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_order'])) {
    $vendor = $_POST['vendor'];
    $product = $_POST['product'];
    $quantity = max(1, (int)$_POST['quantity']); // Ensure quantity is at least 1

    $stmt = $conn->prepare("INSERT INTO orders (vendor, product_name, quantity, order_date, status) VALUES (?, ?, ?, CURDATE(), 'Pending')");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssi", $vendor, $product, $quantity);

    if ($stmt->execute()) {
        $order_success_message = "Order placed successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders Page</title>
    <style>
        body {
            background: url('background2.png') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
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
        .dashboard-container {
            background-color: rgb(0, 218, 0);
            padding: 5px 10px;
            border-radius: 5px;
        }
        .logout {
            text-decoration: none;
            background-color: red;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            border: 2px solid darkred;
        }
        .logout:hover {
            background-color: darkred;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            box-sizing: border-box;
        }
        .form-title {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .small-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }
        .small-link:hover {
            text-decoration: underline;
        }
        .popup-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgb(230, 230, 230);
            color: black; /* Changed to black for better visibility */
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            animation: fadeOut 2.5s ease-in-out 2s forwards;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popupMessage = document.querySelector('.popup-message');
            if (popupMessage) {
                setTimeout(() => {
                    popupMessage.remove();
                }, 4500);
            }
        });
    </script>
</head>
<body>
    <?= getNavbar($user_role, $current_page); ?>

    <div class="container">
        <h2 class="form-title">Place a New Order</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="vendor">Vendor</label>
                <input type="text" id="vendor" name="vendor" required>
            </div>

            <div class="form-group">
                <label for="product">Product</label>
                <input type="text" id="product" name="product" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="1" required>
            </div>

            <button type="submit" name="add_order" class="btn">Place Order</button>
        </form>

        <a href="orderslist.php" class="small-link">View Existing Orders</a>
    </div>

    <?php if ($order_success_message): ?>
        <div class="popup-message">
            <?= htmlspecialchars($order_success_message); ?>
        </div>
    <?php endif; ?>
</body>
</html>