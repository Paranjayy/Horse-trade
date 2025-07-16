<?php
// Include demo mode configuration
require_once __DIR__ . '/../demo-mode.php';

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

// Try to connect to database
$conn = @mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    // If database connection fails, check if we're in demo mode
    if (isDemoMode()) {
        // Demo mode - continue without database
        $conn = null;
        error_log("Running in demo mode - no database connection");
    } else {
        // Production mode - database is required
        die("Database connection failed: " . mysqli_connect_error()); 
    }
} else {
    // Set charset to utf8 for proper character handling
    mysqli_set_charset($conn, "utf8");
}
?>