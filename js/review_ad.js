document.addEventListener("DOMContentLoaded", () => {
    loadData(1);

    document.getElementById("searchInput").addEventListener("input", function () {
        loadData(1, this.value);
    });
});

function loadData(page = 1, searchQuery = '') {
    fetch(`../backend/get_permohonan.php?page=${page}&search=${encodeURIComponent(searchQuery)}`)
        .then((res) => res.json())
        .then((result) => {
            // Pastikan backend mengirim { data: [...], currentPage, totalPages }
            const data = result.data || [];
            const currentPage = result.currentPage || page;
            const totalPages = result.totalPages || 1;

            let table = document.getElementById("permohonanTable");
            table.innerHTML = "";

            data.forEach((row, idx) => {
                const detailId = `detail-row-${idx}`;
                table.innerHTML += `
                  <tr>
                    <td>
                      <button class="btn btn-link p-0 text-start judul-expand" data-detail-id="${detailId}">${row.judul || ''}</button>
                    </td>
                    <td>${row.file_contoh_karya ? `<a href="../uploads/${row.file_contoh_karya}" target="_blank">Lihat</a>` : '-'}</td>
                    <td>${row.file_ktp ? `<a href="../uploads/${row.file_ktp}" target="_blank">Lihat</a>` : '-'}</td>
                    <td>${row.file_sp ? `<a href="../uploads/${row.file_sp}" target="_blank">Lihat</a>` : '-'}</td>
                    <td>${row.file_sph ? `<a href="../uploads/${row.file_sph}" target="_blank">Lihat</a>` : '-'}</td>
                    <td>${row.file_bukti_pembayaran ? `<a href="../uploads/${row.file_bukti_pembayaran}" target="_blank">Lihat</a>` : '-'}</td>
                    <td>
                      <form class="updateForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="${row.review_id || ''}">
                        <select name="status">
                          <option ${row.status == "Diajukan" ? "selected" : ""}>Diajukan</option>
                          <option ${row.status == "Revisi" ? "selected" : ""}>Revisi</option>
                          <option ${row.status == "Terdaftar" ? "selected" : ""}>Terdaftar</option>
                        </select>
                        <input type="file" name="sertifikat">
                        <button type="submit" name="update">Update</button>
                      </form>
                    </td>
                    <td>${row.status || ''}</td>
                    <td>
                      ${row.sertifikat ? `<a href="../uploads/${row.sertifikat}" target="_blank">Lihat</a>` : 'Belum Ada'}
                    </td>
                  </tr>
                  <tr id="${detailId}" class="detail-row" style="display:none; background:#f8f9fa;">
                    <td colspan="9">
                      <div><b>Jenis Permohonan:</b> ${row.jenis_permohonan || '-'}</div>
                      <div><b>Jenis Ciptaan:</b> ${row.jenis_ciptaan || '-'}</div>
                      <div><b>Sub Jenis:</b> ${row.sub_jenis || '-'}</div>
                      <div><b>Judul:</b> ${row.judul || '-'}</div>
                      <div><b>Uraian Singkat:</b> ${row.uraian_singkat || '-'}</div>
                      <div><b>Tanggal:</b> ${row.tanggal || '-'}</div>
                      <div><b>Negara:</b> ${row.negara || '-'}</div>
                      <div><b>Jenis Pendanaan:</b> ${row.jenis_pendanaan || '-'}</div>
                      <div><b>Nama Pendanaan:</b> ${row.nama_pendanaan || '-'}</div>
                    </td>
                  </tr>
                `;
            });

            // Setelah render, pasang event listener untuk ekspansi
            setTimeout(() => {
              document.querySelectorAll('.judul-expand').forEach(btn => {
                btn.addEventListener('click', function() {
                  const detailRow = document.getElementById(this.getAttribute('data-detail-id'));
                  if (detailRow.style.display === 'none') {
                    detailRow.style.display = '';
                  } else {
                    detailRow.style.display = 'none';
                  }
                });
              });
            }, 0);

            renderPagination(currentPage, totalPages, searchQuery);
        });
}

// Bootstrap-style pagination with border
function renderPagination(currentPage, totalPages, searchQuery) {
    const pagination = document.getElementById("pagination");
    let html = `<ul class="pagination justify-content-center">`;

    // Previous
    html += `
      <li class="page-item${currentPage === 1 ? ' disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage - 1}">Sebelumnya</a>
      </li>
    `;

    // Page numbers (max 5)
    let start = Math.max(1, currentPage - 2);
    let end = Math.min(totalPages, currentPage + 2);
    if (currentPage <= 3) end = Math.min(5, totalPages);
    if (currentPage > totalPages - 2) start = Math.max(1, totalPages - 4);

    for (let i = start; i <= end; i++) {
        html += `
          <li class="page-item${i === currentPage ? ' active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
          </li>
        `;
    }

    // Next
    html += `
      <li class="page-item${currentPage === totalPages ? ' disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage + 1}">Berikutnya</a>
      </li>
    `;

    html += `</ul>`;
    pagination.innerHTML = html;

    // Event listener
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const page = parseInt(this.getAttribute('data-page'));
            if (!isNaN(page) && page >= 1 && page <= totalPages && page !== currentPage) {
                loadData(page, searchQuery);
            }
        });
    });
}