<?php
// production: set DEBUG_MODE false
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

try {
    if (!isset($_SESSION['user_id'])) {
        sendJson(['success' => false, 'message' => 'Unauthorized (no session)'], 401);
    }

    $user_id = (int) $_SESSION['user_id'];
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10; // increase if you want more rows per page
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    if (!($conn instanceof mysqli)) throw new Exception('Database connection not available');

    $ownershipCond = "(detail_permohonan.user_id = {$user_id} OR (detail_permohonan.dataid IS NOT NULL AND detail_permohonan.dataid IN (SELECT DISTINCT dataid FROM uploads WHERE user_id = {$user_id})))";

    // Add search
    $where = $ownershipCond;
    if ($search !== '') {
        $s = $conn->real_escape_string('%' . $search . '%');
        $where .= " AND (detail_permohonan.judul LIKE '{$s}' OR detail_permohonan.jenis_ciptaan LIKE '{$s}')";
    }

    // Count for pagination
    $countSql = "SELECT COUNT(*) AS total FROM detail_permohonan WHERE {$where}";
    $cntRes = $conn->query($countSql);
    if (!$cntRes) throw new Exception('Count query failed: ' . $conn->error);
    $cntRow = $cntRes->fetch_assoc();
    $totalRows = (int) ($cntRow['total'] ?? 0);
    $totalPages = $totalRows ? (int) ceil($totalRows / $limit) : 1;

    // Fetch details (no 'status' column)
    $sql = "SELECT id AS detail_id, dataid, judul, jenis_ciptaan, created_at
            FROM detail_permohonan
            WHERE {$where}
            ORDER BY created_at DESC
            LIMIT {$limit} OFFSET {$offset}";
    $res = $conn->query($sql);
    if (!$res) throw new Exception('Details query failed: ' . $conn->error);
    $details = $res->fetch_all(MYSQLI_ASSOC);

    if (empty($details)) {
        sendJson(['success' => true, 'data' => [], 'current_page' => $page, 'total_pages' => $totalPages, 'total_rows' => $totalRows]);
    }

    // Collect dataid values (unique, non-empty)
    $dataids = [];
    foreach ($details as $r) {
        if (!empty($r['dataid'])) $dataids[] = (int)$r['dataid'];
    }
    $uploadsByDataid = [];

    if (!empty($dataids)) {
        $ids = implode(',', array_map('intval', array_unique($dataids)));
        $sqlUp = "SELECT * FROM uploads WHERE dataid IN ({$ids}) ORDER BY uploaded_at DESC, id DESC";
        $resUp = $conn->query($sqlUp);
        if (!$resUp) throw new Exception('Uploads query failed: ' . $conn->error);
        while ($u = $resUp->fetch_assoc()) {
            $did = $u['dataid'];
            if (!isset($uploadsByDataid[$did])) $uploadsByDataid[$did] = $u;
        }
    }

    // Build response
    $out = [];
    foreach ($details as $d) {
        $did = $d['dataid'];
        $up = isset($uploadsByDataid[$did]) ? $uploadsByDataid[$did] : null;

        $out[] = [
            'id' => (int)$d['detail_id'],
            'dataid' => $d['dataid'],
            'judul' => $d['judul'],
            'jenis_ciptaan' => $d['jenis_ciptaan'],
            'status' => 'Pending',
            'created_at' => $d['created_at'],
            'upload_date' => $up['uploaded_at'] ?? null,
            'files' => [
                'ktp' => ['exists' => !empty($up['file_ktp']), 'file' => $up['file_ktp'] ?? null, 'upload_id' => $up['id'] ?? null],
                'contoh_karya' => ['exists' => !empty($up['file_contoh_karya']), 'file' => $up['file_contoh_karya'] ?? null, 'upload_id' => $up['id'] ?? null],
                'sp' => ['exists' => !empty($up['file_sp']), 'file' => $up['file_sp'] ?? null, 'upload_id' => $up['id'] ?? null],
                'sph' => ['exists' => !empty($up['file_sph']), 'file' => $up['file_sph'] ?? null, 'upload_id' => $up['id'] ?? null],
                'bukti' => ['exists' => !empty($up['file_bukti_pembayaran']), 'file' => $up['file_bukti_pembayaran'] ?? null, 'upload_id' => $up['id'] ?? null],
            ],
        ];
    }

    sendJson(['success' => true, 'data' => $out, 'current_page' => $page, 'total_pages' => $totalPages, 'total_rows' => $totalRows]);
} catch (Exception $e) {
    error_log("get_daftar_user error: " . $e->getMessage());
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'Server error';
    sendJson(['success' => false, 'message' => $msg, 'mysqli_error' => $conn->error ?? null], 500);
}
?>