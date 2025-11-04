<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '12022003';
$database = 'car_rental';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($conn, "utf8");
?>
