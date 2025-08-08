<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}
include 'koneksi.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM detail_permohonan WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
}
