<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hki";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $role = $_POST['role'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $kode_pos = $_POST['kode_pos'] ?? '';
    $nomor_telepon = $_POST['nomor_telepon'] ?? '';
    $email = $_POST['email'] ?? '';
    $fakultas = $_POST['fakultas'] ?? '';

    $dataid = $_POST['dataid'] ?? '';
    
    if (!$id || !$role || empty($nama) || empty($alamat) || empty($kode_pos) || empty($nomor_telepon) || empty($email) || empty($fakultas)) {
        header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid) . '&error=Data tidak lengkap');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];
    
    // Check if user can edit this data
    if ($user_role !== 'admin') {
        // For regular users, check if the data belongs to them
        if ($role === 'Dosen') {
            $check_sql = "SELECT id FROM data_pribadi_dosen WHERE id = ? AND user_id = ?";
        } else {
            $check_sql = "SELECT id FROM data_pribadi_mahasiswa WHERE id = ? AND user_id = ?";
        }
        
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid) . '&error=Anda tidak memiliki akses untuk mengedit data ini');
            exit();
        }
        $check_stmt->close();
    }
    
    if ($role === 'Dosen') {
        $sql = "UPDATE data_pribadi_dosen SET nama = ?, alamat = ?, kode_pos = ?, nomor_telepon = ?, email = ?, fakultas = ? WHERE id = ?";
    } else {
        $sql = "UPDATE data_pribadi_mahasiswa SET nama = ?, alamat = ?, kode_pos = ?, nomor_telepon = ?, email = ?, fakultas = ? WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssssi", $nama, $alamat, $kode_pos, $nomor_telepon, $email, $fakultas, $id);

        if ($stmt->execute()) {
            header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid) . '&success=Data berhasil diperbarui');
        } else {
            header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid) . '&error=Gagal memperbarui data');
        }

        $stmt->close();
    } else {
        header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid) . '&error=Gagal menyiapkan statement');
    }
}

$conn->close();
?>
