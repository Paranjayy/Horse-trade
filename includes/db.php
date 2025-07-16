<?php
// Include demo mode configuration
require_once __DIR__ . '/../demo-mode.php';

$conn = null;

try {
    // Database configuration that works both locally and in production
    if (isset($_ENV['DATABASE_URL'])) {
        // Production environment (Render with PostgreSQL)
        $database_url = parse_url($_ENV['DATABASE_URL']);
        $host = $database_url['host'];
        $dbname = ltrim($database_url['path'], '/');
        $username = $database_url['user'];
        $password = $database_url['pass'];
        $port = $database_url['port'] ?? 5432;
        
        // PostgreSQL connection using PDO
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
    } elseif (isset($_ENV['DB_HOST'])) {
        // Production environment (custom environment variables - MySQL)
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $port = $_ENV['DB_PORT'] ?? 3306;
        
        // MySQL connection using PDO
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
    } else {
        // Local development environment (MySQL)
        $host = "localhost";
        $dbname = "horse_trading";
        $username = "root";
        $password = "";
        $port = 3306;
        
        // MySQL connection using PDO
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    
} catch (PDOException $e) {
    // If database connection fails, check if we're in demo mode
    if (isDemoMode()) {
        // Demo mode - continue without database
        $conn = null;
        error_log("Running in demo mode - no database connection: " . $e->getMessage());
    } else {
        // Production mode - database is required
        die("Database connection failed: " . $e->getMessage()); 
    }
}
?>