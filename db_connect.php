<?php
$host = "localhost";
$user = "root"; // Default XAMPP user
$pass = ""; // No password in XAMPP by default
$dbname = "wholesale_db";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
