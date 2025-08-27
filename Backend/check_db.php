<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
include __DIR__ . '/../koneksi.php';
$out = ['ok' => false];
if (!($conn instanceof mysqli)) { $out['error'] = 'No mysqli conn'; echo json_encode($out); exit; }
$out['ok'] = true;
$out['server_info'] = $conn->server_info;
$out['tables'] = [];
foreach (['detail_permohonan','uploads'] as $t) {
    $r = $conn->query("SHOW TABLES LIKE '".$conn->real_escape_string($t)."'");
    $out['tables'][$t] = ($r && $r->num_rows) ? 'exists' : 'missing';
    if ($r && $r->num_rows) {
        $c = $conn->query("SELECT COUNT(*) AS c FROM $t")->fetch_assoc();
        $out['counts'][$t] = (int)$c['c'];
    }
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);