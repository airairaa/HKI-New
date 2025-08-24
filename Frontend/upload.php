<?php
include '../Backend/session_check.php';

// Prefer explicit dataid passed via GET (from input_awal edit flow).
// If provided, set it into session so it won't be reset when user navigates back.
// Accept numeric or string identifiers used in the app.
if (!empty($_GET['dataid'])) {
    $_SESSION['dataid'] = $_GET['dataid'];
} elseif (!isset($_SESSION['dataid']) || empty($_SESSION['dataid'])) {
    // create new dataid for new submissions
    $_SESSION['dataid'] = uniqid('data_', true);
}
$dataid = $_SESSION['dataid'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Halaman Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function redirectToDaftarUser() {
            // Redirect ke halaman daftar_user setelah submit
            window.location.href = "daftar_user.php";
            return true; // Tetap kirim form
        }
    </script>
</head>

<body class="bg flex p-8 items-center justify-center min-h-screen">
    <div class="bg-gray-100 w-full max-w-5xl p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6">UPLOAD DOKUMEN</h1>

        <form action="../Backend/simpan_uploads.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm(event)">
            <input type="hidden" name="dataid" value="<?= htmlspecialchars($dataid) ?>">

            <div class="bg-green-700 text-white p-4 rounded-t-lg">
                <h2 class="font-semibold">Upload Dokumen</h2>
                <p class="text-sm">Format: PDF, Maksimal 2MB per file</p>
            </div>

            <div class="bg-white p-6 rounded-b-lg shadow-md mb-6">
                <?php
                $fields = [
                    'file_sp' => 'Surat Pernyataan',
                    'file_sph' => 'Surat Pengalihan Hak Cipta',
                    'file_contoh_karya' => 'Contoh Karya Dan Uraian',
                    'file_ktp' => 'Scan KTP',
                    'file_bukti_pembayaran' => 'Bukti Pembayaran'
                ];

                foreach ($fields as $name => $label):
                ?>
                    <div class="mb-6 last:mb-0">
                        <div class="border rounded-lg p-4 transition-shadow hover:shadow-md">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <?= htmlspecialchars($label) ?>
                                        <span class="text-red-500">*</span>
                                    </h3>
                                    <p class="text-sm text-gray-600">Format PDF, maksimal 2MB</p>
                                </div>
                                <div>
                                    <input
                                        type="file"
                                        name="<?= $name ?>"
                                        id="<?= $name ?>"
                                        accept="application/pdf"
                                        class="hidden"
                                        onchange="validateFile(this)"
                                        required>
                                    <label for="<?= $name ?>" class="bg-green-700 text-white px-4 py-2 rounded cursor-pointer hover:bg-green-800 transition-colors inline-flex items-center">
                                        <i class="fas fa-upload mr-2"></i>
                                        Pilih File
                                    </label>
                                </div>
                            </div>
                            <div id="<?= $name ?>_info" class="mt-2 text-sm text-gray-500"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-between mt-6">
                <a href="preview.php" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>KEMBALI
                </a>
                <button
                    type="submit"
                    class="bg-green-700 text-white px-6 py-2 rounded hover:bg-green-800 transition-colors">
                    <i class="fas fa-check mr-2"></i>UPLOAD
                </button>
            </div>
        </form>
    </div>

    <script>
        function validateFile(input) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            const fileInfo = document.getElementById(`${input.id}_info`);

            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (file.type !== 'application/pdf') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format File Salah',
                        text: 'Hanya file PDF yang diperbolehkan!'
                    });
                    input.value = '';
                    fileInfo.textContent = '';
                    return false;
                }

                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar',
                        text: 'Ukuran file maksimal 2MB!'
                    });
                    input.value = '';
                    fileInfo.textContent = '';
                    return false;
                }

                fileInfo.textContent = `File terpilih: ${file.name}`;
                return true;
            }
        }

        function validateForm(event) {
            event.preventDefault();

            const requiredFiles = ['file_sp', 'file_sph', 'file_contoh_karya', 'file_ktp', 'file_bukti_pembayaran'];
            const missingFiles = requiredFiles.filter(id => !document.getElementById(id).files[0]);

            if (missingFiles.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Belum Lengkap',
                    text: 'Mohon upload semua file yang diperlukan!'
                });
                return false;
            }

            Swal.fire({
                title: 'Konfirmasi Upload',
                text: 'Pastikan semua file yang diupload sudah benar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Upload',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });

            return false;
        }
    </script>
</body>

</html>