<?php
// reports.php - Reports Page
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
    $nav .= '<a href="orderslist.php"' . ($current_page == 'orderslist.php' ? ' class="active"' : '') . '>Orders List</a>';
    $nav .= '</div>';

    $nav .= '<div class="nav-link-container">';
    $nav .= '<a href="saleslist.php"' . ($current_page == 'saleslist.php' ? ' class="active"' : '') . '>Sales List</a>';
    $nav .= '</div>';

    if (in_array($role, ['Shelf Attendant', 'Store Keeper', 'Manager'])) {
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

$current_page = basename($_SERVER['PHP_SELF']);

// Fetch inventory data
$inventory_query = "SELECT 
                        id,
                        product_name,
                        quantity
                    FROM inventory
                    ORDER BY quantity DESC";

$inventory_result = $conn->query($inventory_query);
$inventory_data = [];

while ($row = $inventory_result->fetch_assoc()) {
    $inventory_data[] = $row;
}

// Fetch sales data to calculate current demand
$sales_query = "SELECT 
                    product_id,
                    SUM(quantity_sold) AS total_sold
                FROM sales
                WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                GROUP BY product_id";

$sales_result = $conn->query($sales_query);
$current_demand = [];

while ($row = $sales_result->fetch_assoc()) {
    $current_demand[$row['product_id']] = $row['total_sold'];
}

// Calculate predictions
$predictions = [];
foreach ($inventory_data as $item) {
    $product_name = $item['product_name'];
    $current_stock = $item['quantity'];
    $current_demand_value = $current_demand[$item['id']] ?? 0; // Use 0 if no demand data
    $expected_demand = $current_demand_value * 1.1; // Example: 10% increase
    $recommended_restock = max(0, $expected_demand - $current_stock); // Ensure non-negative
    $predictions[] = [
        'product_name' => $product_name,
        'current_stock' => $current_stock,
        'current_demand' => $current_demand_value,
        'expected_demand' => round($expected_demand),
        'recommended_restock' => round($recommended_restock),
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Drink Brand Inventory Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: url('background2.png') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            color: #fff;
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
            max-width: 1200px; /* Increased to accommodate side-by-side layout */
            text-align: center;
            display: flex; /* Use flexbox for side-by-side layout */
            gap: 20px; /* Add space between the pie chart and table */
            justify-content: center;
            align-items: flex-start; /* Align items to the top */
        }
        .chart-container {
            flex: 1; /* Take up 50% of the container */
            max-width: 400px; /* Limit the size of the pie chart */
            background: rgba(179, 175, 175, 0.9);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .predictions-table {
            flex: 1; /* Take up 50% of the container */
            max-width: 600px; /* Limit the size of the table */
            background: rgba(212, 208, 208, 0.9);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #333;
        }
        .predictions-table h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .predictions-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .predictions-table th, .predictions-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .predictions-table th {
            background-color: #f2f2f2;
        }
        canvas {
            width: 100% !important; /* Make the pie chart responsive */
            height: auto !important;
        }
        .graph-container {
            margin: 20px auto;
            max-width: 1200px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .graph-container > div {
            flex: 1;
            background: rgba(63, 63, 63, 0.9);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?= getNavbar($user_role, $current_page); ?>

    <div class="container">
        <!-- Pie Chart Container -->
        <div class="chart-container">
            <h1>AVAILABLE INVENTORY</h1>
            <canvas id="drinkChart" width="250" height="250"></canvas>
        </div>

        <!-- Predictions Table -->
        <div class="predictions-table">
            <h2>Inventory Predictions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Current Demand</th>
                        <th>Expected Demand (Next Month)</th>
                        <th>Recommended Restock Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($predictions as $prediction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prediction['product_name']); ?></td>
                            <td><?php echo $prediction['current_stock']; ?></td>
                            <td><?php echo $prediction['current_demand']; ?></td>
                            <td><?php echo $prediction['expected_demand']; ?></td>
                            <td><?php echo $prediction['recommended_restock']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bar and Line Charts -->
    <div class="graph-container">
        <!-- Bar Chart -->
        <div>
            <h2>Current Stock vs Current Demand</h2>
            <canvas id="barChart" width="400" height="200"></canvas>
        </div>

        <!-- Line Chart -->
        <div>
            <h2>Expected Demand Over Time</h2>
            <canvas id="lineChart" width="400" height="200"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('drinkChart').getContext('2d');
        const inventoryData = <?= json_encode($inventory_data); ?>;
        
        const maximumlyDistinctColors = [
            "#FF0000", "#00FF00", "#0000FF", "#FFFF00", "#FF00FF", "#00FFFF", "#000000", "#800000", "#008000", "#000080",
            "#808000", "#800080", "#008080", "#808080", "#FF8000", "#FF0080", "#80FF00", "#00FF80", "#0080FF", "#8000FF",
            "#FF0040", "#FF8080", "#FFFF80", "#80FF80", "#80FFFF", "#8080FF", "#FF80FF", "#4C0000", "#004C00", "#00004C",
            "#4C4C00", "#4C004C", "#004C4C", "#FFC000", "#FF40FF", "#40FFFF", "#993366", "#66CC99", "#6600CC", "#CC9966",
            "#CC0066", "#00CC66", "#6666CC", "#FFCC00", "#FF6600", "#0066FF", "#66FF00", "#E6E6FA", "#8B4513", "#556B2F",
            "#006400", "#483D8B", "#BC8F8F", "#2F4F4F", "#00CED1", "#9400D3", "#FF1493", "#00BFFF", "#696969", "#1E90FF",
            "#B22222", "#FFFAF0", "#228B22", "#DAA520", "#008000", "#ADFF2F", "#CD5C5C", "#4B0082", "#F08080", "#E0FFFF",
            "#FAFAD2", "#90EE90", "#D3D3D3", "#FFB6C1", "#20B2AA", "#87CEFA", "#778899", "#F0F8FF", "#FAEBD7"
        ];
        
        const productNames = inventoryData.map(item => item.product_name);
        const quantities = inventoryData.map(item => parseInt(item.quantity));
        const totalInventory = quantities.reduce((a, b) => a + b, 0);

        // Calculate percentages
        const percentages = quantities.map(qty => ((qty / totalInventory) * 100).toFixed(1));
        
        // Assign a unique color to each product
        const productColorMap = {};
        productNames.forEach((product, index) => {
            productColorMap[product] = maximumlyDistinctColors[index % maximumlyDistinctColors.length];
        });
        
        // Create the background colors array
        const backgroundColors = productNames.map(name => productColorMap[name]);

        // Pie Chart
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: productNames.map((name, index) => `${name} - ${percentages[index]}%`),
                datasets: [{
                    data: quantities,
                    backgroundColor: backgroundColors,
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        bodyFont: {
                            size: 16
                        },
                        titleFont: {
                            size: 16
                        },
                        callbacks: {
                            label: function(context) {
                                const index = context.dataIndex;
                                const name = productNames[index];
                                const value = context.raw || 0;
                                const percentage = percentages[index];
                                return `${name}: ${value} units (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Bar Chart (Current Stock vs Current Demand)
        const barCtx = document.getElementById('barChart').getContext('2d');
        const currentDemandValues = <?= json_encode(array_column($predictions, 'current_demand')); ?>;
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: productNames,
                datasets: [
                    {
                        label: 'Current Stock',
                        data: quantities,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Current Demand',
                        data: currentDemandValues,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 14
                            
                            }
                        }
                    },
                    tooltip: {
                        bodyFont: {
                            size: 16
                        },
                        titleFont: {
                            size: 16
                        }
                    }
                }
            }
        });

        // Line Chart (Expected Demand Over Time)
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        const expectedDemandValues = <?= json_encode(array_column($predictions, 'expected_demand')); ?>;
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: productNames,
                datasets: [
                    {
                        label: 'Expected Demand',
                        data: expectedDemandValues,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        bodyFont: {
                            size: 16
                        },
                        titleFont: {
                            size: 16
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>