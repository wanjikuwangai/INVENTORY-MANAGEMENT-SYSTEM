<?php
// sales.php - Sales Page for Offline Load Storage System
session_start();
require_once 'db_connect.php'; // Ensure this connects to your database

// Fetch products from inventory
$products = [];
$sql = "SELECT id, product_name, quantity FROM inventory";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle sales submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity_sold = intval($_POST['quantity']);

    // Validate inputs
    if ($product_id <= 0 || $quantity_sold <= 0) {
        $message = "Please select a valid product and quantity.";
    } else {
        // Check available stock
        $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($available_stock);
        $stmt->fetch();
        $stmt->close();

        if ($quantity_sold > $available_stock) {
            $message = "Insufficient stock available!";
        } else {
            // Deduct stock and record sale
            $conn->begin_transaction();

            try {
                // Update inventory
                $updateStmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE id = ?");
                $updateStmt->bind_param("ii", $quantity_sold, $product_id);
                $updateStmt->execute();

                // Insert into sales table
                $insertStmt = $conn->prepare("INSERT INTO sales (product_id, quantity_sold, sale_date) VALUES (?, ?, NOW())");
                $insertStmt->bind_param("ii", $product_id, $quantity_sold);
                $insertStmt->execute();

                $conn->commit();
                $message = "Sale recorded successfully!";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Error processing sale: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Page - Wholesale Management</title>
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center; /* Center vertically */
            align-items: center; /* Center horizontally */
        }
        .navbar {
            background-color: rgba(128, 135, 165, 0.97);
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%; /* Ensure navbar spans full width */
            position: fixed; /* Fix navbar at the top */
            top: 0;
            left: 0;
            z-index: 1000; /* Ensure navbar stays on top */
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
        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px; /* Increased padding */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px; /* Increased width */
            text-align: center;
            margin-top: 80px; /* Add margin to avoid overlap with navbar */
        }
        .form-container label {
            display: block;
            margin-bottom: 15px; /* Increased margin */
            font-weight: bold;
            font-size: 16px; /* Slightly larger font */
        }
        .form-container select,
        .form-container input {
            width: 100%;
            padding: 10px; /* Increased padding */
            margin-bottom: 20px; /* Increased margin */
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px; /* Slightly larger font */
        }
        .form-container button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px; /* Increased padding */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px; /* Slightly larger font */
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #45a049;
        }
        .notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 15px;
            background-color: white;
            color: black;
            border-radius: 5px;
            z-index: 2000;
            display: none;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        .notification.error {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <img src="logo.png" alt="Logo">
        <ul class="nav-links">
            <li class="dashboard-link-container">
                <a href="dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="saleslist.php" class="sales-link">Sales list</a>
            </li>
        </ul>
    </nav>

    <!-- Notification Popup -->
    <div id="notification" class="notification"></div>

    <!-- Sales Form -->
    <div class="form-container">
        <h1>Record a Sale</h1>
        <form method="POST" action="sales.php">
            <label for="product_id">Select Product:</label>
            <select name="product_id" id="product_id" required>
                <option value="">-- Choose a product --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>">
                        <?php echo htmlspecialchars($product['product_name']) . " (Stock: " . $product['quantity'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" required>

            <button type="submit">Submit Sale</button>
        </form>
    </div>

    <script>
        window.onload = function() {
            const message = "<?php echo $message; ?>";
            if (message) {
                const notification = document.getElementById('notification');
                notification.textContent = message;
                notification.className = 'notification' + (message.includes('Error') || message.includes('Insufficient') ? ' error' : '');
                notification.style.display = 'block';
                setTimeout(() => { notification.style.display = 'none'; }, 3000);
            }
        };
    </script>
</body>
</html>