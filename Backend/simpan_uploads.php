<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

include 'koneksi.php';

if (!$conn) {
    die("Koneksi ke database gagal.");
}

if (isset($_FILES['file'])) {
    $idp = $_SESSION['id_permohonan'];
    $fileName = $_FILES['file']['name'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = mime_content_type($fileTmp);

    // Validasi file PDF maksimal 2MB
    if ($fileType !== 'application/pdf' || $fileSize > 2 * 1024 * 1024) {
        header('Location: ../frontend/upload.php?m=invalid_file');
        exit();
    }

    // Generate nama file unik
    $newFileName = 'doc_' . $idp . '_' . time() . '.pdf';
    $uploadDir = 'uploads/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filePath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $filePath)) {
        header('Location: ../frontend/upload.php?m=upload_failed');
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO uploads (id_permohonan, nama_file, path_file) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $idp, $fileName, $filePath);

    if ($stmt->execute()) {
        unset($_SESSION['id_permohonan']);
        header('Location: ../frontend/daftar_user.php?status=success');
    } else {
        unlink($filePath); // Hapus file jika gagal simpan ke database
        die("Error: " . $stmt->error);
    }

    $stmt->close();
} else {
    header('Location: ../frontend/daftar_user.php');
}

$conn->close();
?>
