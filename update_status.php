<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}
include 'koneksi.php';

if (isset($_POST['id'], $_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Upload sertifikat jika ada
    if (!empty($_FILES['sertifikat']['name'])) {
        $file_name = $_FILES['sertifikat']['name'];
        move_uploaded_file($_FILES['sertifikat']['tmp_name'], "../uploads/$file_name");

        $stmt = $conn->prepare("UPDATE review_ad SET status=?, sertifikat=? WHERE id=?");
        $stmt->bind_param("ssi", $status, $file_name, $id);
    } else {
        $stmt = $conn->prepare("UPDATE review_ad SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
    }
    $stmt->execute();
    echo json_encode(['success' => true]);
}
