<?php
// koneksi.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Toggle debug (set false di production)
if (!defined('DEBUG_MODE')) define('DEBUG_MODE', false);

// DB config - sesuaikan jika perlu
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hki';
$db_port = 3306;

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_errno) {
    if (defined('API_REQUEST')) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed', 'error' => $conn->connect_error]);
        exit;
    }
    die("Database connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    error_log("Warning: couldn't set charset utf8mb4: " . $conn->error);
}

function dbDebug($msg) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) return;
    $entry = "[".date('Y-m-d H:i:s')."] ".$msg.PHP_EOL;
    error_log($entry, 3, __DIR__ . '/database_debug.log');
    if (!defined('API_REQUEST')) echo "<pre>".htmlspecialchars($entry)."</pre>";
}
?>