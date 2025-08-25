<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

include 'koneksi.php';

if (!$conn) {
    header('Location: ../Frontend/upload.php?m=db_error');
    exit();
}

try {
    $transStarted = false;
    $conn->begin_transaction();
    $transStarted = true;

    // determine identifiers and mode from POST/GET/SESSION
    $dataid = $_POST['dataid'] ?? $_SESSION['dataid'] ?? $_GET['dataid'] ?? null;
    $mode = $_POST['mode'] ?? $_GET['mode'] ?? 'create'; // keep provided mode if any
    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $uploadDir = __DIR__ . '/../uploads/';

    if (!file_exists($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new Exception("Failed to create upload directory");
    }

    // fetch existing upload row (if any) to allow skipping uploads when editing
    $existing = [
        'id' => 0,
        'file_sp' => '',
        'file_sph' => '',
        'file_contoh_karya' => '',
        'file_ktp' => '',
        'file_bukti_pembayaran' => ''
    ];
    if (!empty($dataid)) {
        $s = $conn->prepare('SELECT id, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran FROM uploads WHERE dataid = ? ORDER BY id DESC LIMIT 1');
        if ($s) {
            $s->bind_param('s', $dataid);
            $s->execute();
            $res = $s->get_result();
            if ($row = $res->fetch_assoc()) $existing = array_merge($existing, $row);
            $s->close();
        }
    }

    // If there's an existing uploads row, ensure we're in edit mode
    if (!empty($existing['id'])) {
        $mode = 'edit';
    }

    $fields = [
        'file_sp' => 'Surat Pernyataan',
        'file_sph' => 'Surat Pengalihan Hak',
        'file_contoh_karya' => 'Contoh Karya',
        'file_ktp' => 'KTP',
        'file_bukti_pembayaran' => 'Bukti Pembayaran'
    ];

    $uploadedFiles = [];
    $newUploaded = []; // track which fields got new files
    $deletedFiles = []; // full paths to delete after commit
    $movedFiles = []; // newly moved files to cleanup on error

    // initialize uploadedFiles with existing values so missing checks work
    foreach ($fields as $field => $label) {
        $uploadedFiles[$field] = $existing[$field] ?? '';
        $newUploaded[$field] = false;
    }

    foreach ($fields as $field => $label) {
        // if input not present in $_FILES, skip (keep existing)
        if (!isset($_FILES[$field])) {
            continue;
        }

        $file = $_FILES[$field];

        // No file selected
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            continue; // keep existing if any
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading {$label}: " . $file['error']);
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception("{$label} melebihi batas ukuran 2MB");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($mimeType !== 'application/pdf') {
            throw new Exception("{$label} harus berformat PDF");
        }

        // move uploaded file
        $newFileName = 'file_' . uniqid('', true) . '.pdf';
        $target = $uploadDir . $newFileName;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new Exception("Gagal mengupload {$label}");
        }

        // track moved file for potential cleanup on error
        $movedFiles[] = $target;

        // if there was an existing file for this field mark it for deletion after commit
        if (!empty($existing[$field]) && $existing[$field] !== $newFileName) {
            $deletedFiles[] = $uploadDir . $existing[$field];
        }

        $uploadedFiles[$field] = $newFileName;
        $newUploaded[$field] = true;
    }

    // Jika mode adalah 'edit' maka semua file tidak wajib diupload.
    // Validasi kelengkapan hanya dilakukan saat mode bukan 'edit' (mis. create)
    if ($mode !== 'edit') {
        $missing = [];
        foreach ($fields as $field => $label) {
            $hasExisting = !empty($existing[$field]);
            $hasNew = !empty($newUploaded[$field]);
            if ($mode === 'edit') {
                if (!$hasExisting && !$hasNew) $missing[] = $label;
            } else {
                if (empty($uploadedFiles[$field])) $missing[] = $label;
            }
        }

        if (count($missing) > 0) {
            throw new Exception('File belum lengkap: ' . implode(', ', $missing));
        }
    }

    // Insert or update DB
    if ($mode === 'edit' && !empty($existing['id'])) {
        $stmt = $conn->prepare("UPDATE uploads SET file_sp = ?, file_sph = ?, file_contoh_karya = ?, file_ktp = ?, file_bukti_pembayaran = ?, user_id = ? WHERE id = ?");
        if (!$stmt) throw new Exception("DB prepare failed: " . $conn->error);
        $stmt->bind_param(
            "sssssii",
            $uploadedFiles['file_sp'],
            $uploadedFiles['file_sph'],
            $uploadedFiles['file_contoh_karya'],
            $uploadedFiles['file_ktp'],
            $uploadedFiles['file_bukti_pembayaran'],
            $user_id,
            $existing['id']
        );
        if (!$stmt->execute()) throw new Exception("DB update failed: " . $stmt->error);
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO uploads (dataid, user_id, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception("DB prepare failed: " . $conn->error);
        $stmt->bind_param(
            "sisssss",
            $dataid,
            $user_id,
            $uploadedFiles['file_sp'],
            $uploadedFiles['file_sph'],
            $uploadedFiles['file_contoh_karya'],
            $uploadedFiles['file_ktp'],
            $uploadedFiles['file_bukti_pembayaran']
        );
        if (!$stmt->execute()) throw new Exception("DB insert failed: " . $stmt->error);
        $stmt->close();
    }

    $conn->commit();

    // remove old files that were replaced
    foreach ($deletedFiles as $path) {
        if (file_exists($path)) @unlink($path);
    }

    // --- NEW: reset session keys so next input is fresh ---
    if (isset($_SESSION['dataid'])) unset($_SESSION['dataid']);
    if (isset($_SESSION['input_awal'])) unset($_SESSION['input_awal']);
    if (isset($_SESSION['data_pengusul'])) unset($_SESSION['data_pengusul']);
    // optional flag to indicate upload finished
    $_SESSION['upload_complete'] = true;
    // ------------------------------------------------------

    header('Location: ../Frontend/daftar_user.php?status=success');
    exit();
} catch (Exception $e) {
    if (isset($conn) && !empty($transStarted)) {
        $conn->rollback();
    }

    // cleanup any newly moved files on error
    if (!empty($movedFiles)) {
        foreach ($movedFiles as $p) {
            if (file_exists($p)) @unlink($p);
        }
    }

    error_log("Upload Error: " . $e->getMessage());
    header('Location: ../Frontend/upload.php?m=error&msg=' . urlencode($e->getMessage()));
    exit();
} finally {
    if (isset($conn) && $conn) $conn->close();
}