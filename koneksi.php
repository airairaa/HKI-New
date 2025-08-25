<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hki"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
