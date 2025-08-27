<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        header("Location: ../Frontend/new_password.php?token=$token&error=Password tidak cocok");
        exit();
    }
    
    // Verify token and get user
    $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: ../Frontend/reset_password.php?error=Token tidak valid atau kadaluarsa");
        exit();
    }
    
    $reset = $result->fetch_assoc();
    $user_id = $reset['user_id'];
    
    // Update password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if (!$stmt->execute()) {
        header("Location: ../Frontend/new_password.php?token=$token&error=Gagal update password");
        exit();
    }
    
    // Mark token as used
    $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    header("Location: ../Frontend/login.php?message=Password berhasil diubah");
    exit();
}