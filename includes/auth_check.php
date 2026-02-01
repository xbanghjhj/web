<?php
// ===================================================
// AUTHENTICATION CHECK - include ở đầu các page cần login
// ===================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đã đăng nhập chưa
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '/Demo DA21') . "/index.php");
    exit();
}

// Kiểm tra session timeout (2 giờ)
$sessionTimeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 7200;
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $sessionTimeout)) {
    session_unset();
    session_destroy();
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '/Demo DA21') . "/index.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
