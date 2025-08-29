<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hki";

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => true, 'message' => 'Koneksi gagal: ' . $conn->connect_error]));
}

// Ambil data untuk preview
$sql = "SELECT * FROM detail_permohonan ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => [
            'judul' => $data['judul'] ?? 'Tidak tersedia',
            'jenis_ciptaan' => $data['jenis_ciptaan'] ?? 'Tidak tersedia',
            'tanggal' => $data['created_at'] ?? 'Tidak tersedia',
            'pembayaran' => 'Rp. 200.000,-',
            'rekening' => [
                'BCA' => '###11',
                'BNI' => '333##',
                'BRI' => '66###'
            ]
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
}

$conn->close();
?>