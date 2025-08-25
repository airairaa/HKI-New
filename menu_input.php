<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Frontend/login.php?m=nfound');
    exit();
}

// Tangkap pilihan dari frontend
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selection = $_POST['selection'];

    // Simpan pilihan ke session atau database
    $_SESSION['selection'] = $selection;

    // Redirect ke halaman yang sesuai
    switch ($selection) {
        case 'paten':
            header('Location: paten_form.php');
            break;
        case 'merek':
            header('Location: merek_form.php');
            break;
        case 'desain':
            header('Location: desain_form.php');
            break;
        case 'dtlst':
            header('Location: dtlst_form.php');
            break;
        default:
            header('Location: daftar_user.php');
    }
    exit();
}
?>