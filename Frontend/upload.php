<?php
include '../Backend/session_check.php';
if (!isset($_SESSION['dataid'])) {
    $_SESSION['dataid'] = uniqid('data_', true);
}
$dataid = $_SESSION['dataid'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Halaman Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="style.css" rel="stylesheet"/>
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
        <h1 class="text-2xl font-bold mb-6">UPLOAD</h1>
        <form action="../Backend/upload_proses.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="dataid" value="<?= $dataid ?>">
    <div class="bg-green-700 text-white p-4 rounded-t-lg">
        <h2 class="font-semibold">Upload</h2>
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
        foreach ($fields as $name => $label) {
            echo "
            <div class='border rounded-lg p-4 flex justify-between items-center mb-4'>
                <div>
                    <h2 class='text-xl font-bold'>{$label} <span class='text-red-500'>*</span></h2>
                    <p>Berisi {$label} dalam bentuk PDF</p>
                </div>
                <div class='text-right'>
                    <button class='bg-green-900 text-white px-3 py-2 rounded-lg mt-2'>
                        <input type='file' name='{$name}' class='mt-1' onchange='validateUpload()'/>
                    </button>
                </div>
            </div>";
        }
        ?>
    </div>
    <div class="flex justify-between mt-6">
        <a href="preview.php" class="bg-teal-700 text-white px-4 py-2 rounded">SEBELUMNYA</a>
        <button id="submitBtn" class="bg-teal-700 text-white px-4 py-2 rounded" type="submit">SUBMIT</button>
    </div>
</form>