<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

include 'koneksi.php';

// Verify database connection
if (!$conn) {
    error_log("Database connection failed");
    header('Location: ../Frontend/upload.php?m=db_error');
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    $dataid = $_SESSION['dataid'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;
    $uploadDir = __DIR__ . '/../uploads/';

    // Ensure upload directory exists
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception("Failed to create upload directory");
        }
    }

    // Process each file
    $files = [
        'file_sp' => 'Surat Pernyataan',
        'file_sph' => 'Surat Pengalihan Hak',
        'file_contoh_karya' => 'Contoh Karya',
        'file_ktp' => 'KTP',
        'file_bukti_pembayaran' => 'Bukti Pembayaran'
    ];

    $uploadedFiles = [];

    foreach ($files as $field => $label) {
        if (!isset($_FILES[$field])) {
            throw new Exception("File $label tidak ditemukan");
        }

        $file = $_FILES[$field];

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading $label: " . $file['error']);
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("$label melebihi batas ukuran 2MB");
        }

        // Verify file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            throw new Exception("$label harus berformat PDF");
        }

        // Generate unique filename
        $newFileName = 'file_' . uniqid(time()) . '.pdf';
        $filePath = $uploadDir . $newFileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Gagal mengupload $label");
        }

        $uploadedFiles[$field] = $newFileName;
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO uploads (dataid, user_id, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "iisssss",
        $dataid,
        $user_id,
        $uploadedFiles['file_sp'],
        $uploadedFiles['file_sph'],
        $uploadedFiles['file_contoh_karya'],
        $uploadedFiles['file_ktp'],
        $uploadedFiles['file_bukti_pembayaran']
    );

    if (!$stmt->execute()) {
        throw new Exception("Database insert failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->commit();

    // Redirect on success
    header('Location: ../Frontend/daftar_user.php?status=success');
    exit();
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();

    // Clean up any uploaded files
    foreach ($uploadedFiles as $file) {
        $path = $uploadDir . $file;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    error_log("Upload Error: " . $e->getMessage());
    header('Location: ../Frontend/upload.php?m=error&msg=' . urlencode($e->getMessage()));
    exit();
} finally {
    $conn->close();
}
