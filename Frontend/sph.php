<?php
include '../Backend/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Surat Pengalihan Hak Cipta</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="sph.js" defer></script>
</head>
<body>
    <div class="container">
        <h1 class="title">Generate Surat Pengalihan Hak Cipta</h1>
        <button id="generate-pdf" class="btn">Generate PDF</button>
    </div>
</body>
</html>