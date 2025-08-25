<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan. Hanya admin yang dapat mengakses halaman ini.']);
    exit();
}
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idp = $_POST['id_permohonan'];
    $status = $conn->real_escape_string($_POST['status']);

    // Gunakan prepared statement untuk update status
    $stmt = $conn->prepare("UPDATE review_ad SET status = ? WHERE id_permohonan = ?");
    $stmt->bind_param("ss", $status, $idp);
    $stmt->execute();

    // Jika status adalah "Terdaftar" dan file sertifikat diunggah
    if ($status === 'Terdaftar' && !empty($_FILES['sertifikat']['tmp_name'])) {
        $file = addslashes(file_get_contents($_FILES['sertifikat']['tmp_name']));

        // Gunakan prepared statement untuk update sertifikat
        $stmt = $conn->prepare("UPDATE review_ad SET sertifikat = ? WHERE id_permohonan = ?");
        $stmt->bind_param("ss", $file, $idp);
        $stmt->execute();
    }

    // Redirect ke halaman review_ad
    header('Location: ../frontend/review_ad.php');
    exit();
}
?>