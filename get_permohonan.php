<?php

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Akses tidak diizinkan. Hanya admin yang dapat mengakses halaman ini.']);
    exit();
}

include '../backend/koneksi.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 25;
$offset = ($page - 1) * $limit;

// Query utama
$sql = "
SELECT 
  dp.id as detail_id,
  dp.judul,
  u.file_contoh_karya,
  u.file_ktp,
  u.file_sp,
  u.file_sph,
  u.file_bukti_pembayaran,
  ra.id as review_id,
  ra.status,
  ra.sertifikat
FROM detail_permohonan dp
LEFT JOIN uploads u ON dp.id = u.dataid
LEFT JOIN review_ad ra ON dp.id = ra.detailpermohonan_id
WHERE dp.judul LIKE ?
ORDER BY dp.id ASC
LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$searchQuery = "%$search%";
$stmt->bind_param("sii", $searchQuery, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// Hitung total data untuk pagination
$countSql = "SELECT COUNT(*) as total FROM detail_permohonan WHERE judul LIKE ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("s", $searchQuery);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRows = $countResult->fetch_assoc()['total'] ?? 0;
$totalPages = $limit > 0 ? ceil($totalRows / $limit) : 1;

// Kirim data dalam format JSON yang diharapkan frontend
header('Content-Type: application/json');
echo json_encode([
    "data" => $rows,
    "currentPage" => $page,
    "totalPages" => $totalPages
]);
?>