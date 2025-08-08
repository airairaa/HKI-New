document.addEventListener("DOMContentLoaded", function () {
    const generatePdfButton = document.getElementById("generate-pdf");

    generatePdfButton.addEventListener("click", async function () {
        try {
            // Ambil data dari backend
            const response = await fetch("get_surat_data.php");
            const result = await response.json();

            if (!result.success) {
                alert("Gagal mengambil data: " + result.message);
                return;
            }

            const { jenis_ciptaan, judul, jenis_pendanaan } = result.data;

            // Generate PDF menggunakan jsPDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();

            pdf.setFont("Times", "bold");
            pdf.setFontSize(14);
            pdf.text("SURAT PERNYATAAN", 105, 20, { align: "center" });

            pdf.setFont("Times", "normal");
            pdf.setFontSize(12);
            pdf.text("Yang bertanda tangan di bawah ini:", 20, 30);

            const dataPemohon = [
                { key: "Nama", value: 'Universitas Pembangunan Nasional "Veteran" Yogyakarta' },
                { key: "Kewarganegaraan", value: "Indonesia" },
                {
                    key: "Alamat",
                    value:
                        'Lembaga Penelitian dan Pengabdian kepada Masyarakat, Gedung Rektorat\nLantai 4, Kampus 1 Universitas Pembangunan Nasional "Veteran" Yogyakarta, Jl. Padjajaran No.104, Condongcatur, Depok, Sleman 55283'
                }
            ];

            let y = 40;
            dataPemohon.forEach((item) => {
                pdf.text(`${item.key}:`, 20, y);
                pdf.text(item.value, 60, y, { maxWidth: 130 });
                y += 10;
            });

            pdf.text("Dengan ini menyatakan bahwa:", 20, y);
            y += 10;

            pdf.text("1. Karya Cipta yang kami mohonkan:", 20, y);
            y += 10;
            pdf.text(`Berupa: ${jenis_ciptaan}`, 30, y);
            y += 10;
            pdf.text(`Berjudul: ${judul}`, 30, y);
            y += 10;

            pdf.text("2. Sebagai pemohon mempunyai kewajiban untuk menyimpan asli contoh ciptaan.", 20, y);
            y += 10;

            pdf.text("3. Karya Cipta yang saya mohonkan tidak pernah dan tidak sedang dalam sengketa.", 20, y);
            y += 10;

            pdf.text("4. Dalam hal ketentuan dilanggar, maka:", 20, y);
            y += 10;
            const poin = [
                "permohonan hak cipta dianggap ditarik kembali;",
                "Karya Cipta yang telah terdaftar dihapuskan sesuai ketentuan perundang-undangan.",
                "Status kepemilikan surat pencatatan elektronik ditangguhkan menunggu putusan Pengadilan."
            ];
            poin.forEach((item, index) => {
                pdf.text(`${String.fromCharCode(97 + index)}. ${item}`, 30, y);
                y += 10;
            });

            pdf.text("5. Karya Cipta didapatkan dengan dukungan UPN \"Veteran\" Yogyakarta.", 20, y);
            y += 10;
            pdf.text(`Pendanaan: ${jenis_pendanaan}`, 30, y);
            y += 10;

            pdf.text("Demikian Surat Pernyataan ini dibuat dengan sebenarnya.", 20, y);
            y += 20;

            pdf.text("Sleman, " + new Date().toLocaleDateString("id-ID"), 150, y, { align: "right" });
            y += 10;
            pdf.text("Yang menyatakan,", 150, y, { align: "right" });
            y += 30;
            pdf.text("Dhimas Arief D S.T., Ph.D.", 150, y, { align: "right" });
            y += 10;
            pdf.text("NIP. 19930704 202203 1 003", 150, y, { align: "right" });

            // Tampilkan PDF
            pdf.save("surat_pernyataan.pdf");
        } catch (error) {
            console.error("Error:", error);
            alert("Terjadi kesalahan saat membuat PDF.");
        }
    });
});