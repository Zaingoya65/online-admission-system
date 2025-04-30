<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u438663390_hijrah_DB');
define('DB_PASS', '1234..@Zaingoya');
define('DB_NAME', 'u438663390_hijrah_DB');

// Connect to database
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}