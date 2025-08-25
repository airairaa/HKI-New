<?php
session_start();
include '../koneksi.php'; // Pastikan koneksi database sudah benar

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query cek user berdasarkan email dan password
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Simpan informasi user ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role']; // Pastikan tabel `users` memiliki kolom `role`

        // Periksa role pengguna
        if ($user['role'] === 'admin') {
            header("Location: ../Frontend/review_ad.php"); // Arahkan ke halaman admin
        } else {
            header("Location: ../Frontend/menu_input.php"); // Arahkan ke halaman pengguna biasa
        }
    } else {
        header("Location: ../Frontend/login.php?m=wrong"); // Kalau gagal login
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../Frontend/login.php");
}
?>