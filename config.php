<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "wholesale_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
