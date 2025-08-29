<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page with notification
    header("Location: ../Frontend/login.php?m=login_required");
    exit();
}

// Optional: Check if user role is required for specific pages
function checkRole($required_role = null) {
    if ($required_role && (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role)) {
        header("Location: ../Frontend/login.php?m=unauthorized");
        exit();
    }
}
?> 