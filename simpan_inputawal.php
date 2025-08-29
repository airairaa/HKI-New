<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Frontend/input_awal.php');
    exit();
}

// Pastikan ada dataid di session (dipakai untuk korelasi dengan uploads)
if (empty($_SESSION['dataid'])) {
    $_SESSION['dataid'] = uniqid('data_', true);
}
$dataid = $_SESSION['dataid'];

// Ambil input dan simpan juga di session supaya form upload bisa menampilkannya saat edit
$input = [
    'judul' => trim($_POST['judul'] ?? $_POST['judul_input'] ?? $_SESSION['input_awal']['judul'] ?? ''),
    'jenis_permohonan' => trim($_POST['jenis_permohonan'] ?? $_SESSION['input_awal']['jenis_permohonan'] ?? ''),
    'jenis_ciptaan' => trim($_POST['jenis_ciptaan'] ?? $_POST['jenis_ciptaan_input'] ?? $_SESSION['input_awal']['jenis_ciptaan'] ?? ''),
    'uraian_singkat' => trim($_POST['uraian_singkat'] ?? $_SESSION['input_awal']['uraian_singkat'] ?? ''),
    // beberapa versi front-end pernah menggunakan nama berbeda — coba semua kemungkinan
    'tanggal_pertama_kali_diumumkan' => trim(
        $_POST['tanggal_pertama_kali_diumumkan']
        ?? $_POST['tanggal_pertama_kali_diumumukan']
        ?? $_POST['tanggal_pertama']
        ?? $_SESSION['input_awal']['tanggal_pertama_kali_diumumkan']
        ?? ''
    ),
    'kota_pertama_kali_diumumkan' => trim(
        $_POST['kota_pertama_kali_diumumkan']
        ?? $_POST['kota_pertama_kali_diumumkan']   // kept for legacy typos
        ?? $_POST['kota_pertama_kali_diumumumumkan'] // older typo
        ?? $_POST['kota']
        ?? $_SESSION['input_awal']['kota_pertama_kali_diumumkan']
        ?? ''
    ),
    'jenis_pendanaan' => trim($_POST['jenis_pendanaan'] ?? $_SESSION['input_awal']['jenis_pendanaan'] ?? ''),
    'nama_pendanaan' => trim($_POST['nama_pendanaan'] ?? $_POST['hibah'] ?? $_SESSION['input_awal']['nama_pendanaan'] ?? '')
];
// simpan ke session agar form tetap terisi saat berpindah ke langkah selanjutnya (upload)
$_SESSION['input_awal'] = $input;

// Validasi minimal
$missing = [];
if ($input['judul'] === '') $missing[] = 'Judul';
if ($input['jenis_ciptaan'] === '') $missing[] = 'Jenis Ciptaan';
if ($input['kota_pertama_kali_diumumkan'] === '') $missing[] = 'Kota Pertama Kali Diumumkan';

if (!empty($missing)) {
    // redirect dengan pesan yang lebih informatif (tetap menggunakan m=empty for UX compatibility)
    $msg = 'Field kosong: ' . implode(', ', $missing);
    header('Location: ../Frontend/input_awal.php?m=empty&msg=' . urlencode($msg));
    exit();
}

try {
    // cek apakah sudah ada record untuk dataid
    $stmt = $conn->prepare("SELECT id FROM detail_permohonan WHERE dataid = ? LIMIT 1");
    if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
    $stmt->bind_param('s', $dataid);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = false;
    $existingId = 0;
    if ($row = $res->fetch_assoc()) {
        $exists = true;
        $existingId = (int)$row['id'];
    }
    $stmt->close();

    if ($exists) {
        // UPDATE (tanpa kolom timestamp yang mungkin tidak ada di skema)
        $sql = "UPDATE detail_permohonan
                SET jenis_permohonan = ?, jenis_ciptaan = ?, judul = ?, uraian_singkat = ?, tanggal_pertama_kali_diumumkan = ?, kota_pertama_kali_diumumkan = ?, jenis_pendanaan = ?, jenis_hibah = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
        $v1 = $input['jenis_permohonan'];
        $v2 = $input['jenis_ciptaan'];
        $v3 = $input['judul'];
        $v4 = $input['uraian_singkat'];
        $v5 = $input['tanggal_pertama_kali_diumumkan'];
        $v6 = $input['kota_pertama_kali_diumumkan'];
        $v7 = $input['jenis_pendanaan'];
        $v8 = $input['nama_pendanaan'];
        $stmt->bind_param(
            "ssssssssi",
            $v1,
            $v2,
            $v3,
            $v4,
            $v5,
            $v6,
            $v7,
            $v8,
            $existingId
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal update detail_permohonan: ' . $err);
        }
        $stmt->close();
    } else {
        // INSERT
        $sql = "INSERT INTO detail_permohonan (jenis_permohonan, jenis_ciptaan, judul, uraian_singkat, tanggal_pertama_kali_diumumkan, kota_pertama_kali_diumumkan, jenis_pendanaan, jenis_hibah, dataid)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
        $v1 = $input['jenis_permohonan'];
        $v2 = $input['jenis_ciptaan'];
        $v3 = $input['judul'];
        $v4 = $input['uraian_singkat'];
        $v5 = $input['tanggal_pertama_kali_diumumkan'];
        $v6 = $input['kota_pertama_kali_diumumkan'];
        $v7 = $input['jenis_pendanaan'];
        $v8 = $input['nama_pendanaan'];
        $stmt->bind_param(
            "sssssssss",
            $v1,
            $v2,
            $v3,
            $v4,
            $v5,
            $v6,
            $v7,
            $v8,
            $dataid
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal insert detail_permohonan: ' . $err);
        }
        $stmt->close();
    }

    // Clear only auxiliary transient session keys if any (keep main form data)
    $auxUnset = [
        'input_awal_pengusul_list',
        'data_pengusul_list',
    ];
    foreach ($auxUnset as $k) {
        if (isset($_SESSION[$k])) unset($_SESSION[$k]);
    }

    // Jika form mengirimkan tombol "Simpan & Baru" (name="save_and_new"), buat dataid baru dan arahkan ke form inputawal kosong
    if (!empty($_POST['save_and_new'])) {
        // bersihkan data form agar form benar-benar kosong untuk input baru
        unset($_SESSION['input_awal']);
        unset($_SESSION['data_pengusul']);
        unset($_SESSION['input_awal_pengusul']);
        unset($_SESSION['pengusul']);
        unset($_SESSION['dataid']);

        // buat dataid baru
        $_SESSION['dataid'] = uniqid('data_', true);
        header('Location: ../Frontend/input_awal.php?m=ok');
        exit();
    }

    // Normal redirect: lanjut ke langkah berikutnya (input.php) tanpa mereset input_awal
    // Pastikan mode edit tetap diarahkan ke input.php?dataid=... agar user tidak kehilangan alur edit
    header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid));
    exit();
} catch (Exception $e) {
    error_log('simpan_inputawal error: ' . $e->getMessage());
    header('Location: ../Frontend/input_awal.php?m=error&msg=' . urlencode($e->getMessage()));
    exit();
}
?>