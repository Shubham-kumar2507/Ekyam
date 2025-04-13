<?php
// Database configuration
$host = 'localhost';
$dbname = 'ekyam_db';
$username = 'root';
$password = '';

// Create MySQLi connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check MySQLi connection
if (!$conn) {
    die("MySQLi Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for MySQLi
mysqli_set_charset($conn, "utf8mb4");

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}
?> 