<?php
include '../Backend/session_check.php';
$input_awal = $_SESSION['input_awal'] ?? [
    'judul' => '',
    'jenis_permohonan' => '',
    'jenis_ciptaan' => '',
    'uraian_singkat' => '',
    'tanggal_pertama_kali_diumumkan' => '',
    'kota_pertama_kali_diumumkan' => '',
    'jenis_pendanaan' => '', 
    'nama_pendanaan' => '' 
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Input Surat Permohonan Hak Cipta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/inputawal.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="style.css" rel="stylesheet" />
</head>
<body class="bg p-9 flex justify-center items-center min-h-screen">
    <div class="bg-gray-100 w-full max-w-7xl p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-6">INPUT SURAT PERMOHONAN HAK CIPTA</h1>

        <form action="../Backend/simpan_inputawal.php" method="post">
            <div class="bg-green-700 text-white p-4 rounded-t-lg">
                <h2 class="font-semibold">Detail Permohonan</h2>
            </div>
            <div class="bg-white p-6 rounded-b-lg shadow-md mb-6">
                <div class="container">
                    <!-- Jenis Permohonan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="jenis_permohonan">Jenis Permohonan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <select name="jenis_permohonan" class="w-full mt-1 p-2 border rounded" required>
                                <option value="">Pilih Jenis Permohonan</option>
                                <option value="UMK" <?php echo $input_awal['jenis_permohonan'] === 'UMK' ? 'selected' : ''; ?>>UMK, Lembaga Pendidikan, Lembaga Litbang Pemerintah</option>
                                <option value="Umum" <?php echo $input_awal['jenis_permohonan'] === 'Umum' ? 'selected' : ''; ?>>Umum</option>
                            </select>
                        </div>
                    </div>

                    <!-- Jenis Ciptaan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="jenis_ciptaan" class="block text-gray-700">Jenis Ciptaan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <select name="jenis_ciptaan" id="jenis_ciptaan" class="w-full mt-1 p-2 border rounded" required>
                                <option value="">Pilih Jenis Ciptaan</option>
                                <option value="Karya Tulis" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Tulis' ? 'selected' : ''; ?>>Karya Tulis</option>
                                <option value="Karya Seni" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Seni' ? 'selected' : ''; ?>>Karya Seni</option>
                                <option value="Komposisi Musik" <?php echo $input_awal['jenis_ciptaan'] === 'Komposisi Musik' ? 'selected' : ''; ?>>Komposisi Musik</option>
                                <option value="Karya Audio Visual" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Audio Visual' ? 'selected' : ''; ?>>Karya Audio Visual</option>
                                <option value="Karya Fotografi" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Fotografi' ? 'selected' : ''; ?>>Karya Fotografi</option>
                                <option value="Karya Drama & Koreografi" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Drama & Koreografi' ? 'selected' : ''; ?>>Karya Drama & Koreografi</option>
                                <option value="Karya Rekaman" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Rekaman' ? 'selected' : ''; ?>>Karya Rekaman</option>
                                <option value="Karya Lainnya" <?php echo $input_awal['jenis_ciptaan'] === 'Karya Lainnya' ? 'selected' : ''; ?>>Karya Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sub-Jenis Ciptaan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="sub_jenis_ciptaan" class="block text-gray-700">Sub-Jenis Ciptaan</label>
                        </div>
                        <div class="col-8">
                            <select name="sub_jenis_ciptaan" id="sub_jenis_ciptaan" class="w-full mt-1 p-2 border rounded">
                                <option value="">Pilih Sub-Jenis Ciptaan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Judul -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="judul" class="block text-gray-700">Judul <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <input type="text" name="judul" class="w-full mt-1 p-2 border rounded" placeholder="Masukkan judul" value="<?php echo htmlspecialchars($input_awal['judul']); ?>" required />
                        </div>
                    </div>

                    <!-- Uraian Singkat Ciptaan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="uraian_singkat" class="block text-gray-700">Uraian Singkat Ciptaan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <input type="text" name="uraian_singkat" class="w-full mt-1 p-2 border rounded" placeholder="Masukkan uraian singkat ciptaan" value="<?php echo htmlspecialchars($input_awal['uraian_singkat']); ?>" required />
                        </div>
                    </div>

                    <!-- Tanggal Pertama Kali Diumumkan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="tanggal_pertama_kali_diumumkan" class="block text-gray-700">Tanggal Pertama Kali Diumumkan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <input type="date" name="tanggal_pertama_kali_diumumkan" class="w-full mt-1 p-2 border rounded" value="<?php echo htmlspecialchars($input_awal['tanggal_pertama_kali_diumumkan']); ?>" required />
                        </div>
                    </div>

                    <!-- Negara Pertama Kali Diumumkan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="negara_pertama_kali_diumumkan" class="block text-gray-700">Negara Pertama Kali Diumumkan</label>
                        </div>
                        <div class="col-8">
                            <input type="text" name="negara_pertama_kali_diumumkan" class="w-full mt-1 p-2 border rounded" value="INDONESIA" readonly />
                        </div>
                    </div>

                    <!-- Kota Pertama Kali Diumumkan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="kota_pertama_kali_diumumkan" class="block text-gray-700">Kota Pertama Kali Diumumkan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <input type="text" name="kota_pertama_kali_diumumkan" class="w-full mt-1 p-2 border rounded" placeholder="Masukkan Kota" value="<?php echo htmlspecialchars($input_awal['kota_pertama_kali_diumumkan']); ?>" required />
                        </div>
                    </div>

                    <!-- Jenis Pendanaan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="jenisHibah" class="block text-gray-700">Jenis Pendanaan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <select id="jenisHibah" name="jenis_hibah" class="w-full mt-1 p-2 border rounded" onchange="showHibahOptions()" required>
                                <option value="">Pilih Jenis Pendanaan</option>
                                <option value="internal" <?php echo $input_awal['jenis_pendanaan'] === 'internal' ? 'selected' : ''; ?>>Internal</option>
                                <option value="eksternal" <?php echo $input_awal['jenis_pendanaan'] === 'eksternal' ? 'selected' : ''; ?>>Eksternal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Nama Pendanaan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="hibah" class="block text-gray-700 hidden" id="hibahLabel">Nama Pendanaan <span class="text-red-500">*</span></label>
                        </div>
                        <div class="col-8">
                            <select id="hibah" name="nama_pendanaan" class="w-full mt-1 p-2 border rounded hidden">
                                <option value="">Pilih Hibah</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tampilan Pendanaan -->
                    <div class="row mb-3">
                        <div class="col-4">
                            <label id="pendanaanLabel" class="block text-gray-700 hidden">Pendanaan:</label>
                        </div>
                        <div class="col-8">
                            <span id="pendanaanValue" class="text-blue-600 font-bold hidden"></span>
                        </div>
                    </div>

                    <input type="hidden" id="pendanaanHidden" name="pendanaanHidden" />
                </div>
            </div>

            <!-- Data Pemegang Hak Cipta -->
            <div class="bg-green-700 text-white p-4 rounded-t-lg">
                <h2 class="font-semibold">Data Pemegang Hak Cipta</h2>
            </div>
            <div class="bg-white p-6 rounded-b-lg shadow-md mb-6">
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-2">ID</th>
                            <th class="border p-2">Nama</th>
                            <th class="border p-2">Email</th>
                            <th class="border p-2">No. Telp</th>
                            <th class="border p-2">Kewarganegaraan</th>
                            <th class="border p-2">Alamat</th>
                            <th class="border p-2">Negara</th>
                            <th class="border p-2">Provinsi</th>
                            <th class="border p-2">Kota</th>
                            <th class="border p-2">Kecamatan</th>
                            <th class="border p-2">Kode Pos</th>
                            <th class="border p-2">Pemegang Hakcipta</th>
                        </tr>
                    </thead>
                    <tbody id="penciptaTableBody">
                        <tr><td colspan="12" class="border p-4 text-center">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between mt-4">
                <a href="<?php echo htmlspecialchars($prev_url, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="button" class="bg-green-800 text-white px-4 py-2 rounded">SEBELUMNYA</button>
                </a>
                <a href="input.php?dataid=<?= htmlspecialchars($dataid) ?>">
                    <button class="bg-teal-700 text-white px-6 py-2 rounded">SELANJUTNYA</button>
                </a>
            </div>
        </form>
    </div>

    <script src="../js/inputawal.js"></script>
</body>
</html>
