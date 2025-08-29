<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Akses tidak diizinkan. Silakan login terlebih dahulu.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

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
    echo json_encode(['error' => true, 'message' => 'Koneksi gagal: ' . $conn->connect_error]);
    exit();
}

// Ambil data pengusul
$data = [];
if ($dataid) {
    // Admin can see all data, regular users can only see their own data
    if ($user_role === 'admin') {
        $sql_dosen = "SELECT id, nama, alamat, kode_pos, nomor_telepon, email, fakultas, 'Dosen' as role FROM data_pribadi_dosen WHERE dataid = ?";
        $stmt_dosen = $conn->prepare($sql_dosen);
        $stmt_dosen->bind_param("s", $dataid);
        $stmt_dosen->execute();
        $result_dosen = $stmt_dosen->get_result();
        while ($row = $result_dosen->fetch_assoc()) {
            $data[] = $row;
        }

        $sql_mahasiswa = "SELECT id, nama, alamat, kode_pos, nomor_telepon, email, fakultas, 'Mahasiswa' as role FROM data_pribadi_mahasiswa WHERE dataid = ?";
        $stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
        $stmt_mahasiswa->bind_param("s", $dataid);
        $stmt_mahasiswa->execute();
        $result_mahasiswa = $stmt_mahasiswa->get_result();
        while ($row = $result_mahasiswa->fetch_assoc()) {
            $data[] = $row;
        }
    } else {
        // Regular users can only see their own data
        $sql_dosen = "SELECT id, nama, alamat, kode_pos, nomor_telepon, email, fakultas, 'Dosen' as role FROM data_pribadi_dosen WHERE dataid = ? AND user_id = ?";
        $stmt_dosen = $conn->prepare($sql_dosen);
        $stmt_dosen->bind_param("si", $dataid, $user_id);
        $stmt_dosen->execute();
        $result_dosen = $stmt_dosen->get_result();
        while ($row = $result_dosen->fetch_assoc()) {
            $data[] = $row;
        }

        $sql_mahasiswa = "SELECT id, nama, alamat, kode_pos, nomor_telepon, email, fakultas, 'Mahasiswa' as role FROM data_pribadi_mahasiswa WHERE dataid = ? AND user_id = ?";
        $stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
        $stmt_mahasiswa->bind_param("si", $dataid, $user_id);
        $stmt_mahasiswa->execute();
        $result_mahasiswa = $stmt_mahasiswa->get_result();
        while ($row = $result_mahasiswa->fetch_assoc()) {
            $data[] = $row;
        }
    }
}

echo json_encode(['success' => true, 'data' => $data]);
$conn->close();
?>