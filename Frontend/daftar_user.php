<?php
include '../Backend/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="../css/style.css" rel="stylesheet" />
    <title>Daftar Permohonan User</title>
    
</head>

<body class="bg p-8 flex items-center justify-center min-h-screen">
    <div class="bg-gray-100 w-full max-w-5xl p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-4">DAFTAR PERMOHONAN USER</h1>
        <div class="w-full flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-700">Selamat datang</h2>
            <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition flex items-center">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>

        <div class="flex items-center justify-between mb-4 gap-4">
            <input id="searchInput" class="p-2 rounded-lg border border-gray-300 w-1/2" placeholder="Cari Judul..." type="text" />
            <a href="input_awal.php">
                <button class="bg-green-700 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-plus mr-2"></i> Ajukan Permohonan
                </button>
            </a>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">JUDUL</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">SCAN KTP</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">CONTOH KARYA</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">SURAT PERNYATAAN</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">SURAT PENGALIHAN HAK CIPTA</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">BUKTI BAYAR</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">STATUS</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="permohonanUserTableBody">
                        <tr>
                            <td colspan="8" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Loading data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationUser" class="flex justify-center items-center space-x-2 mt-4">
                <!-- Pagination will be inserted here by JavaScript -->
            </div>
        </div>

        <br />
        <div>
            <a href="menu_input.php">
                <button type="button" class="bg-teal-700 text-white px-4 py-2 rounded">SEBELUMNYA</button>
            </a>
        </div>
    </div>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/daftar_user.js"></script>
</body>

</html>