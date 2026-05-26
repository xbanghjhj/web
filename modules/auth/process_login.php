<?php
require_once '../../config/config.php';

if (!isPostRequest()) {
    redirect(url());
}

$username = cleanText($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

if ($username === '' || $password === '') {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin.');
    redirect(url());
}

$user = fetchOne(
    'SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1',
    'ss',
    [$username, $username]
);

if (!$user) {
    setFlashMessage('error', 'Tên đăng nhập hoặc mật khẩu không đúng.');
    redirect(url());
}

if (($user['status'] ?? STATUS_ACTIVE) === STATUS_LOCKED) {
    setFlashMessage('error', 'Tài khoản của bạn đang bị khóa. Vui lòng liên hệ Admin.');
    redirect(url());
}

if (!verifyPassword($password, $user['password'])) {
    setFlashMessage('error', 'Tên đăng nhập hoặc mật khẩu không đúng.');
    redirect(url());
}

if (($user['role'] ?? ROLE_STAFF) === ROLE_STAFF && !empty($user['must_change_password'])) {
    if (empty($_SESSION['verified_user_id']) || (int) $_SESSION['verified_user_id'] !== (int) $user['id']) {
        setFlashMessage('error', 'Vui lòng đăng nhập bằng cách nhập vào liên kết trong email của bạn.');
        redirect(url());
    }
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role'] = $user['role'];
$_SESSION['avatar'] = $user['avatar'];
$_SESSION['must_change_password'] = (int) $user['must_change_password'];
$_SESSION['login_time'] = time();

unset($_SESSION['verified_user_id']);

if ($remember) {
    $cookieToken = generateToken(32);
    setcookie('remember_token', $cookieToken, time() + (30 * 24 * 60 * 60), '/');
}

if (!empty($user['must_change_password'])) {
    setFlashMessage('warning', 'Bạn phải đổi mật khẩu trước khi sử dụng hệ thống.');
    redirect(url('modules/auth/change_password.php'));
}

setFlashMessage('success', 'Đăng nhập thành công. Xin chào ' . $user['full_name'] . '!');
redirect(routeByRole($user['role']));
