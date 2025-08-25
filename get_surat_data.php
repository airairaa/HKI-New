<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}

$host = "localhost";
$user = "root"; // Ganti dengan user database
$pass = ""; // Ganti dengan password database
$dbname = "hki";

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => true, 'message' => 'Koneksi ke database gagal: ' . $conn->connect_error]));
}

// Ambil data surat terakhir
$sql = "SELECT * FROM detail_permohonan ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => [
            'jenis_ciptaan' => $data['jenis_ciptaan'] ?? 'Tidak tersedia',
            'judul' => $data['judul'] ?? 'Tidak tersedia',
            'jenis_pendanaan' => $data['jenis_pendanaan'] ?? 'Tidak tersedia'
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}

$conn->close();
?>