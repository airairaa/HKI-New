<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

// Koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hki";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari session
$data_pengusul = $_SESSION['data_pengusul'] ?? [];

foreach ($data_pengusul as $pengusul) {
    $dataid = $_GET['dataid'] ?? ''; // ID pengajuan
    $id_pengusul = $pengusul['id'] ?? ''; // ID unik per pengusul
    $nama = $pengusul['nama'] ?? '';
    $alamat = $pengusul['alamat'] ?? '';
    $kode_pos = $pengusul['kode_pos'] ?? '';
    $nomer_telepon = $pengusul['nomer_telepon'] ?? '';
    $email = $pengusul['email'] ?? '';
    $fakultas = $pengusul['fakultas'] ?? '';
    $role = $pengusul['role'] ?? '';

    if (empty($dataid) || empty($id_pengusul) || empty($nama) || empty($alamat) || empty($kode_pos) || empty($nomer_telepon) || empty($email) || empty($fakultas) || empty($role)) {
        continue;
    }

    if ($role === 'Dosen') {
        $sql = "INSERT INTO data_pribadi_dosen (id_pengusul, dataid, Nama, Alamat, Kode_Pos, Nomor_Telepon, Email, Fakultas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    } else {
        $sql = "INSERT INTO data_pribadi_mahasiswa (id_pengusul, dataid, Nama, Alamat, Kode_Pos, Nomor_Telepon, Email, Fakultas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    }

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssss", $id_pengusul, $dataid, $nama, $alamat, $kode_pos, $nomer_telepon, $email, $fakultas);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();

// Bersihkan session setelah simpan
unset($_SESSION['data_pengusul']);

// Debugging path sebelum redirect
$redirect_url = '../Frontend/input.php?dataid=' . urlencode($dataid) . '&success=Data berhasil disimpan ke database';
if (!file_exists('../Frontend/input.php')) {
    die("File input.php tidak ditemukan di path: ../Frontend/input.php");
}

// Redirect
header('Location: ' . $redirect_url);
exit();
?>