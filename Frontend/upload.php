<?php
include '../Backend/session_check.php';
require_once __DIR__ . '/../Backend/koneksi.php';

// Jika upload selesai sebelumnya, reset dataid sehingga mode kembali ke 'create'
if (!empty($_SESSION['upload_complete'])) {
    unset($_SESSION['dataid']);
    unset($_SESSION['upload_complete']);
}

// maintain dataid in session; prefer explicit GET param from input_awal
if (!empty($_GET['dataid'])) {
    $_SESSION['dataid'] = $_GET['dataid'];
} elseif (!isset($_SESSION['dataid']) || empty($_SESSION['dataid'])) {
    $_SESSION['dataid'] = uniqid('data_', true);
}
$dataid = $_SESSION['dataid'];

// default mode from GET, will be upgraded to 'edit' below if a record exists
$mode = (isset($_GET['mode']) && $_GET['mode'] === 'edit') ? 'edit' : 'create';

// fetch existing uploads row for this dataid (if any)
$existing = [
    'id' => 0,
    'file_sp' => '',
    'file_sph' => '',
    'file_contoh_karya' => '',
    'file_ktp' => '',
    'file_bukti_pembayaran' => ''
];

if (!empty($dataid)) {
    $stmt = $conn->prepare('SELECT id, file_sp, file_sph, file_contoh_karya, file_ktp, file_bukti_pembayaran FROM uploads WHERE dataid = ? ORDER BY id DESC LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $dataid); // dataid may be string (uniqid) or int
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $existing = array_merge($existing, $row);
            // jika ada record di DB berarti halaman ini harus dalam mode edit
            $mode = 'edit';
        }
        $stmt->close();
    }
}
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
        const MODE = '<?php echo $mode; ?>'; // 'edit' or 'create'
        // expose which files already exist so client validation can make per-file optional
        const EXISTING_FILES = <?php echo json_encode([
            'file_sp' => !empty($existing['file_sp']),
            'file_sph' => !empty($existing['file_sph']),
            'file_contoh_karya' => !empty($existing['file_contoh_karya']),
            'file_ktp' => !empty($existing['file_ktp']),
            'file_bukti_pembayaran' => !empty($existing['file_bukti_pembayaran'])
        ]); ?>;
        function redirectToDaftarUser() {
            window.location.href = "daftar_user.php";
            return true;
        }
    </script>
</head>
<body class="bg flex p-8 items-center justify-center min-h-screen">
    <div class="bg-gray-100 w-full max-w-5xl p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6">UPLOAD DOKUMEN</h1>

        <form action="../Backend/simpan_uploads.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm(event)">
            <input type="hidden" name="dataid" value="<?= htmlspecialchars($dataid) ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

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
                    $existingFile = $existing[$name] ?? '';
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
                                    <?php if (!empty($existingFile)): ?>
                                        <div class="mt-2 text-sm text-gray-700">
                                            File sebelumnya:
                                            <a target="_blank" href="../Backend/view_file.php?filename=<?= urlencode($existingFile) ?>&type=<?= urlencode($name) ?>" class="text-blue-600"><?= htmlspecialchars($existingFile) ?></a>
                                            <span class="text-sm text-gray-600"></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2 text-sm text-gray-600">Belum ada file sebelumnya.</div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <input
                                        type="file"
                                        name="<?= $name ?>"
                                        id="<?= $name ?>"
                                        accept="application/pdf"
                                        class="hidden"
                                        onchange="validateFile(this)">
                                    <label for="<?= $name ?>" class="bg-green-700 text-white px-4 py-2 rounded cursor-pointer hover:bg-green-800 transition-colors inline-flex items-center">
                                        <i class="fas fa-upload mr-2"></i>
                                        Pilih File
                                    </label>
                                </div>
                            </div>
                            <div id="<?= $name ?>_info" class="mt-2 text-sm text-gray-500"></div>
                            <!-- send existing upload id so backend can update instead of insert -->
                            <input type="hidden" name="upload_id_<?= $name ?>" value="<?= (int)$existing['id'] ?>">
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
            } else {
                fileInfo.textContent = '';
            }
        }

        function validateForm(event) {
            event.preventDefault();

            const requiredFields = ['file_sp', 'file_sph', 'file_contoh_karya', 'file_ktp', 'file_bukti_pembayaran'];

            // If creating, require all files
            if (MODE !== 'edit') {
                const missingFiles = requiredFields.filter(id => !document.getElementById(id).files[0]);
                if (missingFiles.length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File Belum Lengkap',
                        text: 'Mohon upload semua file yang diperlukan!'
                    });
                    return false;
                }
            } else {
                // edit mode: require only fields that do NOT already have existing files
                const labelMap = {
                    file_sp: 'Surat Pernyataan',
                    file_sph: 'Surat Pengalihan Hak Cipta',
                    file_contoh_karya: 'Contoh Karya Dan Uraian',
                    file_ktp: 'Scan KTP',
                    file_bukti_pembayaran: 'Bukti Pembayaran'
                };
                const missing = [];
                requiredFields.forEach(id => {
                    const hasNew = document.getElementById(id).files[0];
                    const hasExisting = !!EXISTING_FILES[id];
                    if (!hasNew && !hasExisting) missing.push(labelMap[id] || id);
                });
                if (missing.length > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File Belum Lengkap',
                        html: `Mohon upload file berikut yang belum ada: <br/><strong>${missing.join('<br/>')}</strong>`
                    });
                    return false;
                }
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