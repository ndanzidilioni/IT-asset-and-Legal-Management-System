<?php
// Database Configuration for Legal Management Services
// This file provides database connections for all legal microservices

class DatabaseConfig {
    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';  // Change this to your MySQL password
    
    // Get database connection
    public static function getConnection($database) {
        try {
            $dsn = "mysql:host=" . self::$host . ";dbname=" . $database . ";charset=utf8mb4";
            $pdo = new PDO($dsn, self::$username, self::$password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
    }
    
    // Database names for each service
    const CASE_DB = 'scheduling';  // Using main scheduling database
    const CLIENT_DB = 'scheduling';  // Using main scheduling database
    const DOCUMENT_DB = 'scheduling';
    const COURT_DB = 'scheduling';
    const BILLING_DB = 'scheduling';
    const AUTH_DB = 'scheduling';
    const CONTRACT_DB = 'scheduling';
    const COMPLIANCE_DB = 'scheduling';
    const ANALYTICS_DB = 'scheduling';
}
?>
