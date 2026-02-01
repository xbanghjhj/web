<?php
/**
 * ===================================================
 * EMAIL VERIFICATION - Xác thực email (link 1 phút)
 * ===================================================
 */

require_once 'config/config.php';
require_once 'config/database.php';

// Lấy token từ URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    setFlashMessage('error', 'Link xác thực không hợp lệ!');
    redirect(url());
}

// Tìm token trong database
$query = "SELECT et.*, u.username, u.full_name, u.email 
          FROM email_tokens et 
          JOIN users u ON et.user_id = u.id 
          WHERE et.token = ? AND et.is_used = 0 
          LIMIT 1";
$tokenData = fetchOne($query, 's', [$token]);

if (!$tokenData) {
    setFlashMessage('error', 'Link xác thực không tồn tại hoặc đã được sử dụng!');
    redirect(url());
}

// Kiểm tra token đã hết hạn chưa (1 phút)
$now = new DateTime();
$expiresAt = new DateTime($tokenData['expires_at']);

if ($now > $expiresAt) {
    setFlashMessage('error', 'Link xác thực đã hết hạn (quá 1 phút). Vui lòng liên hệ Admin để gửi lại email!');
    redirect(url());
}

// Token hợp lệ - Đánh dấu đã sử dụng
$updateQuery = "UPDATE email_tokens SET is_used = 1 WHERE id = ?";
executeQuery($updateQuery, 'i', [$tokenData['id']]);

// Lưu thông tin vào session để cho phép đăng nhập
$_SESSION['verified_user_id'] = $tokenData['user_id'];

// Thông báo thành công và redirect về trang login
setFlashMessage('success', 'Xác thực email thành công! Bạn có thể đăng nhập ngay bây giờ.');
redirect(url());
?>
