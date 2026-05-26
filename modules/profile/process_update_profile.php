<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

if (!isPostRequest()) {
    redirect(url('modules/profile/view_profile.php'));
}

$userId = (int) $_SESSION['user_id'];
$fullName = cleanText($_POST['full_name'] ?? '');
$phone = normalizePhone($_POST['phone'] ?? '');

if ($fullName === '') {
    setFlashMessage('error', 'Họ tên không được để trống.');
    redirect(url('modules/profile/view_profile.php'));
}

if ($phone !== '' && !isValidPhoneNumber($phone)) {
    setFlashMessage('error', 'Số điện thoại không hợp lệ.');
    redirect(url('modules/profile/view_profile.php'));
}

$currentUser = fetchOne('SELECT * FROM users WHERE id = ? LIMIT 1', 'i', [$userId]);
if (!$currentUser) {
    setFlashMessage('error', 'Không tìm thấy tài khoản.');
    redirect(routeByRole());
}

$avatarName = $currentUser['avatar'];
if (!empty($_FILES['avatar']['name'])) {
    $uploadedAvatar = uploadFile($_FILES['avatar'], 'avatars');
    if (!$uploadedAvatar) {
        setFlashMessage('error', 'Upload avatar thất bại. Vui lòng kiểm tra định dạng file.');
        redirect(url('modules/profile/view_profile.php'));
    }

    if (!empty($avatarName) && $avatarName !== 'avatar-default.png') {
        deleteFile($avatarName, 'avatars');
    }

    $avatarName = $uploadedAvatar;
}

$result = executeQuery(
    'UPDATE users SET full_name = ?, phone = ?, avatar = ? WHERE id = ?',
    'sssi',
    [$fullName, $phone, $avatarName, $userId]
);

if (!$result) {
    setFlashMessage('error', 'Không thể cập nhật thông tin cá nhân.');
    redirect(url('modules/profile/view_profile.php'));
}

$_SESSION['full_name'] = $fullName;
$_SESSION['avatar'] = $avatarName;

setFlashMessage('success', 'Đã cập nhật thông tin cá nhân.');
redirect(url('modules/profile/view_profile.php'));
