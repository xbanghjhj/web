<?php
/**
 * Shared login guard for protected routes.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireLogin();

$sessionUser = fetchOne(
    'SELECT id, full_name, avatar, role, status, must_change_password FROM users WHERE id = ? LIMIT 1',
    'i',
    [(int) $_SESSION['user_id']]
);

if (!$sessionUser) {
    session_unset();
    session_destroy();
    header('Location: ' . url('index.php'));
    exit();
}

if (($sessionUser['status'] ?? STATUS_ACTIVE) === STATUS_LOCKED) {
    session_unset();
    session_destroy();
    session_start();
    setFlashMessage('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.');
    header('Location: ' . url('index.php'));
    exit();
}

$_SESSION['full_name'] = $sessionUser['full_name'];
$_SESSION['avatar'] = $sessionUser['avatar'];
$_SESSION['role'] = $sessionUser['role'];
$_SESSION['must_change_password'] = (int) $sessionUser['must_change_password'];

$sessionTimeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 7200;
if (!empty($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $sessionTimeout)) {
    session_unset();
    session_destroy();
    header('Location: ' . url('index.php?timeout=1'));
    exit();
}

enforcePasswordChange();

$_SESSION['last_activity'] = time();
