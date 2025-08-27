console.debug("[review_ad] script loaded");

document.addEventListener("DOMContentLoaded", () => {
  console.debug("[review_ad] DOMContentLoaded");
  loadData(1);

  const searchEl = document.getElementById("searchInput");
  if (searchEl) {
    searchEl.addEventListener("input", function () {
      loadData(1, this.value);
    });
  } else {
    console.warn("[review_ad] #searchInput not found");
  }
});

function loadData(page = 1, searchQuery = "") {
  const url = `../Backend/get_all_data.php?page=${page}&search=${encodeURIComponent(
    searchQuery
  )}`;
  console.debug("[review_ad] fetching", url);

  fetch(url, { credentials: "same-origin" })
    .then((res) => {
      if (!res.ok) throw new Error("HTTP " + res.status);
      return res.json();
    })
    .then((result) => {
      let data = result.data || [];
      const currentPage = result.currentPage || result.current_page || page;
      const totalPages = result.totalPages || result.total_pages || 1;

      data.sort((a, b) => {
        const getScore = (row) => {
          const dateFields = [
            "created_at",
            "createdAt",
            "uploaded_at",
            "uploadedAt",
            "tanggal_pertama_kali_diumumumkan",
            "tanggal",
            "date",
          ];
          for (const f of dateFields) {
            if (row[f]) {
              const d = Date.parse(row[f]);
              if (!isNaN(d)) return d;
            }
          }
          const idFields = ["id", "review_id", "dataid", "upload_id"];
          for (const f of idFields) {
            if (row[f]) {
              const n = parseInt(String(row[f]).replace(/\D/g, ""), 10);
              if (!isNaN(n)) return n;
            }
          }
          return 0;
        };
        return getScore(b) - getScore(a);
      });

      let table = document.getElementById("permohonanTable");
      if (!table) {
        console.error("[review_ad] element #permohonanTable not found");
        return;
      }
      table.innerHTML = "";

      data.forEach((row, idx) => {
        const detailId = `detail-row-${idx}`;
        const jenisPermohonan =
          row.jenis_permohonan || row.jenisPermohonan || "";
        const jenisCiptaan = row.jenis_ciptaan || row.jenisCiptaan || "";
        const subJenis =
          row.sub_jenis_ciptaan || row.sub_jenis || row.subJenis || "";
        const judul = row.judul || "";
        const uraian = row.uraian_singkat || row.uraian || "";
        const tanggal =
          row.tanggal_pertama_kali_diumumumkan || row.tanggal || "";
        const negara = row.negara_pertama_kali_diumumumkan || row.negara || "";
        const jenisPendanaan = row.jenis_pendanaan || "";
        const jenisHibah = row.jenis_hibah || row.nama_pendanaan || "";
        const sertifikat = row.sertifikat || "";
        const fileContoh = row.file_contoh_karya || "";
        const fileKTP = row.file_ktp || "";
        const fileSP = row.file_sp || "";
        const fileSPH = row.file_sph || "";
        const fileBukti = row.file_bukti_pembayaran || "";

        // build file link helper
        const fileLink = (f) =>
          f
            ? `<a href="../uploads/${encodeURIComponent(
                f
              )}" target="_blank" rel="noopener">Lihat</a>`
            : "-";

        // note: ensure column order matches table head in review_ad.php
        table.innerHTML += `
                  <tr>
                    <td>
                      <button class="btn btn-link p-0 text-start judul-expand" data-detail-id="${detailId}">${escapeHtml(
          judul
        )}</button>
                    </td>
+                   <td>${escapeHtml(tanggal || "-")}</td>
                    <td>${fileLink(fileContoh)}</td>
                    <td>${fileLink(fileKTP)}</td>
                    <td>${fileLink(fileSP)}</td>
                    <td>${fileLink(fileSPH)}</td>
                    <td>${fileLink(fileBukti)}</td>
                    <td>
                      <form class="updateForm" method="POST" enctype="multipart/form-data" data-review-id="${
                        row.review_id || row.id || ""
                      }">
                        <input type="hidden" name="id" value="${escapeHtml(
                          row.review_id || row.id || ""
                        )}">
                        <select name="status">
                          <option value="Diajukan" ${
                            row.status == "Diajukan" ? "selected" : ""
                          }>Diajukan</option>
                          <option value="Revisi" ${
                            row.status == "Revisi" ? "selected" : ""
                          }>Revisi</option>
                          <option value="Terdaftar" ${
                            row.status == "Terdaftar" ? "selected" : ""
                          }>Terdaftar</option>
                        </select>
                        <input type="file" name="sertifikat" accept="application/pdf" />
                        <button type="submit" name="update" class="btn-update">Update</button>
                      </form>
                    </td>
                    <td class="status-cell">${escapeHtml(row.status || "")}</td>
                    <td class="sertifikat-cell">
                      ${
                        sertifikat
                          ? `<a href="../uploads/${encodeURIComponent(
                              sertifikat
                            )}" target="_blank" rel="noopener">Lihat</a>`
                          : "Belum Ada"
                      }
                    </td>
                  </tr>
                  </tr>
                  <tr id="${detailId}" class="detail-row" style="display:none; background:#f8f9fa;">
                    <td colspan="9">
                      <div><b>Jenis Permohonan:</b> ${escapeHtml(
                        jenisPermohonan
                      )}</div>
                      <div><b>Jenis Ciptaan:</b> ${escapeHtml(
                        jenisCiptaan
                      )}</div>
                      <div><b>Sub Jenis:</b> ${escapeHtml(subJenis)}</div>
                      <div><b>Judul:</b> ${escapeHtml(judul)}</div>
                      <div><b>Uraian Singkat:</b> ${escapeHtml(uraian)}</div>
                      <div><b>Tanggal:</b> ${escapeHtml(tanggal)}</div>
                      <div><b>Negara:</b> ${escapeHtml(negara)}</div>
                      <div><b>Jenis Pendanaan:</b> ${escapeHtml(
                        jenisPendanaan
                      )}</div>
                      <div><b>Jenis Hibah / Nama Pendanaan:</b> ${escapeHtml(
                        jenisHibah
                      )}</div>
                    </td>
                  </tr>
                `;
      });

      // Setelah render, pasang event listener untuk ekspansi
      setTimeout(() => {
        document.querySelectorAll(".judul-expand").forEach((btn) => {
          btn.addEventListener("click", function () {
            const detailRow = document.getElementById(
              this.getAttribute("data-detail-id")
            );
            if (!detailRow) return;
            detailRow.style.display =
              detailRow.style.display === "none" ? "" : "none";
          });
        });

        // attach submit handler for each update form
        document.querySelectorAll(".updateForm").forEach((form) => {
          form.addEventListener("submit", function (e) {
            e.preventDefault();
            handleUpdateForm(this);
          });
        });
      }, 0);

      renderPagination(currentPage, totalPages, searchQuery);
    })
    .catch((err) => {
      console.error("Fetch error:", err);
      const table = document.getElementById("permohonanTable");
      if (table)
        table.innerHTML = `<tr><td colspan="9" class="text-danger">Error: ${escapeHtml(
          err.message || String(err)
        )}</td></tr>`;
    });
}

// helper escape
function escapeHtml(str) {
  if (typeof str !== "string") return "";
  return str.replace(/[&<>"']/g, function (m) {
    return {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
    }[m];
  });
}

function handleUpdateForm(form) {
  const submitBtn = form.querySelector(".btn-update");
  // status cell is in the same row, not the next sibling
  const statusCell = form.closest("tr")
    ? form.closest("tr").querySelector(".status-cell")
    : null;
  const sertifikatCell = form.closest("tr")
    ? form.closest("tr").querySelector(".sertifikat-cell")
    : null;

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = "Mengirim...";
  }

  const fd = new FormData(form);
  const id =
    form.querySelector('input[name="id"]').value || form.dataset.reviewId;
  fd.set("id", id);

  const updateUrl = "../Backend/update_review.php"; // ensure correct folder name
  console.debug("[review_ad] submitting update to", updateUrl);

  fetch(updateUrl, {
    method: "POST",
    body: fd,
    credentials: "same-origin",
  })
    .then((r) => {
      if (!r.ok) throw new Error("HTTP " + r.status);
      return r.json();
    })
    .then((json) => {
      if (json.success) {
        if (statusCell)
          statusCell.textContent = json.status || fd.get("status") || "—";
        if (json.fileName && sertifikatCell) {
          sertifikatCell.innerHTML = `<a href="../uploads/${encodeURIComponent(
            json.fileName
          )}" target="_blank" rel="noopener">Lihat</a>`;
        }
        alert(json.message || "Berhasil diupdate");
      } else {
        alert(json.message || "Gagal update");
      }
    })
    .catch((err) => {
      console.error(err);
      alert("Terjadi kesalahan saat mengirim data");
    })
    .finally(() => {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = "Update";
      }
    });
}

// Bootstrap-style pagination with border
function renderPagination(currentPage, totalPages, searchQuery) {
  const pagination = document.getElementById("pagination");
  if (!pagination) return;
  let html = `<ul class="pagination justify-content-center">`;

  // Previous
  html += `
      <li class="page-item${currentPage === 1 ? " disabled" : ""}">
        <a class="page-link" href="#" data-page="${
          currentPage - 1
        }">Sebelumnya</a>
      </li>
    `;

  // Page numbers (max 5)
  let start = Math.max(1, currentPage - 2);
  let end = Math.min(totalPages, currentPage + 2);
  if (currentPage <= 3) end = Math.min(5, totalPages);
  if (currentPage > totalPages - 2) start = Math.max(1, totalPages - 4);

  for (let i = start; i <= end; i++) {
    html += `
          <li class="page-item${i === currentPage ? " active" : ""}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
          </li>
        `;
  }

  // Next
  html += `
      <li class="page-item${currentPage === totalPages ? " disabled" : ""}">
        <a class="page-link" href="#" data-page="${
          currentPage + 1
        }">Berikutnya</a>
      </li>
    `;

  html += `</ul>`;
  pagination.innerHTML = html;

  // Event listener
  pagination.querySelectorAll(".page-link").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const page = parseInt(this.getAttribute("data-page"));
      if (
        !isNaN(page) &&
        page >= 1 &&
        page <= totalPages &&
        page !== currentPage
      ) {
        loadData(page, searchQuery);
      }
    });
  });
}
