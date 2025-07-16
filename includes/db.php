<?php
// Database configuration that works both locally and in production
if (isset($_ENV['DB_HOST'])) {
    // Production environment (Vercel)
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];
} else {
    // Local development environment
    $host = "localhost";
    $dbname = "horse_trading";
    $username = "root";
    $password = "";
}

$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) { 
    die("Connection failed: " . mysqli_connect_error()); 
}

// Set charset to utf8 for proper character handling
mysqli_set_charset($conn, "utf8");
?>