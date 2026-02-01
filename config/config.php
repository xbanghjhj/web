<?php
/**
 * ===================================================
 * SYSTEM CONFIGURATION - Cấu hình hệ thống
 * ===================================================
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ===================================================
// CÁC ĐƯỜNG DẪN CƠ BẢN
// ===================================================
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/Demo DA21');

// Thư mục
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/assets/uploads');
define('UPLOADS_URL', ASSETS_URL . '/uploads');

// ===================================================
// CẤU HÌNH HỆ THỐNG
// ===================================================
define('SITE_NAME', 'POS System - Mobile & Accessories');
define('SITE_SHORT_NAME', 'POS System');
define('COMPANY_NAME', 'Cửa hàng điện thoại & phụ kiện');

// ===================================================
// CẤU HÌNH TÀI KHOẢN
// ===================================================
// Password mặc định cho nhân viên mới
// ⚠️ QUAN TRỌNG: Thay bằng MSSV trưởng nhóm (viết thường)
define('DEFAULT_PASSWORD', 'da210001'); // Thay đổi thành MSSV của bạn

// Thời gian token email (giây) - 1 phút theo yêu cầu
define('EMAIL_TOKEN_EXPIRE', 60); // 60 giây = 1 phút

// Session timeout (giây) - 2 giờ
define('SESSION_TIMEOUT', 7200);

// ===================================================
// CẤU HÌNH UPLOAD
// ===================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// ===================================================
// CẤU HÌNH PHÂN TRANG
// ===================================================
define('ITEMS_PER_PAGE', 20);

// ===================================================
// CẤU HÌNH BÁO CÁO
// ===================================================
define('CURRENCY_SYMBOL', '₫');
define('CURRENCY_FORMAT', 'number_format');

// ===================================================
// ERROR HANDLING
// ===================================================
// Development mode
define('DEBUG_MODE', true); // Đổi thành false khi deploy

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// ===================================================
// ROLES & PERMISSIONS
// ===================================================
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');

// ===================================================
// STATUS
// ===================================================
define('STATUS_ACTIVE', 'active');
define('STATUS_LOCKED', 'locked');

// ===================================================
// HELPER FUNCTIONS
// ===================================================

/**
 * Format số tiền
 */
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . CURRENCY_SYMBOL;
}

/**
 * Format ngày tháng
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Format ngày giờ
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i:s') {
    return date($format, strtotime($datetime));
}

/**
 * Redirect tới URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Lấy URL đầy đủ
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Lấy URL assets
 */
function asset($path) {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

/**
 * Sanitize input
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

/**
 * Get và xóa flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'avatar' => $_SESSION['avatar'] ?? 'avatar-default.png'
    ];
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === ROLE_ADMIN;
}

/**
 * Check if user is staff
 */
function isStaff() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === ROLE_STAFF;
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Upload file
 */
function uploadFile($file, $destination = 'uploads') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Check file type
    $fileType = $file['type'];
    if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Upload path
    $uploadPath = UPLOADS_PATH . '/' . $destination . '/' . $filename;
    
    // Create directory if not exists
    if (!is_dir(dirname($uploadPath))) {
        mkdir(dirname($uploadPath), 0755, true);
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Delete file
 */
function deleteFile($filename, $folder = 'uploads') {
    $filePath = UPLOADS_PATH . '/' . $folder . '/' . $filename;
    if (file_exists($filePath) && is_file($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Get avatar URL
 */
function getAvatarUrl($avatar) {
    if (empty($avatar) || $avatar === 'avatar-default.png') {
        return asset('images/avatar-default.png');
    }
    return UPLOADS_URL . '/avatars/' . $avatar;
}

/**
 * Get product image URL
 */
function getProductImageUrl($image) {
    if (empty($image) || $image === 'no-image.png') {
        return asset('images/no-image.png');
    }
    return UPLOADS_URL . '/products/' . $image;
}

// ===================================================
// LOAD DATABASE CONNECTION
// ===================================================
require_once __DIR__ . '/database.php';

?>
