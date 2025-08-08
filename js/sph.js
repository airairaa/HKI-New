document.addEventListener("DOMContentLoaded", function () {
    const generatePdfButton = document.getElementById("generate-pdf");

    generatePdfButton.addEventListener("click", async function () {
        try {
            // Ambil data dari backend
            const response = await fetch("get_sph_data.php");
            const result = await response.json();

            if (!result.success) {
                alert("Gagal mengambil data: " + result.message);
                return;
            }

            const { jenis_pendanaan, pengusul } = result.data;

            // Generate PDF menggunakan jsPDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();

            pdf.setFont("Times", "BU");
            pdf.setFontSize(14);
            pdf.text("SURAT PENGALIHAN HAK CIPTA", 105, 20, { align: "center" });

            pdf.setFont("Times", "normal");
            pdf.setFontSize(12);
            pdf.text("Yang bertanda tangan di bawah ini:", 20, 30);

            let y = 40;
            pengusul.forEach((item, index) => {
                pdf.text(`${index + 1}. Nama: ${item.nama}`, 20, y);
                y += 10;
                pdf.text(`   Pekerjaan: ${item.role}`, 20, y);
                y += 10;
                pdf.text(`   Alamat: ${item.alamat}`, 20, y);
                y += 15;
            });

            pdf.text("yang selanjutnya disebut sebagai PIHAK PERTAMA,", 20, y);
            y += 10;

            pdf.text("Nama: Universitas Pembangunan Nasional \"Veteran\" Yogyakarta", 20, y);
            y += 10;
            pdf.text("Alamat: Lembaga Penelitian dan Pengabdian kepada Masyarakat,", 20, y);
            y += 10;
            pdf.text("Gedung Rektorat Lantai 4, Kampus 1 Universitas Pembangunan Nasional \"Veteran\" Yogyakarta,", 20, y);
            y += 10;
            pdf.text("Jl. Padjajaran No.104, Condongcatur, Depok, Sleman 55283", 20, y);
            y += 15;

            pdf.text("yang selanjutnya disebut sebagai PIHAK KEDUA.", 20, y);
            y += 15;

            pdf.text("PIHAK PERTAMA dan PIHAK KEDUA dengan ini menyatakan sebagai berikut:", 20, y);
            y += 10;

            const points = [
                "Bahwa PIHAK PERTAMA adalah Pencipta atas JENIS CIPTAAN yang berjudul \"JUDUL CIPTAAN\".",
                "Bahwa JENIS CIPTAAN ciptaan PIHAK PERTAMA tersebut diciptakan dengan dukungan UPN \"Veteran\" Yogyakarta.",
                "Dukungan UPN \"Veteran\" Yogyakarta bersumber dari: " + jenis_pendanaan,
                "Bahwa PIHAK PERTAMA mengalihkan kepada PIHAK KEDUA Hak Cipta atas JENIS CIPTAAN tersebut."
            ];

            points.forEach((point, index) => {
                pdf.text(`${index + 1}. ${point}`, 20, y);
                y += 10;
            });

            pdf.text("Demikian Surat Pengalihan Hak Cipta ini dibuat dengan sebenarnya.", 20, y);
            y += 20;

            pdf.text("Sleman, " + new Date().toLocaleDateString("id-ID"), 150, y, { align: "right" });
            y += 10;
            pdf.text("PIHAK PERTAMA", 20, y);
            pdf.text("PIHAK KEDUA", 150, y, { align: "right" });
            y += 30;
            pdf.text("(Nama Pencipta)", 20, y);
            pdf.text("Dhimas Arief D S.T., Ph.D.", 150, y, { align: "right" });

            // Tampilkan PDF
            pdf.save("surat_pengalihan_hak_cipta.pdf");
        } catch (error) {
            console.error("Error:", error);
            alert("Terjadi kesalahan saat membuat PDF.");
        }
    });
});