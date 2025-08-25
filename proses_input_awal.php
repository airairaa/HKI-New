<?php
session_start();

// Validasi user login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

// Validasi data session
if (!isset($_SESSION['input_awal'])) {
    header('Location: ../Frontend/input_awal.php?error=Session data tidak ditemukan');
    exit();
}

// Generate dataid if not exists in session
if (!isset($_SESSION['dataid'])) {
    $_SESSION['dataid'] = time() . rand(100, 999);
}

// Koneksi database
include 'koneksi.php';
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data dari session
$input_awal = $_SESSION['input_awal'];

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // 1. Simpan data ke tabel detail_permohonan (INSERT)
    $sql = "INSERT INTO detail_permohonan (
                jenis_permohonan, jenis_ciptaan, judul, 
                uraian_singkat, tanggal_pertama_kali_diumumkan, 
                kota_pertama_kali_diumumkan, jenis_pendanaan, 
                jenis_hibah, username, email, dataid
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssss",
        $input_awal['jenis_permohonan'],
        $input_awal['jenis_ciptaan'],
        $input_awal['judul'],
        $input_awal['uraian_singkat'],
        $input_awal['tanggal_pertama_kali_diumumkan'],
        $input_awal['kota_pertama_kali_diumumkan'],
        $input_awal['jenis_pendanaan'],
        $input_awal['nama_pendanaan'],
        $_SESSION['username'],
        $_SESSION['email'], // Pastikan email ada di session
        $_SESSION['dataid']
    );

    // Eksekusi query
    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan data permohonan: " . $stmt->error);
    }

    // 2. Update tabel users (jika diperlukan)
    $update_sql = "UPDATE users SET 
                  last_permohonan_id = LAST_INSERT_ID() 
                  WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $_SESSION['user_id']);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Gagal update data user: " . $update_stmt->error);
    }

    // Commit transaksi jika semua berhasil
    $conn->commit();

    // Redirect ke halaman berikutnya dengan dataid
    unset($_SESSION['input_awal']); // Bersihkan session jika ada
    header('Location: ../Frontend/input.php?dataid=' . urlencode($_SESSION['dataid']));
    exit();

} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    
    // Log error
    error_log("Error pada proses_input_awal: " . $e->getMessage());
    
    // Redirect dengan pesan error
    header('Location: ../Frontend/input_awal.php?error=' . urlencode($e->getMessage()));
    exit();
} finally {
    // Tutup koneksi
    if (isset($stmt)) $stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    $conn->close();
}
?>
