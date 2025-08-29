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

try {
    if (!isset($_SESSION['user_id'])) {
        sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $user_id = (int) $_SESSION['user_id'];
    $detail_id = isset($_POST['detail_id']) ? (int)$_POST['detail_id'] : 0;
    $judul = isset($_POST['judul']) ? trim($_POST['judul']) : '';
    $jenis_ciptaan = isset($_POST['jenis_ciptaan']) ? trim($_POST['jenis_ciptaan']) : '';

    if (!$detail_id || !$judul || !$jenis_ciptaan) {
        sendJson(['success' => false, 'message' => 'Invalid input'], 400);
    }

    // Pastikan user adalah owner
    $sqlCheck = "SELECT * FROM detail_permohonan WHERE id = {$detail_id} AND user_id = {$user_id}";
    $resCheck = $conn->query($sqlCheck);
    if (!$resCheck || $resCheck->num_rows == 0) {
        sendJson(['success' => false, 'message' => 'Data not found or unauthorized'], 403);
    }

    // Update data
    $judulEsc = $conn->real_escape_string($judul);
    $jenisEsc = $conn->real_escape_string($jenis_ciptaan);
    $sqlUpdate = "UPDATE detail_permohonan SET judul = '{$judulEsc}', jenis_ciptaan = '{$jenisEsc}' WHERE id = {$detail_id}";
    $resUpdate = $conn->query($sqlUpdate);
    if (!$resUpdate) throw new Exception('Update failed: ' . $conn->error);

    sendJson(['success' => true, 'message' => 'Data updated']);
} catch (Exception $e) {
    error_log("edit_detail_permohonan error: " . $e->getMessage());
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'Server error';
    sendJson(['success' => false, 'message' => $msg, 'mysqli_error' => $conn->error ?? null], 500);
}
?>