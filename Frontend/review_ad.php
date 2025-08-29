<?php
include '../Backend/session_check.php';
// Check if user is admin
checkRole('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Permohonan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/review_ad.js" defer></script>
</head>
<body>
<body>


<div class="container pt-5 mt-4">
    <h3 class="text-center fw-bold mb-4">📑 Daftar Permohonan</h3>

    <div class="row mb-3">
        <div class="col-md-6 mx-auto">
            <input id="searchInput" class="form-control shadow-sm" placeholder="🔍 Cari judul permohonan..." type="text"/>
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead class="table-primary text-center">
                <tr>
                    <th>Judul</th>
                    <th>Contoh Karya</th>
                    <th>KTP</th>
                    <th>SP</th>
                    <th>SPH</th>
                    <th>Bukti Pembayaran</th>
                    <th>Proses</th>
                    <th>Status</th>
                    <th>Sertifikat</th>
                </tr>
            </thead>
            <tbody id="permohonanTable">
                <!-- Data dimuat via JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center mt-4" id="pagination"></ul>
    </nav>

    <div class="text-center mt-4">
        <a href="login.php" class="btn btn-secondary px-4">⬅ Sebelumnya</a>
    </div>
</div>
</body>
</html>