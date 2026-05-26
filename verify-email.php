<?php
require_once 'config/config.php';

$token = cleanText($_GET['token'] ?? '');
if ($token === '') {
    setFlashMessage('error', 'Liên kết xác thực không hợp lệ.');
    redirect(url());
}

$tokenData = fetchOne(
    'SELECT et.*, u.username, u.full_name, u.email FROM email_tokens et JOIN users u ON et.user_id = u.id WHERE et.token = ? AND et.is_used = 0 LIMIT 1',
    's',
    [$token]
);

if (!$tokenData) {
    setFlashMessage('error', 'Liên kết xác thực không tồn tại hoặc đã được sử dụng.');
    redirect(url());
}

if (new DateTime() > new DateTime($tokenData['expires_at'])) {
    setFlashMessage('error', 'Liên kết xác thực đã hết hạn. Vui lòng liên hệ Admin để gửi lại email mới.');
    redirect(url());
}

executeQuery('UPDATE email_tokens SET is_used = 1 WHERE id = ?', 'i', [$tokenData['id']]);
$_SESSION['verified_user_id'] = $tokenData['user_id'];

setFlashMessage('success', 'Xác thực email thành công. Bạn có thể đăng nhập ngay bây giờ.');
redirect(url());
