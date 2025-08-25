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
// Generate numeric dataid yang aman untuk kolom INT di DB
if (empty($_SESSION['dataid'])) {
    // gunakan timestamp + offset kecil untuk menjamin integer dan menghindari overflow INT
    $_SESSION['dataid'] = (int) time() + rand(0, 99);
}
// paksa integer
$dataid = (int) $_SESSION['dataid'];

// Ambil input dan simpan juga di session supaya form upload bisa menampilkannya saat edit
$input = [
    'judul' => trim($_POST['judul'] ?? ''),
    'jenis_permohonan' => trim($_POST['jenis_permohonan'] ?? ''),
    'jenis_ciptaan' => trim($_POST['jenis_ciptaan'] ?? ''),
    'uraian_singkat' => trim($_POST['uraian_singkat'] ?? ''),
    'tanggal_pertama_kali_diumumkan' => trim($_POST['tanggal_pertama_kali_diumumkan'] ?? ''),
    'kota_pertama_kali_diumumkan' => trim($_POST['kota_pertama_kali_diumumkan'] ?? ''),
    'jenis_pendanaan' => trim($_POST['jenis_pendanaan'] ?? ''),
    'nama_pendanaan' => trim($_POST['nama_pendanaan'] ?? '')
];
// simpan ke session agar form tetap terisi saat berpindah ke langkah selanjutnya (upload)
$_SESSION['input_awal'] = $input;

// Validasi minimal
if ($input['judul'] === '' || $input['jenis_ciptaan'] === '' || $input['kota_pertama_kali_diumumkan'] === '') {
    header('Location: ../Frontend/input_awal.php?m=empty');
    exit();
}

try {
    // cek apakah sudah ada record untuk dataid
    $stmt = $conn->prepare("SELECT id FROM detail_permohonan WHERE dataid = ? LIMIT 1");
    if (!$stmt) throw new Exception('DB prepare failed: ' . $conn->error);
    // dataid sekarang integer -> gunakan 'i'
    $stmt->bind_param('i', $dataid);
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
        // 8 string params + 1 integer id
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
        // 8 string params + 1 integer dataid
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
            $dataid
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception('Gagal insert detail_permohonan: ' . $err);
        }
        $stmt->close();
    }

    // Keep input in session until upload completes (simpan_uploads.php akan unset saat upload sukses)
    // Hanya unset auxiliary transient keys here
    $auxUnset = [
        'input_awal_pengusul_list',
        'data_pengusul_list',
    ];
    foreach ($auxUnset as $k) {
        if (isset($_SESSION[$k])) unset($_SESSION[$k]);
    }

    // Jika form mengirimkan tombol "Simpan & Baru" (name="save_and_new"), buat dataid baru dan arahkan ke form inputawal kosong
    if (!empty($_POST['save_and_new'])) {
        unset($_SESSION['input_awal']);
        unset($_SESSION['data_pengusul']);
        unset($_SESSION['input_awal_pengusul']);
        unset($_SESSION['pengusul']);
        unset($_SESSION['dataid']);

        // buat dataid baru (integer)
        $_SESSION['dataid'] = (int) time() + rand(0, 99);
        header('Location: ../Frontend/input_awal.php?m=ok');
        exit();
    }

    // Normal redirect: lanjut ke langkah berikutnya (input.php) tanpa mereset input_awal
    header('Location: ../Frontend/input.php?dataid=' . urlencode($dataid));
    exit();
} catch (Exception $e) {
    error_log('simpan_inputawal error: ' . $e->getMessage());
    header('Location: ../Frontend/input_awal.php?m=error&msg=' . urlencode($e->getMessage()));
    exit();
}
?>