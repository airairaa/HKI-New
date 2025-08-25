<?php
session_start(); // Pastikan session dimulai

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

$judul = $_POST['judul'] ?? '';
$jenis_permohonan = $_POST['jenis_permohonan'] ?? '';
$jenis_ciptaan = $_POST['jenis_ciptaan'] ?? '';
$uraian_singkat = $_POST['uraian_singkat'] ?? '';
$tanggal_pertama_kali_diumumkan = $_POST['tanggal_pertama_kali_diumumkan'] ?? '';
$kota_pertama_kali_diumumkan = $_POST['kota_pertama_kali_diumumkan'] ?? '';
$jenis_pendanaan = $_POST['jenis_pendanaan'] ?? '';

// Ambil data dari form
$nama = $_POST['nama'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$kode_pos = $_POST['kode_pos'] ?? '';
$nomer_telepon = $_POST['nomer_telepon'] ?? '';
$fakultas = $_POST['fakultas'] ?? '';
$role = $_POST['role'] ?? '';
$email = $_POST['email'] ?? '';
$dataid = $_POST['dataid'] ?? '';

// Validasi input
if (empty($nama) || empty($alamat) || empty($kode_pos) || empty($nomer_telepon) || empty($fakultas) || empty($role) || empty($email)) {
    header('Location: ../Frontend/dataid.php?error=Data tidak lengkap');
    exit();
}

// Tambahkan ID unik ke data pengusul
$id = uniqid(); // ID unik untuk setiap pengusul

// Simpan data ke session
$_SESSION['data_pengusul'][] = [
    'id' => uniqid(), // Tambahkan ID unik
    'nama' => $_POST['nama'] ?? '',
    'alamat' => $_POST['alamat'] ?? '',
    'kode_pos' => $_POST['kode_pos'] ?? '',
    'nomer_telepon' => $_POST['nomer_telepon'] ?? '',
    'fakultas' => $_POST['fakultas'] ?? '',
    'role' => $_POST['role'] ?? '',
    'email' => $_POST['email'] ?? '',
];

// Redirect kembali ke halaman input.php
header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid) . '&success=Data berhasil ditambahkan');
exit();
?>