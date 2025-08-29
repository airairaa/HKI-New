<?php
$servername = "localhost";
$username = "root"; // default XAMPP
$password = "";     // default XAMPP
$dbname = "hki"; // nama database kamu

$conn = new mysqli($servername, $username, $password, $dbname);

// Check koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
