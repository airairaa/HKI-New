document.addEventListener("DOMContentLoaded", function () {
    const previewContent = document.getElementById("preview-content");

    // Ambil data dari backend
    fetch("../Backend/get_preview_data.php")
        .then((response) => response.json())
        .then((result) => {
            if (!result.success) {
                previewContent.innerHTML = `<p class="text-gray-600 text-center">Gagal memuat data: ${result.message}</p>`;
                return;
            }

            const { judul, jenis_ciptaan, tanggal, pembayaran, rekening } = result.data;

            // Tambahkan konten preview
            previewContent.innerHTML = `
                <div class="bg-white p-4 rounded-lg shadow flex justify-between items-start">
                  <div class="flex items-start">
                    <img alt="User avatar" class="w-12 h-12 rounded-full mr-4" src="https://storage.googleapis.com/a1aa/image/ceZ5NplVgBw1NisSB2vHj1dQU5gLwGgogtKfOd9i6hu3MMIUA.jpg">
                    <div>
                      <h2 class="text-lg font-bold">Surat Pertanyaan</h2>
                      <p class="text-sm">SP berisi Surat Pernyataan yang berisi judul buku dan tanda tangan</p>
                      <a href="index.php">
                        <button class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Download</button>
                      </a>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm text-gray-500">${tanggal}</p>
                    <i class="fas fa-ellipsis-v text-gray-500"></i>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow flex justify-between items-start">
                  <div class="flex items-start">
                    <img alt="User avatar" class="w-12 h-12 rounded-full mr-4" src="https://storage.googleapis.com/a1aa/image/ceZ5NplVgBw1NisSB2vHj1dQU5gLwGgogtKfOd9i6hu3MMIUA.jpg">
                    <div>
                      <h2 class="text-lg font-bold">Surat Pengalihan Hak</h2>
                      <p class="text-sm">SPH berisi surat pengalihan hak yang berisi seluruh pengusul dan tanda tangan sesuai format yang ada</p>
                      <a href="sph.php">
                        <button class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Download</button>
                      </a>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm text-gray-500">${tanggal}</p>
                    <i class="fas fa-ellipsis-v text-gray-500"></i>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow flex justify-between items-start">
                  <div>
                    <h2 class="text-lg font-bold">Pembayaran: ${pembayaran}</h2>
                    <p class="text-sm">
                      Pembayaran dapat dilakukan dengan mentransfer ke nomor rekening:
                      <br>1. BCA (${rekening.BCA})
                      <br>2. BNI (${rekening.BNI})
                      <br>3. BRI (${rekening.BRI})
                    </p>
                  </div>
                </div>
            `;
        })
        .catch((error) => {
            console.error("Error:", error);
            previewContent.innerHTML = `<p class="text-gray-600 text-center">Terjadi kesalahan saat memuat data.</p>`;
        });
});