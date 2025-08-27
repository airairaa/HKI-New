<?php
define('API_REQUEST', true);
define('DEBUG_MODE', false);

error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

include __DIR__ . '/../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

function sendJson($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function safeFilename($name) {
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    return preg_replace('/_+/', '_', $name);
}

// initialize so catch() can safely access/clean them
$newFiles = []; // field => new filename
$movedFiles = []; // list of full paths moved (for cleanup on error)

try {
    if (!isset($_SESSION['user_id'])) {
        sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if (!($conn instanceof mysqli)) throw new Exception('Database connection not available');

    $user_id = (int) $_SESSION['user_id'];
    $upload_id = isset($_POST['upload_id']) ? (int)$_POST['upload_id'] : 0;
    $dataid = isset($_POST['dataid']) ? (int)$_POST['dataid'] : 0;

    if ($upload_id <= 0 && $dataid <= 0) {
        sendJson(['success' => false, 'message' => 'upload_id or dataid required'], 400);
    }

    // Determine which uploads row to UPDATE:
    // 1) prefer explicit upload_id
    // 2) otherwise find latest uploads row for dataid + user_id
    if ($upload_id > 0) {
        $stmt = $conn->prepare('SELECT * FROM uploads WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->bind_param('ii', $upload_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $existing = $res->fetch_assoc();
        $stmt->close();
    } else {
        $stmt = $conn->prepare('SELECT * FROM uploads WHERE dataid = ? AND user_id = ? ORDER BY uploaded_at DESC, id DESC LIMIT 1');
        $stmt->bind_param('ii', $dataid, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $existing = $res->fetch_assoc();
        $stmt->close();
        if ($existing) $upload_id = (int)$existing['id'];
    }

    if (!$existing) {
        // Do NOT insert new row here — return error so frontend can decide
        sendJson(['success' => false, 'message' => 'No existing upload record found for given upload_id/dataid. Edit aborted (no insert).'], 404);
    }

    // allowed fields and per-field max sizes (bytes)
    $fields = [
        'file_ktp' => 2 * 1024 * 1024,               // 2 MB
        'file_contoh_karya' => 15 * 1024 * 1024,     // 15 MB
        'file_sp' => 5 * 1024 * 1024,                // 5 MB
        'file_sph' => 5 * 1024 * 1024,               // 5 MB
        'file_bukti_pembayaran' => 5 * 1024 * 1024,  // 5 MB
    ];

    $allowed_ext = ['pdf','jpg','jpeg','png','doc','docx','zip'];
    $allowed_mime = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/x-zip-compressed'
    ];

    $uploadDir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) throw new Exception('Unable to create upload directory');
    }

    // finfo for MIME checking (if available)
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;

    foreach ($fields as $field => $maxSize) {
        if (!isset($_FILES[$field])) continue;
        $f = $_FILES[$field];
        if (!isset($f['error']) || $f['error'] !== UPLOAD_ERR_OK) continue;

        if ($f['size'] > $maxSize) {
            if ($finfo) finfo_close($finfo);
            sendJson(['success' => false, 'message' => "$field exceeds max size"], 400);
        }

        $origName = $f['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext, true)) {
            if ($finfo) finfo_close($finfo);
            sendJson(['success' => false, 'message' => "File type not allowed for $field (extension)"], 400);
        }

        // MIME check if finfo available
        if ($finfo) {
            $mime = finfo_file($finfo, $f['tmp_name']);
            if ($mime === false || !in_array($mime, $allowed_mime, true)) {
                finfo_close($finfo);
                sendJson(['success' => false, 'message' => "File type not allowed for $field (mime)"], 400);
            }
        }

        $base = pathinfo($origName, PATHINFO_FILENAME);
        $base = safeFilename(substr($base, 0, 50));
        // Use upload_id in filename to link to existing record (no new record creation)
        $newName = sprintf('upload_%d_%s_%d.%s', $upload_id, $field, time(), $ext);
        $target = $uploadDir . DIRECTORY_SEPARATOR . $newName;

        if (!move_uploaded_file($f['tmp_name'], $target)) {
            // fallback: try rename if temporary file still present
            if (!@rename($f['tmp_name'], $target)) {
                // cleanup moved files so far
                foreach ($movedFiles as $p) @unlink($p);
                if ($finfo) finfo_close($finfo);
                throw new Exception("Failed to move uploaded file for $field");
            }
        }

        @chmod($target, 0644);

        $newFiles[$field] = $newName;
        $movedFiles[] = $target;
    }

    if (empty($newFiles)) {
        if ($finfo) finfo_close($finfo);
        sendJson(['success' => true, 'message' => 'No files uploaded; nothing changed']);
    }

    // prepare update: update only the fields uploaded
    $cols = [];
    $types = '';
    $values = [];
    foreach ($newFiles as $field => $fname) {
        $cols[] = "`$field` = ?";
        $types .= 's';
        $values[] = $fname;
    }
    $cols[] = "uploaded_at = NOW()";

    $sql = 'UPDATE uploads SET ' . implode(', ', $cols) . ' WHERE id = ? AND user_id = ?';
    $types .= 'ii';
    $values[] = $upload_id;
    $values[] = $user_id;

    $conn->begin_transaction();
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $conn->rollback();
        foreach ($movedFiles as $p) @unlink($p);
        if ($finfo) finfo_close($finfo);
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // bind params dynamically (ensure references)
    $bindParams = array_merge([$types], $values);
    $refs = [];
    foreach ($bindParams as $key => $val) {
        $refs[$key] = &$bindParams[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    if (!$stmt->execute()) {
        $stmt->close();
        $conn->rollback();
        foreach ($movedFiles as $p) @unlink($p);
        if ($finfo) finfo_close($finfo);
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    $stmt->close();
    $conn->commit();

    // delete old files that were replaced
    foreach ($newFiles as $field => $fname) {
        $old = $existing[$field] ?? null;
        if ($old) {
            $oldPath = $uploadDir . DIRECTORY_SEPARATOR . $old;
            if (is_file($oldPath)) @unlink($oldPath);
        }
    }

    if ($finfo) finfo_close($finfo);

    // return updated row
    $stmt = $conn->prepare('SELECT * FROM uploads WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $upload_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $updated = $res->fetch_assoc();
    $stmt->close();

    sendJson(['success' => true, 'message' => 'Files updated', 'upload' => $updated]);
} catch (Exception $e) {
    error_log("edit_uploads error: " . $e->getMessage());
    // cleanup moved files on error
    if (!empty($movedFiles) && is_array($movedFiles)) {
        foreach ($movedFiles as $p) @unlink($p);
    }
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'Server error';
    sendJson(['success' => false, 'message' => $msg, 'mysqli_error' => $conn->error ?? null], 500);
}
?>