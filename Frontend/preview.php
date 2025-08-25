<?php
include '../Backend/session_check.php';

// determine identifiers safely from GET or session
$dataid = $_GET['dataid'] ?? $_SESSION['dataid'] ?? null;
$mode = $_GET['mode'] ?? $_SESSION['mode'] ?? 'create';
$detail_id = $_GET['id'] ?? $_SESSION['detail_id'] ?? '';

// build previous url according to mode and available identifiers
if ($mode === 'edit' && !empty($detail_id)) {
    $prev_url = 'input.php?dataid=' . urlencode($dataid) . '&id=' . urlencode($detail_id) . '&mode=edit';
} elseif (!empty($dataid)) {
    $prev_url = 'input.php?dataid=' . urlencode($dataid);
} else {
    // fallback to input list or landing page
    $prev_url = 'input.php';
}

// build next url (keep dataid so upload page can continue the flow)
$next_url = !empty($dataid) ? 'upload.php?dataid=' . urlencode($dataid) : 'upload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preview Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body class="bg p-8 flex items-center justify-center min-h-screen">
  <div class="bg-gray-100 w-full max-w-5xl p-8 rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold mb-4">PREVIEW</h1>
    <div class="space-y-4">
      <!-- Input Pencarian -->
      <div class="relative mb-3">
        <input class="p-2 rounded-lg border border-gray-100 w-full" placeholder="Cari Pertanyaan" type="text">
      </div>

      <!-- Surat Pertanyaan -->
      <div class="bg-white p-4 rounded-lg shadow flex justify-between items-start">
        <div class="flex items-start">
          <img alt="User avatar" class="w-12 h-12 rounded-full mr-4" src="https://storage.googleapis.com/a1aa/image/ceZ5NplVgBw1NisSB2vHj1dQU5gLwGgogtKfOd9i6hu3MMIUA.jpg">
          <div>
            <h2 class="text-lg font-bold">Surat Pertanyaan</h2>
            <p class="text-sm">SP berisi Surat Pernyataan yang berisi judul buku dan tanda tangan</p>
            <a href="../Backend/index.php" target="_blank">
            <button class="bg-blue-500 text-white px-4 py-2 rounded">Download PDF</button>
            </a>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-500">12/10/2022</p>
          <i class="fas fa-ellipsis-v text-gray-500"></i>
        </div>
      </div>

      <!-- Surat Pengalihan Hak -->
      <div class="bg-white p-4 rounded-lg shadow flex justify-between items-start">
        <div class="flex items-start">
          <img alt="User avatar" class="w-12 h-12 rounded-full mr-4" src="https://storage.googleapis.com/a1aa/image/ceZ5NplVgBw1NisSB2vHj1dQU5gLwGgogtKfOd9i6hu3MMIUA.jpg">
          <div>
            <h2 class="text-lg font-bold">Surat Pengalihan Hak</h2>
            <p class="text-sm">SPH berisi surat pengalihan hak yang berisi seluruh pengusul dan tanda tangan sesuai format yang ada</p>
            <a href="../Backend/sph.php">
              <button class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Download PDF</button>
            </a>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm text-gray-500">12/10/2022</p>
          <i class="fas fa-ellipsis-v text-gray-500"></i>
        </div>
      </div>

      <!-- Informasi Pembayaran -->
      <div class="bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-bold">Pembayaran: Rp. 200.000,-</h2>
        <p class="text-sm">
          Pembayaran dapat dilakukan dengan mentransfer ke nomor rekening:
          <br>1. BCA (###11)
          <br>2. BNI (333##)
          <br>3. BRI (66###)
        </p>
      </div>
    </div>

    <!-- Navigasi -->
    <div class="flex justify-between mt-4">
      <a href="<?= htmlspecialchars($prev_url, ENT_QUOTES, 'UTF-8') ?>">
        <button class="bg-teal-700 text-white px-4 py-2 rounded">SEBELUMNYA</button>
      </a>
      <a href="<?= htmlspecialchars($next_url, ENT_QUOTES, 'UTF-8') ?>">
        <button class="bg-teal-700 text-white px-4 py-2 rounded">SELANJUTNYA</button>
      </a>
    </div>
  </div>
</body>
</html>