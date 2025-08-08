<?php
// PERBAIKAN 1: Error handling dan output buffering
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error ke output
ob_start(); // Start output buffering untuk menangkap unexpected output

session_start();

// PERBAIKAN 2: Fungsi untuk mengirim JSON response
function sendJsonResponse($data, $status = 200) {
    // Clear any unexpected output
    ob_clean();
    
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// PERBAIKAN 3: Fungsi untuk mengirim error response
function sendErrorResponse($message, $status = 500) {
    sendJsonResponse([
        'error' => true,
        'message' => $message,
        'data' => [],
        'currentPage' => 1,
        'totalPages' => 0,
        'totalRows' => 0
    ], $status);
}

try {
    // PERBAIKAN 4: Validasi session
    if (!isset($_SESSION['user_id'])) {
        sendErrorResponse('Session user_id tidak ditemukan. Silakan login kembali.', 401);
    }
    
    if (!isset($_SESSION['role'])) {
        sendErrorResponse('Session role tidak ditemukan. Silakan login kembali.', 401);
    }

    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];
    
    // PERBAIKAN 5: Validasi parameter
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $limit = 25;
    $offset = ($page - 1) * $limit;
    
    // PERBAIKAN 6: Include database dengan error handling
    if (!file_exists('koneksi.php')) {
        sendErrorResponse('File koneksi.php tidak ditemukan');
    }
    
    // Define API_REQUEST before including koneksi.php
    define('API_REQUEST', true);
    include 'koneksi.php';
    
    // PERBAIKAN 7: Validasi koneksi database
    if (!isset($conn)) {
        sendErrorResponse('Koneksi database tidak tersedia');
    }
    
    // Check connection error dari koneksi.php
    if (isset($connection_error) && $connection_error !== null) {
        sendErrorResponse($connection_error);
    }
    
    if ($conn->connect_error) {
        sendErrorResponse('Database connection failed: ' . $conn->connect_error);
    }

    // Query untuk mendapatkan data dengan uploads dan status
    $sql = "
    SELECT 
        dp.id as detail_id,
        dp.judul,
        dp.jenis_permohonan,
        dp.jenis_ciptaan,
        dp.uraian_singkat,
        dp.created_at,
        u.file_contoh_karya,
        u.file_ktp,
        u.file_sp,
        u.file_sph,
        u.file_bukti_pembayaran,
        ra.status,
        ra.sertifikat
    FROM detail_permohonan dp
    LEFT JOIN uploads u ON dp.id = u.dataid
    LEFT JOIN review_ad ra ON dp.id = ra.detailpermohonan_id
    WHERE 1=1
    ";

    $params = [];
    $types = "";

    // Filter berdasarkan role
    if ($user_role !== 'admin') {
        $sql .= " AND (dp.user_id = ? OR dp.user_id IS NULL)";
        $params[] = $user_id;
        $types .= "i";
    }

    // Filter berdasarkan search
    if (!empty($search)) {
        $sql .= " AND dp.judul LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }

    $sql .= " ORDER BY dp.id DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // PERBAIKAN 8: Prepare statement dengan error handling
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendErrorResponse('Prepare statement failed: ' . $conn->error);
    }

    // PERBAIKAN 9: Bind parameter dengan validasi
    if (!empty($params)) {
        if (!$stmt->bind_param($types, ...$params)) {
            sendErrorResponse('Bind param failed: ' . $stmt->error);
        }
    }

    // PERBAIKAN 10: Execute dengan error handling
    if (!$stmt->execute()) {
        sendErrorResponse('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        sendErrorResponse('Get result failed: ' . $stmt->error);
    }

    // PERBAIKAN 11: Fetch data dengan validasi
    $data = [];
    while ($row = $result->fetch_assoc()) {
        if ($row) {
            // Pastikan semua field yang dibutuhkan ada
            $cleanRow = [
                'detail_id' => $row['detail_id'] ?? null,
                'judul' => $row['judul'] ?? '',
                'jenis_permohonan' => $row['jenis_permohonan'] ?? '',
                'jenis_ciptaan' => $row['jenis_ciptaan'] ?? '',
                'uraian_singkat' => $row['uraian_singkat'] ?? '',
                'created_at' => $row['created_at'] ?? '',
                'file_contoh_karya' => $row['file_contoh_karya'] ?? null,
                'file_ktp' => $row['file_ktp'] ?? null,
                'file_sp' => $row['file_sp'] ?? null,
                'file_sph' => $row['file_sph'] ?? null,
                'file_bukti_pembayaran' => $row['file_bukti_pembayaran'] ?? null,
                'status' => $row['status'] ?? null,
                'sertifikat' => $row['sertifikat'] ?? null
            ];
            $data[] = $cleanRow;
        }
    }

    // PERBAIKAN 12: Count query dengan error handling
    $countSql = "SELECT COUNT(*) as total FROM detail_permohonan dp WHERE 1=1";
    $countParams = [];
    $countTypes = "";

    if ($user_role !== 'admin') {
        $countSql .= " AND (dp.user_id = ? OR dp.user_id IS NULL)";
        $countParams[] = $user_id;
        $countTypes .= "i";
    }

    if (!empty($search)) {
        $countSql .= " AND dp.judul LIKE ?";
        $countParams[] = "%$search%";
        $countTypes .= "s";
    }

    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) {
        sendErrorResponse('Count prepare failed: ' . $conn->error);
    }

    if (!empty($countParams)) {
        if (!$countStmt->bind_param($countTypes, ...$countParams)) {
            sendErrorResponse('Count bind param failed: ' . $countStmt->error);
        }
    }

    if (!$countStmt->execute()) {
        sendErrorResponse('Count execute failed: ' . $countStmt->error);
    }

    $countResult = $countStmt->get_result();
    if (!$countResult) {
        sendErrorResponse('Count get result failed: ' . $countStmt->error);
    }

    $countRow = $countResult->fetch_assoc();
    $totalRows = $countRow['total'] ?? 0;
    $totalPages = $limit > 0 ? ceil($totalRows / $limit) : 1;

    // PERBAIKAN 13: Response sukses dengan struktur yang konsisten
    sendJsonResponse([
        'success' => true,
        'data' => $data,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalRows' => $totalRows,
        'search' => $search,
        'user_role' => $user_role, // Debug info
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // PERBAIKAN 14: Tangkap semua exception
    error_log("get_daftar_user.php Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendErrorResponse('Internal server error: ' . $e->getMessage());
} finally {
    // PERBAIKAN 15: Cleanup
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($countStmt)) {
        $countStmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>