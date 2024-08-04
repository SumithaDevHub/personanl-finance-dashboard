<?php
// Database configuration
$host = "localhost";
$db_name = "finance_dashboard";
$username = "root";
$password = "root19"; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Set PDO to throw exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set character set to utf8mb4 (optional, adjust as per your encoding needs)
    $conn->exec("SET NAMES utf8mb4");
} catch(PDOException $exception) {
    // Handle connection errors
    die("Connection failed: " . $exception->getMessage());
}
?>
