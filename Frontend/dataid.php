<?php
include '../Backend/session_check.php';
$dataid = isset($_GET['dataid']) ? intval($_GET['dataid']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="style.css" rel="stylesheet"/>
  <script src="dataid.js" defer></script>
  <title>Informasi Data Pribadi</title>
</head>
<body class="bg p-8 flex items-center justify-center min-h-screen">
  <div class="bg-gray-100 w-full max-w-5xl p-8 rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold mb-4">INFORMASI DATA PRIBADI</h1>
    <div class="bg-white p-6 rounded-b-lg shadow-md mb-6">
      <div class="container">
      <form action="../Backend/proses_input_pengusul.php" method="POST">

          <!-- Kirim dataid juga ke proses_dataid.php -->
          <input type="hidden" name="dataid" value="<?= $dataid ?>">

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="nama">Nama</label></div>
              <div class="col-8">
                <input id="nama" class="w-full mt-1 p-2 border rounded" type="text" name="nama" required />
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="alamat">Alamat</label></div>
              <div class="col-8">
                <input id="alamat" class="w-full mt-1 p-2 border rounded" type="text" name="alamat" required />
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="kode_pos">Kode Pos</label></div>
              <div class="col-8">
                <input id="kode_pos" class="w-full mt-1 p-2 border rounded" type="text" name="kode_pos" required />
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="nomer_telepon">Nomor Telepon</label></div>
              <div class="col-8">
                <input id="nomor_telepon" class="w-full mt-1 p-2 border rounded" type="text" name="nomor_telepon" required />
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="fakultas">Fakultas</label></div>
              <div class="col-8">
                <select id="fakultas" class="w-full mt-1 p-2 border rounded" name="fakultas" required>
                  <option value="">Pilih Fakultas</option>
                  <option value="Fakultas Teknologi Mineral dan Energi">Fakultas Teknologi Mineral dan Energi</option>
                  <option value="Fakultas Ekonomi dan Bisnis">Fakultas Ekonomi dan Bisnis</option>
                  <option value="Fakultas Pertanian">Fakultas Pertanian</option>
                  <option value="Fakultas Teknik Industri">Fakultas Teknik Industri</option>
                  <option value="Fakultas Ilmu Sosial dan Ilmu Politik">Fakultas Ilmu Sosial dan Ilmu Politik</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="role">Status</label></div>
              <div class="col-8">
                <select id="role" class="w-full mt-1 p-2 border rounded" name="role" required>
                  <option value="Mahasiswa">Mahasiswa</option>
                  <option value="Dosen">Dosen</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <div class="row">
              <div class="col-4"><label for="email">Alamat Email</label></div>
              <div class="col-8">
                <input id="email" class="w-full mt-1 p-2 border rounded" type="email" name="email" required />
              </div>
            </div>
          </div>

          <button class="bg-green-800 text-white px-4 py-2 rounded w-full mt-4" type="submit">SIMPAN</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>