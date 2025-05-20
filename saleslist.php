<?php
// saleslist.php - Displays a list of all sales with search and delete functionality
session_start();
require_once 'db_connect.php'; // Ensure database connection

// Get the active page for styling
$current_page = basename($_SERVER['PHP_SELF']);

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        // Redirect to prevent form resubmission
        header("Location: saleslist.php?delete_success=1");
        exit();
    }
    $delete_stmt->close();
}

// Handle search input
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch sales with product names
$sql = "SELECT s.id, i.product_name, s.quantity_sold, s.sale_date FROM sales s JOIN inventory i ON s.product_id = i.id";
if ($search) {
    $sql .= " WHERE i.product_name LIKE ? OR s.sale_date LIKE ?";
}
$stmt = $conn->prepare($sql);
if ($search) {
    $likeSearch = "%$search%";
    $stmt->bind_param("ss", $likeSearch, $likeSearch);
}
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales List - Wholesale Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-image: url('background2.png');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            text-align: center;
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
        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
            align-items: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .nav-links li {
            display: flex;
        }
        .nav-links a {
            text-decoration: none;
            color: white;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .nav-links a:hover {
            background-color: rgba(255,255,255,0.2);
        }
        .dashboard-link-container {
            display: flex;
            align-items: center;
            background-color: rgba(0, 218, 0, 0.7);
            padding: 5px 10px;
            border-radius: 5px;
        }
        .sales-link {
            background-color: rgba(0, 218, 0, 0.7);
            padding: 8px 12px;
            border-radius: 5px;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        h1 {
            color: white;
            margin: 20px 0;
        }
        .content {
            background-color: rgba(255, 255, 255, 0.9);
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 800px;
        }
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .search-form input {
            flex-grow: 1;
            max-width: 300px;
            padding: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .delete-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        .delete-btn:hover {
            background-color: #ff3333;
        }
        /* Custom Confirmation Modal */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-align: center;
        }
        .confirmation-modal p {
            margin-bottom: 20px;
            font-size: 16px;
        }
        .confirmation-modal button {
            padding: 8px 16px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .confirmation-modal button.confirm {
            background-color: #4CAF50;
            color: white;
        }
        .confirmation-modal button.cancel {
            background-color: #f44336;
            color: white;
        }
    </style>
    <script>
        let saleIdToDelete = null;

        function confirmDelete(saleId) {
            saleIdToDelete = saleId;
            document.getElementById('confirmationModal').style.display = 'block';
        }

        function deleteConfirmed() {
            if (saleIdToDelete) {
                window.location.href = 'saleslist.php?delete=' + saleIdToDelete;
            }
        }

        function cancelDelete() {
            document.getElementById('confirmationModal').style.display = 'none';
        }

        // Show popup on page load if delete was successful
        window.onload = function() {
            <?php if (isset($_GET['delete_success'])): ?>
                document.getElementById('successPopup').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('successPopup').style.display = 'none';
                }, 3000); // Hide after 3 seconds
            <?php endif; ?>
        }
    </script>
</head>
<body>
    <!-- Success Popup -->
    <div id="successPopup" class="popup">
        Sale record deleted successfully.
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <p>Are you sure you want to delete this entry?</p>
        <button class="confirm" onclick="deleteConfirmed()">Yes</button>
        <button class="cancel" onclick="cancelDelete()">No</button>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <img src="logo.png" alt="Logo">
        <ul class="nav-links">
            <li class="dashboard-link-container">
                <a href="dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="sales.php" class="sales-link">Sales</a>
            </li>
        </ul>
    </nav>

    <h1>Sales List</h1>

    <div class="content">
        <!-- Search Form -->
        <form class="search-form" method="GET" action="saleslist.php">
            <input type="text" name="search" placeholder="Search by product or date" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Sales Table -->
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity Sold</th>
                    <th>Sale Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sales) > 0): ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                            <td><?php echo $sale['quantity_sold']; ?></td>
                            <td><?php echo $sale['sale_date']; ?></td>
                            <td>
                                <button class="delete-btn" onclick="confirmDelete(<?php echo $sale['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No sales found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>