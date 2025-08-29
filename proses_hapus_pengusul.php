<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}

include '../koneksi.php';

header('Content-Type: application/json');

$id = $_POST['delete_id'] ?? '';
$role = $_POST['role'] ?? '';
$dataid = $_POST['dataid'] ?? '';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

if ($id && $role) {
    // Check if user can delete this data
    if ($user_role !== 'admin') {
        // For regular users, check if the data belongs to them
        if ($role == 'Dosen') {
            $check_query = "SELECT id FROM data_pribadi_dosen WHERE id = '$id' AND user_id = '$user_id'";
        } elseif ($role == 'Mahasiswa') {
            $check_query = "SELECT id FROM data_pribadi_mahasiswa WHERE id = '$id' AND user_id = '$user_id'";
        } else {
            echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
            exit;
        }
        
        $check_result = $conn->query($check_query);
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus data ini']);
            exit;
        }
    }
    
    if ($role == 'Dosen') {
        $query = "DELETE FROM data_pribadi_dosen WHERE id = '$id'";
    } elseif ($role == 'Mahasiswa') {
        $query = "DELETE FROM data_pribadi_mahasiswa WHERE id = '$id'";
    } else {
        echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
        exit;
    }

    if ($conn->query($query)) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus', 'dataid' => $dataid]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID atau Role tidak ditemukan']);
}
exit;
