<?php
include '../Backend/session_check.php';

// Periksa apakah dataid ada di URL
$dataid = $_GET['dataid'] ?? null;

if ($dataid === null) {
    // Jika dataid tidak ada, redirect ke halaman input_awal.php dengan pesan error
    header('Location: input_awal.php?error=Data ID tidak ditemukan');
    exit();
}

// Ambil data pengusul dari session
$data_pengusul = $_SESSION['data_pengusul'] ?? [];

// Reset data pengusul setelah ditampilkan
unset($_SESSION['data_pengusul']);

include '../koneksi.php';

// Ambil data dosen
$dosen_result = $conn->query("SELECT * FROM data_pribadi_dosen WHERE dataid = '$dataid'");
while ($row = $dosen_result->fetch_assoc()) {
    $row['role'] = 'Dosen';
    $data_pengusul[] = $row;
}

// Ambil data mahasiswa
$mhs_result = $conn->query("SELECT * FROM data_pribadi_mahasiswa WHERE dataid = '$dataid'");
while ($row = $mhs_result->fetch_assoc()) {
    $row['role'] = 'Mahasiswa';
    $data_pengusul[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Surat Permohonan Hak Cipta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Tailwind & Icon -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <!-- SweetAlert & JS eksternal -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/input.js" defer></script>
</head>
<body class="bg p-8 flex items-center justify-center min-h-screen">
    <div class="bg-gray-100 w-full max-w-5xl p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6">INPUT SURAT PERMOHONAN HAK CIPTA</h1>

        <!-- Tombol Tambah Pengusul -->
        <a href="dataid.php?dataid=<?= htmlspecialchars($dataid) ?>">
            <button class="bg-green-800 text-white px-4 py-2 rounded-lg mb-6 flex items-center">
                Tambah Pengusul <i class="fas fa-plus ml-2"></i>
            </button>
        </a>

        <!-- Daftar Pengusul -->
        <div class="space-y-4">
            <?php if (!empty($data_pengusul)): ?>
                <?php foreach ($data_pengusul as $pengusul): ?>
                    <?php if (isset($pengusul['id']) && isset($pengusul['nama'])): ?>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h2 class="text-lg font-semibold"><?= htmlspecialchars($pengusul['nama']) ?></h2>
                                    <p class="text-gray-600"><?= htmlspecialchars($pengusul['alamat']) ?>, <?= htmlspecialchars($pengusul['kode_pos']) ?></p>
                                    <p class="text-gray-600">📞 <?= htmlspecialchars($pengusul['nomor_telepon']) ?></p>
                                    <p class="text-gray-600">✉ <?= htmlspecialchars($pengusul['email']) ?></p>
                                    <p class="text-gray-600">🏫 <?= htmlspecialchars($pengusul['fakultas']) ?></p>
                                    <span class="bg-blue-600 text-white px-2 py-1 rounded-full text-sm"><?= htmlspecialchars($pengusul['role']) ?></span>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="edit.php?id=<?= urlencode($pengusul['id']) ?>&role=<?= urlencode($pengusul['role']) ?>&dataid=<?= urlencode($dataid) ?>" class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button 
                                        type="button"
                                        class="text-red-500 hover:text-red-700 hapus-btn"
                                        data-id="<?= htmlspecialchars($pengusul['id']) ?>"
                                        data-role="<?= htmlspecialchars($pengusul['role']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-600 text-center">Belum ada data pengusul ditambahkan.</p>
            <?php endif; ?>
        </div>

        <!-- Navigasi -->
        <div class="flex justify-between mt-6">
            <a href="input_awal.php?dataid=<?= htmlspecialchars($dataid) ?>">
                <button class="bg-teal-700 text-white px-4 py-2 rounded">SEBELUMNYA</button>
            </a>
            <a href="preview.php?dataid=<?= htmlspecialchars($dataid) ?>">
                <button class="bg-teal-700 text-white px-6 py-2 rounded">SELANJUTNYA</button>
            </a>
        </div>
    </div>

    <!-- Flash message -->
    <div id="flash-message"
         data-success="<?= htmlspecialchars($_GET['success'] ?? '') ?>"
         data-error="<?= htmlspecialchars($_GET['error'] ?? '') ?>">
    </div>
</body>
</html>
