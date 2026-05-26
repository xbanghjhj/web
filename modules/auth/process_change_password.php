<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

if (!isPostRequest()) {
    redirect(url('modules/auth/change_password.php'));
}

$currentUser = getCurrentUser();
$userId = (int) $currentUser['id'];
$isFirstLogin = mustChangePassword();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($newPassword === '' || $confirmPassword === '' || (!$isFirstLogin && $currentPassword === '')) {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin.');
    redirect(url('modules/auth/change_password.php'));
}

if ($newPassword !== $confirmPassword) {
    setFlashMessage('error', 'Mật khẩu mới và xác nhận mật khẩu không khớp.');
    redirect(url('modules/auth/change_password.php'));
}

if (strlen($newPassword) < 6) {
    setFlashMessage('error', 'Mật khẩu mới phải có ít nhất 6 ký tự.');
    redirect(url('modules/auth/change_password.php'));
}

$user = fetchOne('SELECT * FROM users WHERE id = ? LIMIT 1', 'i', [$userId]);
if (!$user) {
    setFlashMessage('error', 'Không tìm thấy thông tin người dùng.');
    redirect(url('modules/auth/change_password.php'));
}

if (!$isFirstLogin && !verifyPassword($currentPassword, $user['password'])) {
    setFlashMessage('error', 'Mật khẩu hiện tại không đúng.');
    redirect(url('modules/auth/change_password.php'));
}

$result = executeQuery(
    'UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?',
    'si',
    [hashPassword($newPassword), $userId]
);

if (!$result) {
    setFlashMessage('error', 'Có lỗi xảy ra khi cập nhật mật khẩu.');
    redirect(url('modules/auth/change_password.php'));
}

$_SESSION['must_change_password'] = 0;

setFlashMessage('success', 'Đổi mật khẩu thành công.');
redirect(routeByRole($currentUser['role']));
