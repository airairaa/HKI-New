<?php
session_start();
include 'koneksi.php';

echo "<h2>Database Check Results</h2>\n";

// Check session
echo "<h3>Session Information:</h3>\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NULL') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NULL') . "\n\n";

// Check users table
echo "<h3>Users Table:</h3>\n";
$result = $conn->query("SELECT id, username, role FROM users LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Username: {$row['username']}, Role: {$row['role']}\n";
    }
} else {
    echo "Error querying users table: " . $conn->error . "\n";
}
echo "\n";

// Check detail_permohonan table
echo "<h3>Detail Permohonan Table (first 10 records):</h3>\n";
$result = $conn->query("SELECT id, judul, user_id, created_at FROM detail_permohonan ORDER BY id DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Judul: {$row['judul']}, User ID: {$row['user_id']}, Created: {$row['created_at']}\n";
    }
} else {
    echo "Error querying detail_permohonan table: " . $conn->error . "\n";
}
echo "\n";

// Check uploads table
echo "<h3>Uploads Table (first 5 records):</h3>\n";
$result = $conn->query("SELECT id, dataid, file_ktp, file_contoh_karya FROM uploads ORDER BY id DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Data ID: {$row['dataid']}, KTP: {$row['file_ktp']}, Karya: {$row['file_contoh_karya']}\n";
    }
} else {
    echo "Error querying uploads table: " . $conn->error . "\n";
}
echo "\n";

// Check review_ad table
echo "<h3>Review AD Table (first 5 records):</h3>\n";
$result = $conn->query("SELECT id, detailpermohonan_id, status FROM review_ad ORDER BY id DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Detail ID: {$row['detailpermohonan_id']}, Status: {$row['status']}\n";
    }
} else {
    echo "Error querying review_ad table: " . $conn->error . "\n";
}
echo "\n";

// Test the actual query that get_daftar_user.php uses
echo "<h3>Testing Actual Query:</h3>\n";
$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? '';

echo "User ID: $user_id, Role: $user_role\n";

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

if ($user_role !== 'admin') {
    $sql .= " AND dp.user_id = $user_id";
}

$sql .= " ORDER BY dp.id DESC LIMIT 5";

echo "SQL Query: $sql\n\n";

$result = $conn->query($sql);
if ($result) {
    echo "Query Results:\n";
    while ($row = $result->fetch_assoc()) {
        echo "Detail ID: {$row['detail_id']}, Judul: {$row['judul']}, Status: {$row['status']}\n";
    }
} else {
    echo "Error executing query: " . $conn->error . "\n";
}

$conn->close();
?> 