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

// Ambil data pengusul berdasarkan dataid permohonan terakhir
$dataid = isset($data['dataid']) ? $data['dataid'] : '';

$query = "SELECT Nama as nama, Alamat as alamat, 'Dosen' AS role FROM data_pribadi_dosen WHERE dataid = '" . $conn->real_escape_string($dataid) . "' UNION ALL SELECT Nama as nama, Alamat as alamat, 'Mahasiswa' AS role FROM data_pribadi_mahasiswa WHERE dataid = '" . $conn->real_escape_string($dataid) . "'";

$result = $conn->query($query);


$i = 1;


// Pastikan data yang dibutuhkan ada

// $jenis_pendanaan = isset($_POST['jenis_pendanaan']) ? $_POST['jenis_pendanaan'] : 'Tidak tersedia';
$jenis_pendanaan = isset($data['jenis_pendanaan']) ? $data['jenis_pendanaan'] : 'Tidak tersedia';
$jenis_hibah = isset($data['jenis_hibah']) ? $data['jenis_hibah'] : 'Tidak tersedia';
//$pendanaan = isset($_POST['pendanaanHidden']) ? $_POST['pendanaanHidden'] : 'Tidak ada data';

if($jenis_hibah == "hibah penelitian dosen pemula"){
 $test = "Rp. 50.000.000,00";
}
else if($jenis_hibah == "hibah penelitian dasar"){
 $test = "Rp. 100.000.000,00";
}
else if($jenis_hibah == "hibah penelitian dosen kolaborasi"){
 $test = "Rp. 150.000.000,00";
}
else if($jenis_hibah == "hibah penelitian mahasiswa"){
 $test = "Rp. 10.000.000,00";
}
else{
 $test = "Tidak tersedia";
}
// Set header agar langsung membuka PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="sph.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

class PDF extends FPDF {  
    function Header() {  
        // Set font  
        // Title  
        $this->Cell(0, 0, '', 0, 1, 'C');   
    }  

    function Footer() {  
        // Go to 1.5 cm from bottom  
        $this->SetY(-15);  
        // Set font  
        $this->SetFont('Times', 'I', 8);  
        // Page number  
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');  
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
                // Print the justified line
                $this->PrintJustifiedLine($line, $pageWidth);
                $line = $word . ' '; // Start a new line
            } else {
                $line = $testLine; // Add word to the current line
            }
        }
        // Print the last line
        if (!empty($line)) {
            $this->PrintJustifiedLine($line, $pageWidth);
        }
    }

    function PrintJustifiedLine($line, $pageWidth) {
        $words = explode(' ', trim($line));
        $numWords = count($words);
        if ($numWords === 0) return;
    
        // Calculate total width of words
        $totalWidth = 0;
        foreach ($words as $word) {
            $totalWidth += $this->GetStringWidth($word) + $this->GetStringWidth(' '); // Add space width
        }
    
        // Check if there's more than one word to justify
        if ($numWords > 1) {
            // Calculate total space needed
            $spaceWidth = ($pageWidth - $totalWidth) / ($numWords - 1);
            $this->SetX($this->lMargin); // Set X position
    
            // Print words with calculated spaces
            foreach ($words as $i => $word) {
                $this->Write(10, $word);
                if ($i < $numWords - 1) {
                    $this->Write($spaceWidth, ' '); // Add space
                }
            }
        } else {
            // If there's only one word, just print it without justification
            $this->SetX($this->lMargin); // Set X position
            $this->Write(10, $line);
        }
        $this->Ln(); // Move to the next line
    }
    
}  

// Inisialisasi kelas PDF  
$pdf = new PDF();  
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();

$pdf->SetFont('Times', 'BU', 14);
$pdf->Cell(0, 10, 'SURAT PENGALIHAN HAK CIPTA', 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetFont('Times', '', 11);
$pdf->Cell(0, 10, 'Yang bertanda tangan di bawah ini:', 0, 1, 'L');
$pdf->Ln(2);


//poin
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(5, 5, "$i.", 0, 0);
    $pdf->Cell(30, 5, 'Nama', 0, 0);
    $pdf->Cell(5, 5, ':', 0, 0);
    $pdf->Cell(0, 5, $row['nama'], 0, 1);

    $pdf->Cell(5, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Pekerjaan', 0, 0);
    $pdf->Cell(5, 5, ':', 0, 0);
    $pdf->Cell(0, 5, $row['role'], 0, 1, 'J');

    $pdf->Cell(5, 5, '', 0, 0);
    $pdf->Cell(30, 5, 'Alamat', 0, 0);
    $pdf->Cell(5, 5, ':', 0, 0);
    $pdf->Cell(0, 5, $row['alamat'], 0, 1);

    $pdf->Ln(5);
    $i++;
}
//pihak 1
$pdf->SetFont('Times', '', 11);
$pdf->Cell(0, 10, 'yang selanjutnya disebut sebagai PIHAK PERTAMA, dan ', 0, 1, 'L');
$pdf->Ln(2);

// Data pemohon
$pdf->Cell(30, 5, 'Nama', 0, 0);
$pdf->Cell(5, 5, ':', 0, 0);
$pdf->Cell(0, 5, 'Universitas Pembangunan Nasional "Veteran" Yogyakarta
', 0, 1);
$pdf->Cell(30, 5, '', 0, 0);
$pdf->Cell(5, 5, '', 0, 0);
$pdf->Cell(0, 5, 'dalam hal ini diwakili oleh Dr. Dyah Sugandini, S.E., M.Si., sebagai Ketua Lembaga', 0, 1);
$pdf->Cell(30, 5, '', 0, 0);
$pdf->Cell(5, 5, '', 0, 0);
$pdf->Cell(0, 5, 'Penelitian dan Pengabdian kepada Masyarakat', 0, 1);

$data = [
    'Alamat' => "Lembaga Penelitian dan Pengabdian kepada Masyarakat, Gedung Rektorat\nLantai 4, Kampus 1 Universitas Pembangunan Nasional \"Veteran\" Yogyakarta, Jl. Padjajaran No.104, Condongcatur, Depok, Sleman 55283"
];

foreach ($data as $key => $value) {
    $pdf->SetX(20); // Indentasi
    $pdf->Cell(30, 5, $key, 0, 0, 'J');
    $pdf->Cell(5, 5, ':', 0, 0, 'J');
    $pdf->MultiCell(0, 5, $value, 0, 'J');
}

//pihak 2
$pdf->Ln(7);
$pdf->SetFont('Times', '', 11);
$pdf->Cell(0, 10, 'yang selanjutnya disebut sebagai PIHAK KEDUA. ', 0, 1, 'L');

$pdf->SetX(20); // Indentasi
$pdf->MultiCell(0, 5, 'PIHAK PERTAMA dan PIHAK KEDUA yang selanjutnya secara bersama-sama disebut PARA PIHAK dan secara sendiri-sendiri disebut PIHAK dengan ini menyatakan sebagai berikut: ', 0, 'J');
$pdf->Ln(2);

$points = [
    'Bahwa PIHAK PERTAMA adalah Pencipta atas JENIS CIPTAAN yang berjudul "JUDUL CIPTAAN".',
    'Bahwa JENIS CIPTAAN ciptaan PIHAK PERTAMA tersebut diciptakan dengan "Dukungan UPN "Veteran" Yogyakarta" yaitu segala bentuk dukungan untuk menghasilkan Kekayaan Intelektual, baik berupa finasial atau dukungan lainnya baik secara langsung maupun yang disalurkan melalui UPN "Veteran" Yogyakarta atau dengan menggunakan nama UPN "Veteran" Yogyakarta, penggunaan substansial Sumber Daya UPN "Veteran" Yogyakarta, dan/atau bimbingan atau adanya masukan secara intelektual dari Dosen, Tenaga Kependidikan, dan/atau Peneliti di lingkungan UPN "Veteran" Yogyakarta oleh karenanya UPN "Veteran" Yogyakarta memiliki Hak Cipta atas JENIS CIPTAAN tersebut;',
    'Dukungan UPN "Veteran" Yogyakarta sebagaimana dimaksud pada Angka (2) bersumber dari:',
];


$counter = 1; // Nomor awal
foreach ($points as $point) {
    $pdf->SetX(20); // Indentasi
    $pdf->Cell(5, 5, $counter . '.', 0, 0, 'J'); // Cetak nomor poin
    $pdf->MultiCell(0, 5, $point, 0, 'J');       // Cetak isi poin
    $counter++;
    $pdf->Ln(2); // Tambahkan sedikit jarak antar poin
}

//tabel
$pdf->SetFont('Times', '', 11);
$pdf->SetFillColor(204, 255, 204); // Warna hijau muda
$pdf->Cell(85, 10, "Pendanaan Eksternal UPN \"Veteran\" \nYogyakarta", 1, 0, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Warna putih
$pdf->Cell(85, 10, $jenis_pendanaan, 1, 0, 'C');

//$pendanaan = isset($_POST['pendanaanHidden']) ? $_POST['pendanaanHidden'] : 'Tidak ada data';

$pdf->Ln(10);
$pdf->Cell(20, 10, 'Dengan angka pendanaan sejumlah: ' . $test, 0, 1, 'L');
$pdf->Ln(5);

//lanjut 4
$points = [
    'Bahwa PIHAK PERTAMA mengalihkan kepada PIHAK KEDUA dan sebaliknya PIHAK KEDUA menerima dari PIHAK PERTAMA, Hak Cipta atas JENIS CIPTAAN tersebut, yang mencakup pengalihan wewenang pengelolaan perlindungan atas Ciptaan tersebut, termasuk pengalihan hak ekonomi atas ciptaan tersebut baik keseluruhan ataupun sebagian secara bersama-sama kepada pihak-pihak lain;',
    'PIHAK PERTAMA akan mendapatkan seluruh pembayaran royati atas komersialisasi ciptaan JENIS CIPTAAN tersebut oleh PIHAK KEDUA;',
    'Bahwa PIHAK PERTAMA menjamin JENIS CIPTAAN ciptaannya tersebut tidak meniru atau melanggar Hak Cipta atau Karya Intelektual milik pihak lain serta ciptaan tersebut tidak pernah dan tidak sedang dalam sengketa Pidana dan/atau Perdata baik di Peradilan atau di luar Peradilan;',
    'Bahwa PIHAK PERTAMA menjamin membebaskan PIHAK KEDUA dari segala tuntutan Pihak Ketiga terkait dugaan pelanggaran Hak Cipta yang dilakukan oleh PIHAK PERTAMA atas penciptaan JENIS CIPTAAN tersebut.',
];


$counter = 4; // Nomor awal
foreach ($points as $point) {
    $pdf->SetX(20); // Indentasi
    $pdf->Cell(5, 5, $counter . '.', 0, 0, 'J'); // Cetak nomor poin
    $pdf->MultiCell(0, 5, $point, 0, 'J');       // Cetak isi poin
    $counter++;
    $pdf->Ln(2); // Tambahkan sedikit jarak antar poin
}

$pdf->Ln(2);
$pdf->SetX(20); // Indentasi
$pdf->MultiCell(0, 5, 'Demikian Surat Pengalihan Hak Cipta ini dibuat secara sadar dan sukarela, tanpa paksaan dari pihak manapun untuk dimanfaatkan sebagaimana mestinya.', 0, 'J');
$pdf->Ln(2);


// Tanggal dan Tempat
$pdf->Cell(0, 10, "Sleman, " . date('d F Y'), 0, 1, 'L');
$pdf->Ln(10);

// Pihak Pertama
$pdf->SetFont('Times', 'B', 12);
$pdf->Cell(90, 10, 'PIHAK PERTAMA', 0, 0, 'L');
$pdf->Cell(90, 10, 'PIHAK KEDUA', 0, 1, 'R');
$pdf->Ln(5);

// Materai
$pdf->SetFont('Times', '', 12);
$pdf->Cell(90, 7, 'Materai 10.000', 0, 1, 'L');
$pdf->Ln(10);

// Pihak Pertama - Nama Pencipta


$i = 1;

// echo $result;
while ($row = $result->fetch_object()) {   
    $pdf->Cell(90, 10, "( " . $row['nama'] . " )", 0, 1, 'L', true);
    $pdf->Ln(10);
    $i++;
}


// // Pihak Kedua
// $pdf->Cell(90, 10, '--------Nama Pencipta 1--------', 0, 0, 'L', true);
$pdf->Cell(180, 10, 'Dhimas Arief D S.T., Ph.D.', 0, 1, 'R');

$result->data_seek(0); // Mengembalikan pointer hasil query ke awal
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(90, 10, "($row[nama])", 0, 0, 'L');
    $pdf->Ln(30);
}

// Menyimpan atau mengirim PDF ke browser  
$pdf->Output('surat_pengalihan_hak_cipta.pdf', 'I');  
?>