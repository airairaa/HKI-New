<?php
include '../Backend/session_check.php';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Selection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/menu_input.js"></script>
</head>
<body class="bg-green-900 flex items-center justify-center min-h-screen">
    <div class="bg-gray-100 p-8 rounded-lg shadow-lg w-80">
        <h2 class="text-center mb-6 text-gray-700">Pilih Jenis Usulan Kekayaan intelektual yang ingin didaftarkan</h2>
        <div class="space-y-4">
            <a href="daftar_user.php">
                <button class="w-full py-2 bg-white text-gray-700 rounded-lg shadow">Hak Cipta</button>
            </a>
            <button class="w-full py-2 bg-white text-gray-700 rounded-lg shadow">Paten</button>
            <button class="w-full py-2 bg-white text-gray-700 rounded-lg shadow">Merek/Logo</button>
            <button class="w-full py-2 bg-white text-gray-700 rounded-lg shadow">Desain Industri</button>
            <button class="w-full py-2 bg-white text-gray-700 rounded-lg shadow">DTLST</button>
        </div>
    <br>
    <div class="flex justify-between mt-4">
        <a href="login_user.php">
            <button class="bg-teal-700 text-white px-4 py-2 rounded">
                SEBELUMNYA
            </button>
        </a>
    </div>
</body>
</html>