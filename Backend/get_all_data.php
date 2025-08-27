<?php
session_start();

// temporary dev-mode: tunjukkan error di JSON agar mudah debugging
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// capture accidental output
ob_start();

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/koneksi.php';

// discard include output
$maybe_output = ob_get_clean();
if (!empty($maybe_output)) {
    error_log("get_all_data extra output: " . substr($maybe_output, 0, 2000));
}

if (!isset($conn) || !$conn) {
    http_response_code(500);
    $err = isset($conn) ? 'Invalid $conn' : 'DB connection failed';
    error_log("get_all_data DB error: " . $err);
    echo json_encode(['error' => $err]);
    exit();
}

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, (int)($_GET['per_page'] ?? 25));
    $search = trim($_GET['search'] ?? '');

    // build where clause and params
    $where = '1=1';
    $params = [];
    $types = '';

    if ($search !== '') {
        $where .= ' AND (d.judul LIKE ? OR d.uraian_singkat LIKE ? OR EXISTS(SELECT 1 FROM users u JOIN uploads up ON u.id = up.user_id WHERE up.dataid = d.dataid AND u.username LIKE ?))';
        $like = '%' . $search . '%';
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types .= 'sss';
    }

    // count total
    $countSql = "SELECT COUNT(*) AS cnt FROM detail_permohonan d WHERE $where";
    $stmt = $conn->prepare($countSql);
    if ($stmt === false) {
        throw new Exception('Prepare failed (count): ' . $conn->error);
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new Exception('Execute failed (count): ' . $stmt->error);
    }
    $res = $stmt->get_result();
    $totalRows = ($r = $res->fetch_assoc()) ? (int)$r['cnt'] : 0;
    $stmt->close();

    $totalPages = (int)ceil($totalRows / $perPage);
    $offset = ($page - 1) * $perPage;

    $sql = "
    SELECT
      d.id,
      d.dataid,
      d.jenis_permohonan,
      d.jenis_ciptaan,
      d.sub_jenis_ciptaan,
      d.judul,
      d.uraian_singkat,
      d.tanggal_pertama_kali_diumumkan,
      d.negara_pertama_kali_diumumkan,
      d.jenis_pendanaan,
      d.jenis_hibah,
      d.created_at,
      (SELECT u.file_contoh_karya FROM uploads u WHERE u.dataid = d.dataid ORDER BY u.id DESC LIMIT 1) AS file_contoh_karya,
      (SELECT u.file_ktp FROM uploads u WHERE u.dataid = d.dataid ORDER BY u.id DESC LIMIT 1) AS file_ktp,
      (SELECT u.file_sp FROM uploads u WHERE u.dataid = d.dataid ORDER BY u.id DESC LIMIT 1) AS file_sp,
      (SELECT u.file_sph FROM uploads u WHERE u.dataid = d.dataid ORDER BY u.id DESC LIMIT 1) AS file_sph,
      (SELECT u.file_bukti_pembayaran FROM uploads u WHERE u.dataid = d.dataid ORDER BY u.id DESC LIMIT 1) AS file_bukti_pembayaran
    FROM detail_permohonan d
    WHERE $where
    ORDER BY d.id DESC
    LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare failed (select): ' . $conn->error);
    }

    // bind params (search params + limit/offset)
    $bindTypes = $types . 'ii';
    $bindParams = $params;
    $bindParams[] = $perPage;
    $bindParams[] = $offset;

    // ensure correct number of params
    if (strlen($bindTypes) > 0) {
        $stmt->bind_param($bindTypes, ...$bindParams);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute failed (select): ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => isset($row['id']) ? (int)$row['id'] : null,
            'dataid' => $row['dataid'] ?? null,
            'jenis_permohonan' => $row['jenis_permohonan'] ?? null,
            'jenis_ciptaan' => $row['jenis_ciptaan'] ?? null,
            'sub_jenis_ciptaan' => $row['sub_jenis_ciptaan'] ?? null,
            'judul' => $row['judul'] ?? null,
            'uraian_singkat' => $row['uraian_singkat'] ?? null,
            'tanggal_pertama_kali_diumumkan' => $row['tanggal_pertama_kali_diumumkan'] ?? null,
            'negara_pertama_kali_diumumkan' => $row['negara_pertama_kali_diumumkan'] ?? null,
            'jenis_pendanaan' => $row['jenis_pendanaan'] ?? null,
            'jenis_hibah' => $row['jenis_hibah'] ?? null,
            'file_contoh_karya' => $row['file_contoh_karya'] ?? null,
            'file_ktp' => $row['file_ktp'] ?? null,
            'file_sp' => $row['file_sp'] ?? null,
            'file_sph' => $row['file_sph'] ?? null,
            'file_bukti_pembayaran' => $row['file_bukti_pembayaran'] ?? null,
            'created_at' => $row['created_at'] ?? null
        ];
    }
    $stmt->close();
    $conn->close();

    echo json_encode([
        'data' => $data,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalRows' => $totalRows
    ], JSON_UNESCAPED_UNICODE);
    exit();

} catch (Exception $e) {
    // log and return JSON error (for debugging)
    error_log('get_all_data exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
    exit();
}
?>