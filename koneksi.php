<?php
// Database configuration
$servername = "localhost";
$username = "root";     // default XAMPP
$password = "";         // default XAMPP (kosong)
$dbname = "hki";        // nama database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set charset untuk mendukung UTF-8 (karakter Indonesia)
$conn->set_charset("utf8");

// Check koneksi
if ($conn->connect_error) {
    // Jangan gunakan die() untuk API endpoints karena akan mengacaukan JSON response
    // die("Connection failed: " . $conn->connect_error);
    
    // Instead, set error flag yang bisa dicek oleh file yang menginclude
    $connection_error = "Database connection failed: " . $conn->connect_error;
    
    // Log error untuk debugging
    error_log("Database Connection Error: " . $conn->connect_error);
    
    // Untuk non-API files, masih bisa gunakan die()
    // Tapi untuk API, biarkan file yang include yang handle error
    if (!defined('API_REQUEST')) {
        die("Connection failed: " . $conn->connect_error);
    }
} else {
    // Connection successful
    $connection_error = null;
    
    // Optional: Set timezone
    $conn->query("SET time_zone = '+07:00'"); // WIB timezone
}

// Function untuk test koneksi (optional)
function testConnection() {
    global $conn, $connection_error;
    
    if ($connection_error) {
        return false;
    }
    
    $result = $conn->query("SELECT 1");
    return $result !== false;
}

// Function untuk escape string (security)
function escapeString($string) {
    global $conn;
    return $conn->real_escape_string($string);
}
?>