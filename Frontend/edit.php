<?php
include '../Backend/session_check.php';

// Ambil data dari session
$data_pengusul = $_SESSION['data_pengusul'] ?? [];

// Ambil ID pengusul yang akan diedit
$id = $_GET['id'] ?? null;

// Validasi jika ID tidak ditemukan
if (!$id) {
    header('Location: input.php?error=ID tidak ditemukan');
    exit();
}

// Pastikan $data_pengusul tidak kosong
if (empty($data_pengusul)) {
    header('Location: input.php?error=Data pengusul kosong');
    exit();
}

// Cari data pengusul berdasarkan ID
$pengusul = null;
foreach ($data_pengusul as $key => $item) {
    if (isset($item['id']) && $item['id'] == $id) {
        $pengusul = $item;
        $pengusul['key'] = $key; // Simpan index array untuk update
        break;
    }
}

// Jika data tidak ditemukan, redirect kembali ke input.php
if (!$pengusul) {
    header('Location: input.php?error=Data pengusul tidak ditemukan');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengusul</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg p-8 flex items-center justify-center min-h-screen">
    <div class="bg-gray-100 w-full max-w-3xl p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6">Edit Data Pengusul</h1>
        <form action="../Backend/proses_edit_pengusul.php" method="POST">
            <input type="hidden" name="key" value="<?= $pengusul['key'] ?>">
            <input type="hidden" name="id" value="<?= $pengusul['id'] ?>">
            <div class="mb-4">
                <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($pengusul['nama']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                <input type="text" id="alamat" name="alamat" value="<?= htmlspecialchars($pengusul['alamat']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label for="kode_pos" class="block text-sm font-medium text-gray-700">Kode Pos</label>
                <input type="text" id="kode_pos" name="kode_pos" value="<?= htmlspecialchars($pengusul['kode_pos']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($pengusul['nomor_telepon']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($pengusul['email']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>
            <div class="form-group row">
                    <label class="col-4 font-semibold">Fakultas</label>
                    <div class="col-8">
                    <select name="fakultas" class="w-full mt-1 p-2 border rounded" required>
                        <option value="">Pilih Fakultas</option>
                        <option value="Fakultas Teknologi Mineral dan Energi" <?= isset($pengusul['fakultas']) && $pengusul['fakultas'] === 'Fakultas Teknologi Mineral dan Energi' ? 'selected' : '' ?>>Fakultas Teknologi Mineral dan Energi</option>
                        <option value="Fakultas Ekonomi dan Bisnis" <?= isset($pengusul['fakultas']) && $pengusul['fakultas'] === 'Fakultas Ekonomi dan Bisnis' ? 'selected' : '' ?>>Fakultas Ekonomi dan Bisnis</option>
                        <option value="Fakultas Pertanian" <?= isset($pengusul['fakultas']) && $pengusul['fakultas'] === 'Fakultas Pertanian' ? 'selected' : '' ?>>Fakultas Pertanian</option>
                        <option value="Fakultas Teknik Industri" <?= isset($pengusul['fakultas']) && $pengusul['fakultas'] === 'Fakultas Teknik Industri' ? 'selected' : '' ?>>Fakultas Teknik Industri</option>
                        <option value="Fakultas Ilmu Sosial dan Ilmu Politik" <?= isset($pengusul['fakultas']) && $pengusul['fakultas'] === 'Fakultas Ilmu Sosial dan Ilmu Politik' ? 'selected' : '' ?>>Fakultas Ilmu Sosial dan Ilmu Politik</option>
                    </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="role" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="role" name="role" class="w-full mt-1 p-2 border rounded" required>
                        <option value="Mahasiswa" <?= isset($pengusul['role']) && $pengusul['role'] == 'Mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                        <option value="Dosen" <?= isset($pengusul['role']) && $pengusul['role'] == 'Dosen' ? 'selected' : '' ?>>Dosen</option>
                    </select>
                </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-green-800 text-white px-4 py-2 rounded">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>