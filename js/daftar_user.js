document.addEventListener("DOMContentLoaded", () => {
    loadUserData(1);
  
    // Search functionality
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(() => {
            loadUserData(1);
        }, 500));
    }
  
    // Delete functionality
    document.addEventListener("click", function (e) {
      if (e.target.matches(".btn-hapus")) {
        let id = e.target.dataset.id;
        if (confirm("Yakin ingin hapus data ini?")) {
          fetch("../Backend/hapus_permohonan.php", {
            method: "POST",
            body: new URLSearchParams({ id }),
          })
            .then((res) => res.json())
            .then((result) => {
              if (result.success) {
                alert("Data berhasil dihapus!");
                loadUserData(1);
              } else {
                alert("Gagal menghapus data: " + (result.message || "Unknown error"));
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              alert("Terjadi kesalahan saat menghapus data");
            });
        }
      }
    });
  });
  
  function loadUserData(page) {
    const searchInput = document.getElementById("searchInput");
    const search = searchInput ? searchInput.value : "";
    const tableBody = document.getElementById("permohonanUserTableBody");
    
    // Show loading state
    tableBody.innerHTML = `
      <tr>
        <td colspan="8" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
          <i class="fas fa-spinner fa-spin mr-2"></i>Loading data...
        </td>
      </tr>
    `;
    
    fetch(`../Backend/get_daftar_user.php?page=${page}&search=${encodeURIComponent(search)}`)
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
      })
      .then((result) => {
        console.log('API Response:', result); // Debug log
        
        let data = result.data || [];
        
        if (!data || data.length === 0) {
          tableBody.innerHTML = `
            <tr>
              <td colspan="8" class="border border-gray-300 px-4 py-2 text-center text-gray-500">
                <i class="fas fa-inbox mr-2"></i>Data Kosong
                <br><small class="text-gray-400">Belum ada data permohonan yang diupload</small>
                <br><br>
                <a href="input_awal.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                  <i class="fas fa-plus mr-1"></i>Ajukan Permohonan Baru
                </a>
              </td>
            </tr>
          `;
          
          // Hide pagination if no data
          const pagination = document.getElementById("paginationUser");
          if (pagination) {
            pagination.innerHTML = '';
          }
          return;
        }
        
        tableBody.innerHTML = "";
  
        data.forEach((row) => {
          const status = getStatusBadge(row.status);
          const fileLinks = getFileLinks(row);
          
          tableBody.innerHTML += `
            <tr class="hover:bg-gray-50">
              <td class="border border-gray-300 px-4 py-2">
                <div class="font-medium">${escapeHtml(row.judul || 'N/A')}</div>
                <div class="text-sm text-gray-500">${escapeHtml(row.jenis_ciptaan || 'N/A')}</div>
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                ${fileLinks.ktp}
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                ${fileLinks.karya}
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                ${fileLinks.sp}
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                ${fileLinks.sph}
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                ${fileLinks.bukti}
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                ${status}
              </td>
              <td class="border border-gray-300 px-4 py-2 text-center">
                <div class="flex flex-col space-y-1">
                  <button class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" 
                          onclick="viewDetails(${row.detail_id})">
                    <i class="fas fa-eye mr-1"></i>Detail
                  </button>
                  <button class="bg-red-500 text-white px-2 py-1 rounded text-xs hover:bg-red-600 btn-hapus" 
                          data-id="${row.detail_id}">
                    <i class="fas fa-trash mr-1"></i>Hapus
                  </button>
                </div>
              </td>
            </tr>
          `;
        });
  
                 // Pagination
         if (result.totalPages > 1) {
           updatePagination(result.currentPage, result.totalPages);
         } else {
           const pagination = document.getElementById("paginationUser");
           if (pagination) {
             pagination.innerHTML = '';
           }
         }
      })
      .catch((error) => {
        console.error("Error loading data:", error);
        tableBody.innerHTML = `
          <tr>
            <td colspan="8" class="border border-gray-300 px-4 py-2 text-center text-red-500">
              <i class="fas fa-exclamation-triangle mr-2"></i>Error loading data: ${error.message}
            </td>
          </tr>
        `;
        
        // Hide pagination on error
        const pagination = document.getElementById("paginationUser");
        if (pagination) {
          pagination.innerHTML = '';
        }
      });
  }
  
  function getStatusBadge(status) {
    if (!status) return '<span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">Pending</span>';
    
    switch(status.toLowerCase()) {
      case 'approved':
      case 'disetujui':
        return '<span class="bg-green-200 text-green-700 px-2 py-1 rounded text-xs">Disetujui</span>';
      case 'rejected':
      case 'ditolak':
        return '<span class="bg-red-200 text-red-700 px-2 py-1 rounded text-xs">Ditolak</span>';
      case 'review':
      case 'reviewing':
        return '<span class="bg-yellow-200 text-yellow-700 px-2 py-1 rounded text-xs">Review</span>';
      default:
        return '<span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs">Pending</span>';
    }
  }
  
  function getFileLinks(row) {
    const baseUrl = '../uploads/';
    const createLink = (filename, text) => {
      if (filename) {
        return `<a href="${baseUrl}${filename}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs">
                  <i class="fas fa-file-pdf mr-1"></i>${text}
                </a>`;
      }
      return '<span class="text-gray-400 text-xs">-</span>';
    };
    
    return {
      ktp: createLink(row.file_ktp, 'Lihat'),
      karya: createLink(row.file_contoh_karya, 'Lihat'),
      sp: createLink(row.file_sp, 'Lihat'),
      sph: createLink(row.file_sph, 'Lihat'),
      bukti: createLink(row.file_bukti_pembayaran, 'Lihat')
    };
  }
  
  function updatePagination(currentPage, totalPages) {
    const pagination = document.getElementById("paginationUser");
    if (!pagination || totalPages <= 1) {
      if (pagination) pagination.innerHTML = '';
      return;
    }
    
    let paginationHtml = '';
    
    // Previous button
    if (currentPage > 1) {
      paginationHtml += `<button onclick="loadUserData(${currentPage - 1})" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
        <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
      </button>`;
    }
    
    // Page numbers
    for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
      if (i === currentPage) {
        paginationHtml += `<span class="px-3 py-1 bg-blue-500 text-white rounded">${i}</span>`;
      } else {
        paginationHtml += `<button onclick="loadUserData(${i})" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">${i}</button>`;
      }
    }
    
    // Next button
    if (currentPage < totalPages) {
      paginationHtml += `<button onclick="loadUserData(${currentPage + 1})" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">
        Berikutnya<i class="fas fa-chevron-right ml-1"></i>
      </button>`;
    }
    
    pagination.innerHTML = paginationHtml;
  }
  
  function viewDetails(id) {
    // Implement view details functionality
    alert(`View details for ID: ${id}`);
  }
  
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
  