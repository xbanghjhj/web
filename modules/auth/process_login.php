<?php
/**
 * ===================================================
 * PROCESS LOGIN - Xử lý đăng nhập
 * ===================================================
 */

require_once '../../config/config.php';
require_once '../../config/database.php';

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(url());
}

// Lấy dữ liệu từ form
$username = cleanInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
if (empty($username) || empty($password)) {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin!');
    redirect(url());
}

// Tìm user trong database (có thể đăng nhập bằng username hoặc email)
$query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = ? LIMIT 1";
$user = fetchOne($query, 'sss', [$username, $username, STATUS_ACTIVE]);

// Kiểm tra user tồn tại
if (!$user) {
    setFlashMessage('error', 'Tên đăng nhập hoặc mật khẩu không đúng!');
    redirect(url());
}

// Kiểm tra password
if (!verifyPassword($password, $user['password'])) {
    setFlashMessage('error', 'Tên đăng nhập hoặc mật khẩu không đúng!');
    redirect(url());
}

// Kiểm tra tài khoản có bị khóa không
if ($user['status'] === STATUS_LOCKED) {
    setFlashMessage('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên!');
    redirect(url());
}

// ⚠️ QUAN TRỌNG: Kiểm tra nhân viên mới phải đăng nhập qua link email
// Nếu là nhân viên (không phải admin) và vẫn phải đổi mật khẩu và chưa có email token hợp lệ
if ($user['role'] === ROLE_STAFF && $user['must_change_password'] == 1) {
    // Kiểm tra xem có token email hợp lệ không (trong session từ verify-email.php)
    if (!isset($_SESSION['verified_user_id']) || $_SESSION['verified_user_id'] != $user['id']) {
        setFlashMessage('error', 'Vui lòng đăng nhập bằng cách nhấp vào liên kết trong email của bạn!');
        redirect(url());
    }
}

// Đăng nhập thành công - Lưu thông tin vào session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];
$_SESSION['avatar'] = $user['avatar'];
$_SESSION['must_change_password'] = $user['must_change_password'];
$_SESSION['login_time'] = time();

// Xóa verified_user_id khỏi session (đã dùng xong)
unset($_SESSION['verified_user_id']);

// Remember me - lưu cookie (optional)
if ($remember) {
    $cookieToken = generateToken(32);
    setcookie('remember_token', $cookieToken, time() + (30 * 24 * 60 * 60), '/'); // 30 ngày
}

// Nếu phải đổi mật khẩu -> redirect đến trang đổi mật khẩu
if ($user['must_change_password'] == 1) {
    setFlashMessage('warning', 'Bạn phải đổi mật khẩu trước khi sử dụng hệ thống!');
    redirect(url('modules/auth/change_password.php'));
}

// Redirect dựa vào role
if ($user['role'] === ROLE_ADMIN) {
    setFlashMessage('success', 'Chào mừng Admin ' . $user['full_name'] . '!');
    redirect(url('modules/dashboard/admin_dashboard.php'));
} else {
    setFlashMessage('success', 'Chào mừng ' . $user['full_name'] . '!');
    redirect(url('modules/dashboard/staff_dashboard.php'));
}
?>
