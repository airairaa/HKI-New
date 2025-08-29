<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$upload_id = isset($_GET['upload_id']) ? (int)$_GET['upload_id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$filenameParam = isset($_GET['filename']) ? urldecode($_GET['filename']) : null;

$map = [
    'ktp' => 'file_ktp',
    'karya' => 'file_contoh_karya',
    'sp' => 'file_sp',
    'sph' => 'file_sph',
    'bukti' => 'file_bukti_pembayaran'
];

try {
    // prefer upload_id + type
    if ($upload_id > 0 && $type && isset($map[$type])) {
        $col = $map[$type];
        $stmt = $conn->prepare("SELECT {$col} AS fname FROM uploads WHERE id = ? LIMIT 1");
        if (!$stmt) { http_response_code(500); exit('Server error'); }
        $stmt->bind_param("i", $upload_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) { http_response_code(404); exit('File not found'); }
        $row = $res->fetch_assoc();
        $fname = $row['fname'];
    } elseif ($filenameParam) {
        $fname = $filenameParam;
    } else {
        http_response_code(400);
        exit('Bad request');
    }

    if (!$fname) { http_response_code(404); exit('No file'); }

    // support stored values that may include 'uploads/' prefix
    $basename = basename($fname);
    $path = realpath(__DIR__ . '/../uploads/' . $basename);
    if ($path === false || !file_exists($path)) {
        http_response_code(404);
        exit('File not found on disk');
    }

    // determine mime
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $path);
    finfo_close($finfo);

    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . $basename . '"');
    readfile($path);
    exit;

} catch (Exception $e) {
    error_log("view_file error: " . $e->getMessage());
    http_response_code(500);
    exit('Server error');
}
?>