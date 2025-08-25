<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Frontend/reset_password.php?error=username tidak valid");
        exit();
    }

    // Check if username exists in database
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: ../Frontend/reset_password.php?error=username tidak ditemukan");
        exit();
    }

    $user = $result->fetch_assoc();
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Save token to database
    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user['id'], $token, $expires);
    
    if (!$stmt->execute()) {
        error_log("Database Error: " . $stmt->error);
        header("Location: ../Frontend/reset_password.php?error=Sistem error");
        exit();
    }

    // Configure username settings
    ini_set('SMTP', 'localhost');
    ini_set('smtp_port', 25);
    
    // Prepare username content
    $resetLink = "http://localhost/HKI/Frontend/new_password.php?token=" . urlencode($token);
    $to = $username;
    $subject = "Reset Password HKI";
    $message = "
    <html>
    <body>
        <h2>Reset Password HKI</h2>
        <p>Anda telah meminta untuk mereset password akun HKI Anda.</p>
        <p>Klik link berikut untuk melanjutkan:</p>
        <p><a href=\"$resetLink\">Reset Password</a></p>
        <p>Atau copy paste link berikut ke browser Anda:</p>
        <p>$resetLink</p>
        <p><strong>Link ini akan kadaluarsa dalam 1 jam.</strong></p>
        <p>Jika Anda tidak meminta reset password, abaikan username ini.</p>
    </body>
    </html>";

    // username headers
    $headers = array(
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: HKI System <noreply@hki.com>',
        'Reply-To: noreply@hki.com',
        'X-Mailer: PHP/' . phpversion()
    );

    // Try to send username
    $mailSent = mail($to, $subject, $message, implode("\r\n", $headers));

    if ($mailSent) {
        // Log success
        error_log("Reset password username sent to: " . $username);
        header("Location: ../Frontend/login.php?message=Instruksi reset password telah dikirim ke username Anda");
    } else {
        // Log error
        error_log("Failed to send reset password username to: " . $username);
        header("Location: ../Frontend/reset_password.php?error=Gagal mengirim username reset. Silakan coba lagi nanti.");
    }
    exit();
}