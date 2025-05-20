<?php
// orderslist.php - Orders List Page
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
    $nav .= '<div class="nav-link-container">';
    $nav .= '<a href="dashboard.php"' . ($current_page == 'dashboard.php' ? ' class="active"' : '') . '>Dashboard</a>';
    $nav .= '</div>';

    $nav .= '<div class="nav-link-container">';
    $nav .= '<a href="orders.php"' . ($current_page == 'orders.php' ? ' class="active"' : '') . '>Orders</a>';
    $nav .= '</div>';

    if ($role === 'Shelf Attendant' || $role === 'Store Keeper' || $role === 'Manager') {
        $nav .= '<a href="inventory.php"' . ($current_page == 'inventory.php' ? ' class="active"' : '') . '>Inventory</a>';
    }
    if ($role === 'Manager') {
        $nav .= '<a href="reports.php"' . ($current_page == 'reports.php' ? ' class="active"' : '') . '>Reports</a>';
    }
    $nav .= '</div>';

    $nav .= '<a href="logout.php" class="logout">Logout</a>';
    $nav .= '</nav>';
    return $nav;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    if ($new_status == 'Completed') {
        // Fetch order details
        $order_stmt = $conn->prepare("SELECT vendor, product_name, quantity FROM orders WHERE id = ?");
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order = $order_result->fetch_assoc();

        // Check if the product already exists in the inventory
        $check_stmt = $conn->prepare("SELECT quantity FROM inventory WHERE product_name = ?");
        $check_stmt->bind_param("s", $order['product_name']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Product exists, update the quantity
            $row = $check_result->fetch_assoc();
            $new_quantity = $row['quantity'] + $order['quantity'];
            $update_stmt = $conn->prepare("UPDATE inventory SET quantity = ? WHERE product_name = ?");
            $update_stmt->bind_param("is", $new_quantity, $order['product_name']);
            $update_stmt->execute();
        } else {
            // Product does not exist, insert a new entry
            $insert_stmt = $conn->prepare("INSERT INTO inventory (vendor, product_name, quantity) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("ssi", $order['vendor'], $order['product_name'], $order['quantity']);
            $insert_stmt->execute();
        }

        // Delete the completed order
        $delete_stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();

        $status_message = "Order marked as completed and removed from the list.";
    } elseif ($new_status == 'Cancelled') {
        // Delete the cancelled order
        $delete_stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();
        $status_message = "Order cancelled and removed from the database.";
    } else {
        // Update order status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        $stmt->execute();
        $status_message = "Order status updated successfully.";
    }
}

// Fetch pending and in-progress orders only (excluding completed ones)
$orders_result = $conn->query("SELECT id, vendor, product_name, quantity, order_date, status FROM orders WHERE status != 'Completed'");

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Orders List</title>
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
            background-color: rgba(0, 218, 0, 0.7);
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
            border: 2px solid darkred;
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
    </style>
</head>
<body>
    <?= getNavbar($user_role, $current_page); ?>

    <div class="container">
        <h1>Orders List</h1>

        <?php if (isset($status_message)): ?>
            <div class="popup-message">
                <?= htmlspecialchars($status_message); ?>
            </div>
        <?php endif; ?>

        <table>
            <tr>
                <th>Vendor</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Status</th>
            </tr>
            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['vendor']); ?></td>
                        <td><?= htmlspecialchars($order['product_name']); ?></td>
                        <td><?= htmlspecialchars($order['quantity']); ?></td>
                        <td><?= htmlspecialchars($order['order_date']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                <select name="status">
                                    <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                <button type="submit" name="update_status">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No pending orders</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>