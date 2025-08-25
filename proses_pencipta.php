<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "hki";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Koneksi gagal: ' . $conn->connect_error]);
    exit();
}

$sql = "SELECT * FROM pencipta";  // Ambil semua data pencipta
$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
