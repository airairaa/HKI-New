<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hki";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dataid'])) {
    // MODIFIKASI: Berikan nilai default untuk setiap file yang tidak diupload
    $dataid = $_POST['dataid'];
    $uploadDir = 'uploads/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // MODIFIKASI: Inisialisasi semua nilai dengan string kosong sebagai default
    $uploads = [
        'file_sp' => '',
        'file_sph' => '',
        'file_contoh_karya' => '',
        'file_ktp' => '',
        'file_bukti_pembayaran' => ''
    ];

    // MODIFIKASI: Proses upload hanya untuk file yang benar-benar diupload
    foreach ($uploads as $key => &$value) {
        if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION);
            $newFileName = "{$key}_{$dataid}.{$ext}";
            $filePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES[$key]['tmp_name'], $filePath)) {
                $value = $filePath;
            }
        }
    }

    try {
        // MODIFIKASI: Gunakan prepared statement dengan nilai default
        $stmt = $conn->prepare("INSERT INTO uploads 
                              (dataid, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssss",
            $dataid,
            $uploads['file_sp'],
            $uploads['file_sph'],
            $uploads['file_contoh_karya'],
            $uploads['file_ktp'],
            $uploads['file_bukti_pembayaran']
        );

        if ($stmt->execute()) {
            $conn->close();
            header("Location: ../Frontend/daftar_user.php?status=success");
            exit();
        } else {
            // MODIFIKASI: Tambahkan pesan error yang lebih informatif
            $errorMsg = "Gagal menyimpan data: " . $stmt->error;
            $conn->close();
            header("Location: ../Frontend/upload.php?status=error&msg=" . urlencode($errorMsg));
            exit();
        }
    } catch (Exception $e) {
        // MODIFIKASI: Tambahkan rollback dan error handling
        $conn->rollback();
        $conn->close();
        header("Location: ../Frontend/upload.php?status=error&msg=" . urlencode("Error: " . $e->getMessage()));
        exit();
    }
} else {
    $conn->close();
    header("Location: ../Frontend/upload.php?status=error&msg=" . urlencode("Request tidak valid"));
    exit();
}
?>
