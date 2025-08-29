let CURRENT_PAGE = 1;

document.addEventListener("DOMContentLoaded", () => {
  loadUserData(1);
  const searchInput = document.getElementById("searchInput");
  if (searchInput) searchInput.addEventListener("input", debounce(()=>loadUserData(1), 400));
});

function loadUserData(page = 1) {
  CURRENT_PAGE = page;
  const search = (document.getElementById("searchInput") || {}).value || "";
  const tableBody = document.getElementById("permohonanUserTableBody");
  if (!tableBody) return;

  tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-4">Loading...</td></tr>`;

  fetch(`../Backend/get_daftar_user.php?page=${page}&search=${encodeURIComponent(search)}`, { credentials: 'same-origin' })
    .then(r => {
      if (!r.ok) throw new Error('HTTP error! status: ' + r.status);
      return r.json();
    })
    .then(res => {
      if (!res.success) {
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-red-600 p-4">Error: ${escapeHtml(res.message || 'Server error')}</td></tr>`;
        return;
      }
      const data = res.data || [];
      if (!data.length) {
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center p-6">Tidak ada data</td></tr>`;
        const p = document.getElementById("paginationUser"); if (p) p.innerHTML = ""; 
        return;
      }

      tableBody.innerHTML = "";
      data.forEach(row => {
        const files = row.files || {};

        // normalize identifiers (try multiple possible column names)
        const idRaw = (row.id ?? row.detail_id ?? row.detailId);
        const dataidRaw = (row.dataid ?? row.dataId ?? row.data_id);
        const uploadIdRaw = (row.upload_id ?? row.uploadId ?? row.uploadId);

        const idVal = (idRaw === null || idRaw === undefined || idRaw === '') ? null : Number(idRaw);
        const dataidVal = (dataidRaw === null || dataidRaw === undefined || dataidRaw === '') ? null : Number(dataidRaw);
        const uploadIdVal = (uploadIdRaw === null || uploadIdRaw === undefined || uploadIdRaw === '') ? null : Number(uploadIdRaw);

        const makeBtn = (f, type) => {
          if (!f || !f.exists) return '<span class="text-gray-400 text-xs">-</span>';
          if (f.upload_id) {
            return `<button onclick="openFileByUploadId(${encodeURIComponent(f.upload_id)}, '${escapeJs(type)}')" class="inline-flex items-center text-blue-600 text-sm">
                      <i class="fas fa-file-pdf mr-1"></i>Lihat
                    </button>`;
          }
          if (f.file && f.file !== '') {
            const fname = encodeURIComponent(f.file);
            return `<button onclick="openFileByFilename('${fname}', '${escapeJs(type)}')" class="inline-flex items-center text-blue-600 text-sm">
                      <i class="fas fa-file-pdf mr-1"></i>Lihat
                    </button>`;
          }
          return '<span class="text-gray-400 text-xs">-</span>';
        };

        const contohKarya = files.contoh_karya || files.karya || files.contoh || null;

        tableBody.innerHTML += `
          <tr class="hover:bg-gray-50">
            <td class="p-3 align-top">
              <div class="font-medium">${escapeHtml(row.judul || '')}</div>
              <div class="text-sm text-gray-500">${escapeHtml(row.jenis_ciptaan || '')}</div>
            </td>
            <td class="p-3 text-center align-top">${makeBtn(files.ktp, 'ktp')}</td>
            <td class="p-3 text-center align-top">${makeBtn(contohKarya, 'karya')}</td>
            <td class="p-3 text-center align-top">${makeBtn(files.sp, 'sp')}</td>
            <td class="p-3 text-center align-top">${makeBtn(files.sph, 'sph')}</td>
            <td class="p-3 text-center align-top">${makeBtn(files.bukti, 'bukti')}</td>
            <td class="p-3 text-center align-top">${escapeHtml(row.status || 'Pending')}</td>
            <td class="p-3 text-center align-top">
              <div class="flex flex-col gap-2 items-center">
                <button onclick="location.href='input_awal.php?id=${encodeURIComponent(idVal ?? '')}&dataid=${encodeURIComponent(dataidVal ?? '')}&upload_id=${encodeURIComponent(uploadIdVal ?? '')}&mode=edit'" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">Edit</button>
                <button onclick="confirmDelete(${JSON.stringify(idVal)}, ${JSON.stringify(dataidVal)}, ${JSON.stringify(uploadIdVal)})" class="bg-red-500 text-white px-3 py-1 rounded text-xs">Hapus</button>
              </div>
            </td>
          </tr>
        `;
      });

      const cur = res.current_page || res.currentPage || 1;
      const total = res.total_pages || res.totalPages || 1;
      updatePagination(cur, total);
    })
    .catch(err => {
      console.error(err);
      tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-red-600 p-4">Error loading data: ${escapeHtml(err.message)}</td></tr>`;
    });
}

// ...rest of file unchanged...
function openFileByUploadId(uploadId, type) {
  const url = `../Backend/view_file.php?upload_id=${encodeURIComponent(uploadId)}&type=${encodeURIComponent(type)}`;
  window.open(url, '_blank');
}

function openFileByFilename(filenameEncoded, type) {
  const url = `../Backend/view_file.php?filename=${filenameEncoded}&type=${encodeURIComponent(type)}`;
  window.open(url, '_blank');
}

// accept id, dataid, upload_id
function confirmDelete(id, dataid, uploadId) {
  if ((id === null || id === undefined) && (dataid === null || dataid === undefined) && (uploadId === null || uploadId === undefined)) {
    alert('ID tidak tersedia untuk dihapus'); return;
  }
  if (!confirm('Yakin ingin menghapus data ini? Tindakan ini akan menghapus record dan file terkait.')) return;
  deletePermohonan(id, dataid, uploadId);
}

function deletePermohonan(id, dataid, uploadId) {
    // ensure we have at least one identifier
    const idNum = Number(id) || 0;
    const dataidNum = Number(dataid) || 0;
    const uploadNum = Number(uploadId) || 0;
    if (!idNum && !dataidNum && !uploadNum) {
        Swal.fire('Gagal', 'Tidak ada identifier valid untuk dihapus.', 'error');
        return;
    }

    const fd = new FormData();
    if (idNum) fd.append('id', idNum);
    if (dataidNum && dataidNum < 1000000000000) fd.append('dataid', dataidNum);
    if (uploadNum) fd.append('upload_id', uploadNum);

    fetch('../Backend/hapus_permohonan.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            loadUserData(CURRENT_PAGE);
            Swal.fire('Berhasil', res.message || 'Terhapus', 'success');
        } else {
            console.error('delete error', res);
            // show diagnostic info if available
            const diag = res.diagnostics ? `<pre style="text-align:left">${escapeHtml(JSON.stringify(res.diagnostics, null, 2))}</pre>` : '';
            Swal.fire({
                title: 'Gagal menghapus',
                html: `${escapeHtml(res.message || 'Server error')}<br/>${diag}`,
                icon: 'error',
                width: 700
            });
        }
    })
    .catch(err => {
        console.error('delete error', err);
        Swal.fire('Gagal', 'Koneksi gagal', 'error');
    });
}

function updatePagination(currentPage, totalPages) {
  const p = document.getElementById("paginationUser");
  if (!p) return;
  if (totalPages <= 1) { p.innerHTML = ""; return; }
  let html = '';
  if (currentPage > 1) html += `<button onclick="loadUserData(${currentPage-1})" class="px-2 py-1 border rounded mr-1">Prev</button>`;
  for (let i = Math.max(1, currentPage-2); i <= Math.min(totalPages, currentPage+2); i++) {
    html += i === currentPage ? `<span class="px-3 py-1 bg-blue-500 text-white rounded">${i}</span>` : `<button onclick="loadUserData(${i})" class="px-3 py-1 border rounded mr-1">${i}</button>`;
  }
  if (currentPage < totalPages) html += `<button onclick="loadUserData(${currentPage+1})" class="px-2 py-1 border rounded">Next</button>`;
  p.innerHTML = html;
}

function escapeHtml(s){ if (s==null) return ''; const d=document.createElement('div'); d.textContent = s; return d.innerHTML; }
function escapeJs(s){ if (s==null) return ''; return String(s).replace(/'/g,"\\'").replace(/\n/g,''); }

function debounce(fn, t){ let to; return (...a)=>{ clearTimeout(to); to=setTimeout(()=>fn(...a), t); }; }