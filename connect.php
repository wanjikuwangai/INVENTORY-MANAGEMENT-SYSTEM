<?php
// connect.php - Database connection
$host = 'localhost';
$user = 'root'; // Default XAMPP MySQL user
$password = ''; // Default is empty in XAMPP
$database = 'wholesale_db';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
