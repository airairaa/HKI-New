// Sub-Jenis Ciptaan Dropdown Dinamis
(function () {
    if (window.__inputawal_loaded) return;
    window.__inputawal_loaded = true;

    // Sub-Jenis Ciptaan Dropdown Dinamis
    const subjenisOptions = {
        "Karya Tulis": [
            "Atlasbiografi","Booklet","Buku","Buku mewarnai","Buku panduan/petunjuk",
            "Buku pelajaran","Buku saku","Bunga rampai","Cerita bergambar","Diktat",
            "Dongeng","E-book","Ensiklopedia","Jurnal","Kamus","Karya ilmiah",
            "Karya tulis","Karya tulis (artikel)","Karya tulis (disertasi)","Karya tulis (skripsi)",
            "Karya tulis (tesis)","Karya tulis lainnya","Komik","Laporan penelitian",
            "Majalah","Makalah","Modul","Naskah drama/pertunjukan"
        ],
        "Karya Seni": [
            "Alat Peraga","Arsitektur","Baliho","Banner","Brosur","Diorama","Flayer","Kaligrafi",
            "Karya seni batik","Karya seni rupa","Kolase","Leaflet","Motif sasirangan","Motif tapis",
            "Motif Tenun Ikat","Motif Ulos","Pamflet","Peta","Poster","Seni Gambar","Seni Ilustrasi",
            "Seni Lukis","Seni Motif","Seni Motif Lainnya","Seni Pahat","Seni Patung","Seni Rupa",
            "Seni Songket","Seni Terapan","Seni Umum","Sketsa","Spanduk","Ukiran"
        ],
        "Komposisi Musik": ["Aransemen","Lagu (Musik Dengan Teks)","Musik","Musik Blues","Musik Country","Musik Dangdut","Musik Elektronik","Musik Funk","Musik Gospel","Musik Hip Hop, Rap, Rapcore","Musik Jazz","Musik Karawitan"],
        "Karya Audio Visual": ["Film","Film Cerita","Film Dokumenter","Film Iklan","Film Kartun","Karya Rekaman Video","Karya Siaran","Karya Siaran Media Televisi dan Film","Karya Siaran Video","Karya Siaran Media Radio","Karya Sinematografi","Kuliah","Reportase"],
        "Karya Fotografi": ["Karya Fotografi","Potret"],
        "Karya Drama & Koreografi": ["Drama / Pertunjukan","Drama Musikal","Ketoprak","Komedi/Lawak","Koreografi","Lenong","Ludruk","Opera","Pantomim","Pentas Musik","Pewayangan","Seni Akrobat","Seni Pertunjukan","Sirkus","Sulap","Tari (Sendra Tari)"],
        "Karya Rekaman": ["Ceramah","Karya Rekaman Suara atau Bunyi","Khutbah","Pidato"],
        "Karya Lainnya": ["Basis Data","Kompilasi Ciptaan / Data","Permainan Video","Program Komputer"]
    };

    function populateSubJenis(selectedJenis, prevSelected = '') {
        const subSelect = document.getElementById('sub_jenis_ciptaan');
        if (!subSelect) return;
        subSelect.innerHTML = '<option value="">Pilih Sub-Jenis Ciptaan</option>';
        const list = subjenisOptions[selectedJenis] || [];
        list.forEach(subjenis => {
            const option = document.createElement('option');
            option.value = subjenis;
            option.textContent = subjenis;
            if (prevSelected && prevSelected === subjenis) option.selected = true;
            subSelect.appendChild(option);
        });
    }

    // ensure pendanaan display element exists and update it
    function ensurePendanaanDisplay(hibahSelect) {
        if (!hibahSelect) return null;
        let display = document.getElementById('pendanaanValue');
        if (!display) {
            display = document.createElement('div');
            display.id = 'pendanaanValue';
            display.className = 'mt-2 text-sm text-gray-700';
            // Insert after hibahSelect
            hibahSelect.parentNode.insertBefore(display, hibahSelect.nextSibling);
        }
        return display;
    }

    function updatePendanaanDisplay(hibahSelect) {
        const display = ensurePendanaanDisplay(hibahSelect);
        const pendanaanHidden = document.getElementById('pendanaanHidden');
        if (!display) return;
        const selectedOption = hibahSelect.selectedOptions ? hibahSelect.selectedOptions[0] : null;
        const fundingAmount = selectedOption ? (selectedOption.getAttribute('data-funding') || '') : '';
        if (fundingAmount) {
            display.textContent = 'Jumlah pendanaan: ' + fundingAmount;
            display.style.display = 'block';
        } else {
            display.textContent = '';
            display.style.display = 'none';
        }
        if (pendanaanHidden) pendanaanHidden.value = fundingAmount || '';
    }

    document.addEventListener("DOMContentLoaded", function () {
        const jenisCiptaanDropdown = document.getElementById('jenis_ciptaan');
        const subJenisDropdown = document.getElementById('sub_jenis_ciptaan');

        if (!jenisCiptaanDropdown || !subJenisDropdown) {
            console.error("Pastikan elemen dengan ID 'jenis_ciptaan' dan 'sub_jenis_ciptaan' ada di dalam HTML.");
        } else {
            // If server rendered a previously selected sub-jenis, store it (optional)
            const prevSelectedSub = subJenisDropdown.getAttribute('data-selected') || '';
            // populate immediately based on current jenis_ciptaan value
            populateSubJenis(jenisCiptaanDropdown.value, prevSelectedSub);
            // attach change listener to update options when jenis_ciptaan changes
            jenisCiptaanDropdown.addEventListener('change', function () {
                populateSubJenis(this.value);
            });
        }

        // Initialize hibah/pendanaan controls and display
        const jenisPendanaanEl = document.getElementById('jenis_pendanaan');
        const hibahSelect = document.getElementById('hibah');
        // populate hibah based on current jenis_pendanaan if present
        if (typeof window.showHibahOptions === 'function') {
            window.showHibahOptions();
        }
        // ensure pendanaan display exists and is updated according to current selection
        if (hibahSelect) {
            // create display element if missing and update initial value
            updatePendanaanDisplay(hibahSelect);
            // ensure onchange handler updates display
            hibahSelect.addEventListener('change', function () {
                updatePendanaanDisplay(this);
            });
        }

        // also update display when jenis_pendanaan changes (because options change)
        if (jenisPendanaanEl) {
            jenisPendanaanEl.addEventListener('change', function () {
                // after options are rebuilt inside showHibahOptions, pick the new select and update
                const newHibah = document.getElementById('hibah');
                updatePendanaanDisplay(newHibah);
            });
        }
    });

    // Functions for hibah/pendanaan (kept but ensure data-funding attributes are set)
    window.showHibahOptions = function () {
        const jenisHibahEl = document.getElementById("jenis_pendanaan") || document.getElementById("jenisHibah");
        const jenisHibah = jenisHibahEl ? jenisHibahEl.value : '';
        const hibahSelect = document.getElementById("hibah");
        const hibahLabel = document.getElementById("hibahLabel");
        const pendanaanLabel = document.getElementById("pendanaanLabel");
        const pendanaanValue = document.getElementById("pendanaanValue");
        const pendanaanHidden = document.getElementById("pendanaanHidden");

        if (!hibahSelect) return;

        hibahSelect.innerHTML = '<option value="">Pilih Hibah</option>';
        if (hibahLabel) hibahLabel.classList.add("hidden");
        hibahSelect.classList.add("hidden");
        if (pendanaanLabel) pendanaanLabel.classList.add("hidden");
        if (pendanaanValue) pendanaanValue.classList.add("hidden");
        if (pendanaanHidden) pendanaanHidden.value = "";

        if (jenisHibah === "internal") {
            if (hibahLabel) hibahLabel.classList.remove("hidden");
            hibahSelect.classList.remove("hidden");
            hibahSelect.innerHTML += `
                <option value="hibah penelitian dosen pemula" data-funding="Rp50.000.000">Hibah Penelitian Dosen Pemula</option>
                <option value="hibah penelitian dasar" data-funding="Rp100.000.000">Hibah Penelitian Dasar</option>
                <option value="hibah penelitian terapan" data-funding="Rp150.000.000">Hibah Penelitian Terapan</option>
                <option value="hibah penelitian pengembangan" data-funding="Rp200.000.000">Hibah Penelitian Pengembangan</option>
                <option value="hibah penelitian kelembagaan" data-funding="Rp250.000.000">Hibah Penelitian Kelembagaan</option>
            `;
        } else if (jenisHibah === "eksternal") {
            if (hibahLabel) hibahLabel.classList.remove("hidden");
            hibahSelect.classList.remove("hidden");
            hibahSelect.innerHTML += `
                <option value="hibah drtpm" data-funding="Rp300.000.000">Hibah DRTPM</option>
                <option value="riim" data-funding="Rp400.000.000">RIIM</option>
                <option value="kedaireka" data-funding="Rp500.000.000">Kedaireka</option>
                <option value="riset inovatif produktif" data-funding="Rp600.000.000">Riset Inovatif Produktif (RISPRO)</option>
                <option value="grant riset sawit bpdpks" data-funding="Rp700.000.000">Grant Riset Sawit BPDPKS</option>
                <option value="lainnya" data-funding="-">Lainnya</option>
            `;
        }

        // attach onchange to update pendanaan display and hidden value
        hibahSelect.onchange = function () {
            const selectedOption = hibahSelect.selectedOptions[0];
            const fundingAmount = selectedOption ? selectedOption.getAttribute("data-funding") || "" : "";
            if (pendanaanLabel) pendanaanLabel.classList.remove("hidden");
            if (pendanaanHidden) pendanaanHidden.value = fundingAmount;
            // update or create display element
            updatePendanaanDisplay(hibahSelect);
        };

        // if there's an already selected option, ensure display is updated
        updatePendanaanDisplay(hibahSelect);
    };
})();

function showPendanaan() {
  const selectedOption = document.getElementById("hibah").selectedOptions[0];
  const pendanaanLabel = document.getElementById("pendanaanLabel");
  const pendanaanValue = document.getElementById("pendanaanValue");
  const pendanaanHidden = document.getElementById("pendanaanHidden");

  if (selectedOption.value) {
    const fundingAmount = selectedOption.getAttribute("data-funding");
    pendanaanLabel.classList.remove("hidden");
    pendanaanValue.classList.remove("hidden");
    pendanaanValue.textContent = fundingAmount;
    pendanaanHidden.value = fundingAmount;
  } else {
    pendanaanLabel.classList.add("hidden");
    pendanaanValue.classList.add("hidden");
    pendanaanValue.textContent = "";
    pendanaanHidden.value = "";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const tbody = document.getElementById("penciptaTableBody");

  fetch("../Backend/proses_pencipta.php")
    .then((response) => response.json())
    .then((data) => {
      tbody.innerHTML = "";
      if (data.error) {
        tbody.innerHTML = `<tr><td colspan="12" class="border p-4 text-center text-red-600">${data.error}</td></tr>`;
        return;
      }
      if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="12" class="border p-4 text-center">Tidak ada data pencipta.</td></tr>`;
        return;
      }
      data.forEach((row, index) => {
        const tr = document.createElement("tr");
        tr.classList.add("border");
        tr.innerHTML = `
                    <td class="border p-2 text-center">${index + 1}</td>
                    <td class="border p-2">${row.nama}</td>
                    <td class="border p-2">${row.email}</td>
                    <td class="border p-2 text-center">${row.no_telp}</td>
                    <td class="border p-2 text-center">${
                      row.kewarganegaraan
                    }</td>
                    <td class="border p-2">${row.alamat}</td>
                    <td class="border p-2 text-center">${row.negara}</td>
                    <td class="border p-2 text-center">${row.provinsi}</td>
                    <td class="border p-2 text-center">${
                      row.kabupaten_kota
                    }</td>
                    <td class="border p-2 text-center">${row.kecamatan}</td>
                    <td class="border p-2 text-center">${row.kode_pos}</td>
                    <td class="border p-2 text-center">${
                      row.pemegang_hakcipta
                    }</td>
                `;
        tbody.appendChild(tr);
      });
    })
    .catch((error) => {
      tbody.innerHTML = `<tr><td colspan="12" class="border p-4 text-center text-red-600">Gagal memuat data.</td></tr>`;
      console.error("Error:", error);
    });
});
