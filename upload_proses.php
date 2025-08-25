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

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Request tidak valid");
    }

    // Ambil upload_id (jika form menyediakan) dan dataid (POST > session > GET)
    $uploadId = isset($_POST['upload_id']) ? (int)$_POST['upload_id'] : 0;
    $dataid = trim($_POST['dataid'] ?? $_SESSION['dataid'] ?? $_GET['dataid'] ?? '');

    // Jika dataid kosong buat baru dan simpan ke session (untuk INSERT)
    if ($dataid === '') {
        $dataid = uniqid('data_', true);
        $_SESSION['dataid'] = $dataid;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception("Gagal membuat direktori upload");
    }

    // Ambil existing record: prioritas berdasarkan upload_id jika ada, lain cari by dataid (terbaru)
    $existing = [
        'id' => 0,
        'dataid' => '',
        'file_sp' => '',
        'file_sph' => '',
        'file_contoh_karya' => '',
        'file_ktp' => '',
        'file_bukti_pembayaran' => ''
    ];

    if ($uploadId > 0) {
        $q = $conn->prepare("SELECT id, dataid, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran FROM uploads WHERE id = ? LIMIT 1");
        if (!$q) throw new Exception("DB prepare failed: " . $conn->error);
        $q->bind_param("i", $uploadId);
        $q->execute();
        $res = $q->get_result();
        if ($row = $res->fetch_assoc()) $existing = array_merge($existing, $row);
        $q->close();
    } else {
        $q = $conn->prepare("SELECT id, dataid, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran FROM uploads WHERE dataid = ? ORDER BY id DESC LIMIT 1");
        if (!$q) throw new Exception("DB prepare failed: " . $conn->error);
        $q->bind_param("s", $dataid);
        $q->execute();
        $res = $q->get_result();
        if ($row = $res->fetch_assoc()) $existing = array_merge($existing, $row);
        $q->close();
    }

    $isUpdate = !empty($existing['id']);
    $existingId = (int)$existing['id'];

    // Default filenames = existing -> supaya field yang tidak diupload tetap terjaga
    $uploads = [
        'file_sp' => $existing['file_sp'],
        'file_sph' => $existing['file_sph'],
        'file_contoh_karya' => $existing['file_contoh_karya'],
        'file_ktp' => $existing['file_ktp'],
        'file_bukti_pembayaran' => $existing['file_bukti_pembayaran']
    ];

    $fields = array_keys($uploads);
    $movedFiles = [];   // new files moved (absolute paths) — cleanup on error
    $deletedFiles = []; // old files to delete after successful commit

    // Proses hanya file yang diupload; nama existing dipertahankan bila tidak diupload
    foreach ($fields as $key) {
        if (!isset($_FILES[$key])) continue;
        $file = $_FILES[$key];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) continue;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading {$key}: code " . $file['error']);
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("{$key} melebihi batas 2MB");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            throw new Exception("{$key} harus berformat PDF");
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'pdf';
        $newFileName = $key . '_' . uniqid($dataid . '_', true) . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Gagal memindahkan file untuk {$key}");
        }

        // Jadwalkan penghapusan file lama (jika ada) setelah DB commit
        if (!empty($existing[$key]) && $existing[$key] !== $newFileName) {
            $oldPath = $uploadDir . $existing[$key];
            if (file_exists($oldPath)) $deletedFiles[] = $oldPath;
        }

        $uploads[$key] = $newFileName;
        $movedFiles[] = $targetPath;
    }

    // Mulai transaksi supaya DB dan penghapusan file konsisten
    if (method_exists($conn, 'begin_transaction')) $conn->begin_transaction();

    $user_id = (int)($_SESSION['user_id'] ?? 0);

    if ($isUpdate) {
        // UPDATE berdasarkan id record (paling aman) -> tidak membuat baris baru
        $stmt = $conn->prepare("UPDATE uploads SET file_sp = ?, file_sph = ?, file_contoh_karya = ?, file_ktp = ?, file_bukti_pembayaran = ?, user_id = ?, updated_at = NOW() WHERE id = ?");
        if (!$stmt) throw new Exception("DB prepare failed: " . $conn->error);
        $stmt->bind_param(
            "sssssii",
            $uploads['file_sp'],
            $uploads['file_sph'],
            $uploads['file_contoh_karya'],
            $uploads['file_ktp'],
            $uploads['file_bukti_pembayaran'],
            $user_id,
            $existingId
        );
        if (!$stmt->execute()) throw new Exception("DB update failed: " . $stmt->error);
        $stmt->close();
    } else {
        // INSERT baru bila belum ada record untuk dataid
        $stmt = $conn->prepare("INSERT INTO uploads (dataid, user_id, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) throw new Exception("DB prepare failed: " . $conn->error);
        $stmt->bind_param(
            "sisssss",
            $dataid,
            $user_id,
            $uploads['file_sp'],
            $uploads['file_sph'],
            $uploads['file_contoh_karya'],
            $uploads['file_ktp'],
            $uploads['file_bukti_pembayaran']
        );
        if (!$stmt->execute()) throw new Exception("DB insert failed: " . $stmt->error);
        $stmt->close();
    }

    // Commit DB
    if (method_exists($conn, 'commit')) $conn->commit();

    // Hapus file lama yang diganti
    foreach ($deletedFiles as $p) {
        @unlink($p);
    }

    // Bersihkan session agar form baru kosong saat user ingin input baru
    if (isset($_SESSION['dataid'])) unset($_SESSION['dataid']);
    if (isset($_SESSION['input_awal'])) unset($_SESSION['input_awal']);
    $_SESSION['upload_complete'] = true;

    $conn->close();
    header("Location: ../Frontend/daftar_user.php?status=success");
    exit();
} catch (Exception $e) {
    if (isset($conn) && method_exists($conn, 'rollback')) $conn->rollback();

    // Hapus file baru yang sudah dipindah bila terjadi error
    if (!empty($movedFiles)) {
        foreach ($movedFiles as $p) if (file_exists($p)) @unlink($p);
    }

    if (isset($conn)) $conn->close();
    header("Location: ../Frontend/upload.php?status=error&msg=" . urlencode($e->getMessage()));
    exit();
}
?>