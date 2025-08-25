<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}

if (!isset($_SESSION['dataid'])) {
    $_SESSION['dataid'] = time() . rand(100, 999);
}
$dataid = isset($_GET['dataid']) ? $_GET['dataid'] : $_SESSION['dataid'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hki";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => true, 'message' => 'Koneksi gagal: ' . $conn->connect_error]));
}

// Proses penghapusan data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $role = $_GET['role'];

    if ($role == 'Dosen') {
        $sql_delete = "DELETE FROM data_pribadi_dosen WHERE id = ?";
    } else {
        $sql_delete = "DELETE FROM data_pribadi_mahasiswa WHERE id = ?";
    }

    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);

    if ($stmt_delete->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus data']);
    }
    $stmt_delete->close();
    $conn->close();
    exit();
}

// Ambil data pengusul
$data = [];
if ($dataid) {
    $sql_dosen = "SELECT id, Nama, Alamat, Kode_Pos, Nomor_Telepon, Email, Fakultas, 'Dosen' as role FROM data_pribadi_dosen WHERE dataid = ?";
    $stmt_dosen = $conn->prepare($sql_dosen);
    $stmt_dosen->bind_param("s", $dataid);
    $stmt_dosen->execute();
    $result_dosen = $stmt_dosen->get_result();
    while ($row = $result_dosen->fetch_assoc()) {
        $data[] = $row;
    }

    $sql_mahasiswa = "SELECT id, Nama, Alamat, Kode_Pos, Nomor_Telepon, Email, Fakultas, 'Mahasiswa' as role FROM data_pribadi_mahasiswa WHERE dataid = ?";
    $stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
    $stmt_mahasiswa->bind_param("s", $dataid);
    $stmt_mahasiswa->execute();
    $result_mahasiswa = $stmt_mahasiswa->get_result();
    while ($row = $result_mahasiswa->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $data]);
$conn->close();
?>