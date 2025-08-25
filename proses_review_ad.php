<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}
include 'koneksi.php';

// Proses update status dan upload sertifikat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $sertifikatFile = null;

    if (isset($_FILES['sertifikat']) && $_FILES['sertifikat']['error'] === 0) {
        $ext = pathinfo($_FILES['sertifikat']['name'], PATHINFO_EXTENSION);
        $sertifikatFile = 'uploads/sertifikat_' . $id . '.' . $ext;
        move_uploaded_file($_FILES['sertifikat']['tmp_name'], '../' . $sertifikatFile);
    }

    $check = $conn->query("SELECT * FROM review_ad WHERE detailpermohonan_id = '$id'");
    if ($check->num_rows > 0) {
        if ($sertifikatFile) {
            $stmt = $conn->prepare("UPDATE review_ad SET status = ?, sertifikat = ? WHERE detailpermohonan_id = ?");
            $stmt->bind_param("ssi", $status, $sertifikatFile, $id);
        } else {
            $stmt = $conn->prepare("UPDATE review_ad SET status = ? WHERE detailpermohonan_id = ?");
            $stmt->bind_param("si", $status, $id);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO review_ad (status, sertifikat, detailpermohonan_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $status, $sertifikatFile, $id);
    }
    $stmt->execute();
    $stmt->close();
    exit;
}

// Ambil data permohonan
$sql = "
    SELECT dp.id, dp.judul, up.file_ktp, up.file_contoh_karya, up.file_sp, up.file_sph, up.file_bukti_pembayaran,
           ra.status, ra.sertifikat
    FROM detail_permohonan dp
    LEFT JOIN uploads up ON dp.dataid = up.dataid
    LEFT JOIN review_ad ra ON dp.id = ra.detailpermohonan_id
    ORDER BY dp.id DESC
";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
