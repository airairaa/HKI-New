<?php
// File debug untuk test koneksi database dan session
session_start();

// Set header JSON
header('Content-Type: application/json');

$debug_info = [];

// Test 1: Session info
$debug_info['session'] = [
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'role' => $_SESSION['role'] ?? 'NOT SET',
    'session_id' => session_id(),
    'all_session_vars' => $_SESSION
];

// Test 2: Include koneksi.php
try {
    if (file_exists('koneksi.php')) {
        define('API_REQUEST', true);
        include 'koneksi.php';
        $debug_info['koneksi_file'] = 'EXISTS';
        
        // Test connection
        if (isset($conn)) {
            $debug_info['connection'] = [
                'object_exists' => true,
                'connect_error' => $conn->connect_error,
                'connection_error_var' => isset($connection_error) ? $connection_error : 'NOT SET'
            ];
            
            // Test simple query
            $test_query = $conn->query("SELECT 1 as test");
            if ($test_query) {
                $debug_info['connection']['test_query'] = 'SUCCESS';
                $debug_info['connection']['server_info'] = $conn->server_info;
            } else {
                $debug_info['connection']['test_query'] = 'FAILED: ' . $conn->error;
            }
        } else {
            $debug_info['connection'] = [
                'object_exists' => false,
                'error' => 'conn variable not set after include'
            ];
        }
    } else {
        $debug_info['koneksi_file'] = 'NOT EXISTS';
    }
} catch (Exception $e) {
    $debug_info['koneksi_error'] = $e->getMessage();
}

// Test 3: Database tables
if (isset($conn) && !$conn->connect_error) {
    try {
        // Check if tables exist
        $tables_to_check = ['detail_permohonan', 'uploads', 'review_ad'];
        $debug_info['database_tables'] = [];
        
        foreach ($tables_to_check as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            $debug_info['database_tables'][$table] = $result && $result->num_rows > 0 ? 'EXISTS' : 'NOT EXISTS';
        }
        
        // Check if there's any data
        $result = $conn->query("SELECT COUNT(*) as count FROM detail_permohonan");
        if ($result) {
            $row = $result->fetch_assoc();
            $debug_info['data_count'] = $row['count'];
        }
        
    } catch (Exception $e) {
        $debug_info['database_test_error'] = $e->getMessage();
    }
}

// Test 4: File paths
$debug_info['file_paths'] = [
    'current_file' => __FILE__,
    'current_dir' => __DIR__,
    'koneksi_path' => __DIR__ . '/koneksi.php',
    'koneksi_exists' => file_exists(__DIR__ . '/koneksi.php'),
    'get_daftar_user_exists' => file_exists(__DIR__ . '/get_daftar_user.php')
];

// Test 5: Server info
$debug_info['server'] = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'UNKNOWN',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN'
];

// Output debug info
echo json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>