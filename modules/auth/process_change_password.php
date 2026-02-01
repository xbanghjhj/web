<?php
/**
 * ===================================================
 * PROCESS CHANGE PASSWORD - Xử lý đổi mật khẩu
 * ===================================================
 */

require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url('modules/auth/change_password.php'));
}

$currentUser = getCurrentUser();
$userId = $currentUser['id'];

// Lấy dữ liệu từ form
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin!');
    redirect(url('modules/auth/change_password.php'));
}

// Kiểm tra mật khẩu mới và xác nhận khớp nhau
if ($newPassword !== $confirmPassword) {
    setFlashMessage('error', 'Mật khẩu mới và xác nhận mật khẩu không khớp!');
    redirect(url('modules/auth/change_password.php'));
}

// Kiểm tra độ dài mật khẩu mới
if (strlen($newPassword) < 6) {
    setFlashMessage('error', 'Mật khẩu mới phải có ít nhất 6 ký tự!');
    redirect(url('modules/auth/change_password.php'));
}

// Lấy thông tin user từ database
$user = fetchOne("SELECT * FROM users WHERE id = ? LIMIT 1", 'i', [$userId]);

if (!$user) {
    setFlashMessage('error', 'Không tìm thấy thông tin người dùng!');
    redirect(url('modules/auth/change_password.php'));
}

// Kiểm tra mật khẩu hiện tại
if (!verifyPassword($currentPassword, $user['password'])) {
    setFlashMessage('error', 'Mật khẩu hiện tại không đúng!');
    redirect(url('modules/auth/change_password.php'));
}

// Hash mật khẩu mới
$hashedPassword = hashPassword($newPassword);

// Cập nhật mật khẩu trong database
$updateQuery = "UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?";
$result = executeQuery($updateQuery, 'si', [$hashedPassword, $userId]);

if (!$result) {
    setFlashMessage('error', 'Có lỗi xảy ra khi đổi mật khẩu!');
    redirect(url('modules/auth/change_password.php'));
}

// Cập nhật session
$_SESSION['must_change_password'] = 0;

// Thông báo thành công
setFlashMessage('success', 'Đổi mật khẩu thành công!');

// Redirect về dashboard tương ứng
if ($currentUser['role'] === ROLE_ADMIN) {
    redirect(url('modules/dashboard/admin_dashboard.php'));
} else {
    redirect(url('modules/dashboard/staff_dashboard.php'));
}
?>
