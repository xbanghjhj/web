<?php
/**
 * ===================================================
 * LOGOUT - Đăng xuất
 * ===================================================
 */

session_start();

// Xóa tất cả session
session_unset();
session_destroy();

// Xóa cookies nếu có
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect về trang login
header("Location: index.php");
exit();
?>
