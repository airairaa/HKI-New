<?php
include 'session_check.php';
require('fpdf.php');
$host = "localhost";
$user = "root"; // Ganti dengan user database
$pass = ""; // Ganti dengan password database
$dbname = "hki";

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Ambil data surat terakhir
$sql = "SELECT * FROM detail_permohonan ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);


$data = $result->fetch_assoc();

$jenis_ciptaan = isset($data['jenis_ciptaan']) ? $data['jenis_ciptaan'] : 'Tidak tersedia';
$judul = isset($data['judul']) ? $data['judul'] : 'Tidak tersedia';
$jenis_pendanaan = isset($data['jenis_pendanaan']) ? $data['jenis_pendanaan'] : 'Tidak tersedia';

class PDF extends FPDF {
    function Header() {
        $this->Ln(1);
    }

    function JustifyText($text) {
        $this->SetFont('Times', '', 10); // Menggunakan Times New Roman
        $words = explode(' ', $text);
        $line = '';
        $lineHeight = 10;
        $pageWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;

        foreach ($words as $word) {
            $testLine = $line . $word . ' ';
            $testLineWidth = $this->GetStringWidth($testLine);

            if ($testLineWidth > $pageWidth) {
                $this->PrintJustifiedLine($line, $pageWidth);
                $line = $word . ' ';
            } else {
                $line = $testLine;
            }
        }
        if (!empty($line)) {
            $this->PrintJustifiedLine($line, $pageWidth);
        }
    }

    function PrintJustifiedLine($line, $pageWidth) {
        $words = explode(' ', trim($line));
        $numWords = count($words);
        if ($numWords === 0) return;

        $totalWidth = 0;
        foreach ($words as $word) {
            $totalWidth += $this->GetStringWidth($word) + $this->GetStringWidth(' ');
        }

        if ($numWords > 1) {
            $spaceWidth = ($pageWidth - $totalWidth) / ($numWords - 1);
            $this->SetX($this->lMargin);

            foreach ($words as $i => $word) {
                $this->Write(10, $word);
                if ($i < $numWords - 1) {
                    $this->Write($spaceWidth, ' ');
                }
            }
        } else {
            $this->SetX($this->lMargin);
            $this->Write(10, $line);
        }
        $this->Ln();
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetMargins(20, 20, 20);

$pdf->SetFont('Times', 'BU', 14);
$pdf->Cell(0, 10, 'SURAT PERNYATAAN', 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetFont('Times', '', 10);
$pdf->Cell(0, 10, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');
$pdf->Ln(3);

$data_pemohon = [
    'Nama' => 'Universitas Pembangunan Nasional "Veteran" Yogyakarta',
    'Kewarganegaraan' => 'Indonesia',
    'Alamat' => "Lembaga Penelitian dan Pengabdian kepada Masyarakat, Gedung Rektorat\nLantai 4, Kampus 1 Universitas Pembangunan Nasional \"Veteran\" Yogyakarta, Jl. Padjajaran No.104, Condongcatur, Depok, Sleman 55283"
];

foreach ($data_pemohon as $key => $value) {
    $pdf->SetX(20);
    $pdf->Cell(40, 5, $key, 0, 0, 'J');
    $pdf->Cell(5, 5, ':', 0, 0, 'J');
    $pdf->MultiCell(0, 5, $value, 0, 'J');
}

$pdf->Ln(5);
$pdf->Cell(0, 10, 'Dengan ini menyatakan bahwa:', 0, 1, 'L');

$pdf->Cell(10, 7, '1.', 0, 0, 'L');
$pdf->Cell(0, 7, 'Karya Cipta yang kami mohonkan:', 0, 1, 'L');
$pdf->SetX(30);
$pdf->Cell(30, 7, 'Berupa', 0, 0, 'L');
$pdf->Cell(5, 7, ':', 0, 0, 'L');
$pdf->Cell(0, 7, $jenis_ciptaan, 0, 1, 'L');
$pdf->SetX(30);
$pdf->Cell(30, 7, 'Berjudul', 0, 0, 'L');
$pdf->Cell(5, 7, ':', 0, 0, 'L');
$pdf->Cell(0, 7, $judul, 0, 1, 'L');
$pdf->Ln(3);

$points = [
    'Tidak meniru dan tidak sama secara esensial dengan Karya Cipta milik pihak lain atau obyek kekayaan intelektual lainnya sebagaimana dimaksud dalam Pasal 68 ayat (2);',
    'Bukan merupakan Ekspresi Budaya Tradisional sebagaimana dimaksud dalam Pasal 38;',
    'Bukan merupakan Ciptaan yang tidak diketahui penciptanya sebagaimana dimaksud dalam Pasal 39;',
    'Bukan merupakan hasil karya yang tidak dilindungi Hak Cipta sebagaimana dimaksud dalam Pasal 41 dan 42;',
    'Bukan merupakan Ciptaan seni lukis yang berupa logo atau tanda pembeda yang digunakan sebagai merek dalam perdagangan barang/jasa atau digunakan sebagai lambang organisasi, badan usaha, atau badan hukum sebagaimana dimaksud dalam Pasal 65 dan;',
    'Bukan merupakan Ciptaan yang melanggar norma agama, norma susila, ketertiban umum, pertahanan dan keamanan negara atau melanggar peraturan perundang-undangan sebagaimana dimaksud dalam Pasal 74 ayat (1) huruf d Undang-Undang Nomor 28 Tahun 2014 tentang Hak Cipta.'
];

foreach ($points as $point) {
    $pdf->SetX(20);
    $pdf->Cell(5, 5, chr(149), 0, 0, 'J');
    $pdf->MultiCell(0, 5, $point, 0, 'J');
    $pdf->Ln(2);
}

$pdf->Ln(5);
$pdf->SetX(20);
$pdf->Cell(5, 5, '2.', 0, 0, 'J');
$pdf->MultiCell(0, 5, 'Sebagai pemohon mempunyai kewajiban untuk menyimpan asli contoh ciptaan yang dimohonkan dan harus memberikan apabila dibutuhkan untuk kepentingan penyelesaian sengketa perdata maupun pidana sesuai dengan ketentuan perundang-undangan.', 0, 'J');
$pdf->Ln(5);

$pdf->SetX(20);
$pdf->Cell(5, 5, '3.', 0, 0, 'J');
$pdf->MultiCell(0, 5, 'Karya Cipta yang saya mohonkan pada Angka 1 tersebut di atas tidak pernah dan tidak sedang dalam sengketa pidana dan/atau perdata di Pengadilan.', 0, 'J');
$pdf->Ln(5);

$pdf->SetX(20);
$pdf->Cell(5, 5, '4.', 0, 0, 'J');
$pdf->MultiCell(0, 5, 'Dalam hal ketentuan sebagaimana dimaksud dalam Angka 1 dan Angka 3 tersebut di atas saya / kami langgar, maka saya / kami bersedia secara sukarela bahwa:', 0, 'J');
$pdf->Ln(2);

$poin_abc = [
    'permohonan hak cipta yang saya ajukan dianggap ditarik kembali; atau',
    'Karya Cipta yang telah terdaftar dalam Daftar Umum Ciptaan Direktorat Hak Cipta, Direktorat Jenderral Hak Kekayaan Intelektual, Kementerian Hukum Dan Hak Asasi Manusia R.I dihapuskan sesuai dengan ketentuan perundang-undangan yang berlaku.',
    'Dalam hal kepemilikan Hak Cipta yang dimohonkan secara elektronik sedang dalam perkara dan/atau sedang dalam gugatan di Pengadilan maka status kepemilikan surat pencatatan elektronik tersebut ditangguhkan menunggu putusan Pengadilan yang berkekuatan hukum tetap.'
];

$letter = 97;
foreach ($poin_abc as $poin) {
    $pdf->SetX(20);
    $pdf->Cell(10, 5, chr($letter) . '.', 0, 0, 'L');
    $pdf->MultiCell(0, 5, $poin, 0, 'J');
    $pdf->Ln(2);
    $letter++;
}

$pdf->Ln(8);
$pdf->Cell(5, 5, '5.', 0, 0, 'L');
$pdf->MultiCell(0, 5, 'Karya Cipta didapatkan dengan "Dukungan UPN \"Veteran\" Yogyakarta" yang bersumber dari:', 0, 'L');
$pdf->Ln(2);

$pdf->SetFont('Times', '', 11);
$pdf->SetFillColor(204, 255, 204);
$pdf->Cell(85, 10, "Pendanaan (" . $jenis_pendanaan . ") UPN \"Veteran\" \nYogyakarta", 1, 0, 'C', true);

// $pdf->SetFillColor(255, 255, 255);
// $pdf->Cell(85, 10, $jenis_pendanaan, 1, 0, 'C');

$pdf->Ln(10);
$pdf->Cell(20, 10, 'Dengan angka pendanaan sejumlah: Rp. 200.000,-', 0, 1, 'L');
$pdf->Ln(5);

$pdf->Ln(5);
$pdf->MultiCell(0, 5, 'Demikian Surat pernyataan ini saya/kami buat dengan sebenarnya dan untuk dipergunakan sebagaimana mestinya.', 0, 'L');
$pdf->Ln(15);

$pdf->Cell(0, 7, 'Sleman,' . date('d F Y'), 0, 1, 'R');
$pdf->Ln(5);
$pdf->Cell(0, 7, 'Yang menyatakan,', 0, 1, 'R');
$pdf->Cell(0, 7, 'Ketua LPPM UPN "Veteran" Yogyakarta', 0, 1, 'R');
$pdf->Ln(10);
$pdf->Cell(0, 7, 'Materai 10.000,-', 0, 1, 'R');
$pdf->Ln(5);
$pdf->Cell(0, 7, 'Dhimas Arief D S.T., Ph.D.', 0, 1, 'R');
$pdf->Ln(2);
$pdf->Cell(0, 7, 'NIP. 19930704 202203 1 003 ', 0, 1, 'R');

$pdf->Output('surat_pernyataan.pdf', 'I');
?>