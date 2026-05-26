<?php
/**
 * Core application configuration and shared helpers.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

function detectBaseUrl() {
    $configuredUrl = getenv('APP_BASE_URL');
    if (!empty($configuredUrl)) {
        return rtrim($configuredUrl, '/');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $projectFolder = rawurlencode(basename(dirname(__DIR__)));

    return $scheme . '://' . $host . '/' . $projectFolder;
}

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', detectBaseUrl());
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/assets/uploads');
define('UPLOADS_URL', ASSETS_URL . '/uploads');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('EMAIL_LOG_PATH', STORAGE_PATH . '/email_logs');

define('SITE_NAME', 'POS System - Mobile & Accessories');
define('SITE_SHORT_NAME', 'POS System');
define('COMPANY_NAME', 'Cửa hàng điện thoại và phụ kiện');

define('DEFAULT_PASSWORD', '52000148');
define('EMAIL_TOKEN_EXPIRE', 60);
define('SESSION_TIMEOUT', 7200);

define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

define('ITEMS_PER_PAGE', 20);
define('CURRENCY_SYMBOL', 'VND');

define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ensureDirectory(BASE_PATH . '/logs');
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');

define('STATUS_ACTIVE', 'active');
define('STATUS_LOCKED', 'locked');

function ensureDirectory($path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    return is_dir($path);
}

function formatMoney($amount) {
    return number_format((float) $amount, 0, ',', '.') . ' ' . CURRENCY_SYMBOL;
}

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }

    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'd/m/Y H:i:s') {
    if (empty($datetime)) {
        return '';
    }

    return date($format, strtotime($datetime));
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset($path) {
    $url = ASSETS_URL . '/' . ltrim($path, '/');
    $filePath = BASE_PATH . '/assets/' . ltrim($path, '/');
    if (is_file($filePath)) {
        $url .= '?v=' . filemtime($filePath);
    }
    return $url;
}

function cleanInput($data) {
    $data = trim((string) $data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function cleanText($data) {
    return trim((string) $data);
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlashMessage() {
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $message;
}

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function mustChangePassword() {
    return isLoggedIn() && !empty($_SESSION['must_change_password']);
}

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
        'avatar' => $_SESSION['avatar'] ?? 'avatar-default.png',
        'must_change_password' => $_SESSION['must_change_password'] ?? 0,
    ];
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? null) === ROLE_ADMIN;
}

function isStaff() {
    return isLoggedIn() && ($_SESSION['role'] ?? null) === ROLE_STAFF;
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect(url());
    }
}

function routeByRole($role = null) {
    $role = $role ?? ($_SESSION['role'] ?? null);

    if ($role === ROLE_ADMIN) {
        return url('modules/dashboard/admin_dashboard.php');
    }

    return url('modules/pos/pos.php');
}

function requireRole($roles) {
    requireLogin();

    $roles = (array) $roles;
    $role = $_SESSION['role'] ?? null;

    if (!in_array($role, $roles, true)) {
        setFlashMessage('error', 'Bạn không có quyền truy cập chức năng này.');
        redirect(routeByRole());
    }
}

function enforcePasswordChange($allowList = []) {
    if (!mustChangePassword()) {
        return;
    }

    $defaultAllowList = [
        '/modules/auth/change_password.php',
        '/modules/auth/process_change_password.php',
        '/logout.php',
    ];

    $currentScript = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    foreach (array_merge($defaultAllowList, $allowList) as $allowedPath) {
        if ($allowedPath !== '' && substr($currentScript, -strlen($allowedPath)) === $allowedPath) {
            return;
        }
    }

    setFlashMessage('warning', 'Bạn phải đổi mật khẩu trước khi sử dụng hệ thống.');
    redirect(url('modules/auth/change_password.php'));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function uploadFile($file, $destination = 'uploads') {
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    if (($file['size'] ?? 0) > MAX_FILE_SIZE) {
        return false;
    }

    $fileType = $file['type'] ?? '';
    if (!in_array($fileType, ALLOWED_IMAGE_TYPES, true)) {
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_IMAGE_EXTENSIONS, true)) {
        return false;
    }

    $directory = UPLOADS_PATH . '/' . trim($destination, '/');
    ensureDirectory($directory);

    $filename = uniqid('', true) . '_' . time() . '.' . $extension;
    $uploadPath = $directory . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    }

    return false;
}

function deleteFile($filename, $folder = 'uploads') {
    $filePath = UPLOADS_PATH . '/' . trim($folder, '/') . '/' . $filename;
    if (is_file($filePath)) {
        return unlink($filePath);
    }

    return false;
}

function getAvatarUrl($avatar) {
    if (empty($avatar) || $avatar === 'avatar-default.png') {
        return asset('images/avatar-default.svg');
    }

    return UPLOADS_URL . '/avatars/' . $avatar;
}

function getProductImageUrl($image) {
    if (empty($image) || $image === 'no-image.png') {
        return asset('images/no-image.svg');
    }

    return UPLOADS_URL . '/products/' . $image;
}

function normalizePhone($phone) {
    return preg_replace('/\s+/', '', trim((string) $phone));
}

function isPostRequest() {
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function isValidEmailAddress($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhoneNumber($phone) {
    return preg_match('/^[0-9\+\-\s\(\)]{8,15}$/', normalizePhone($phone)) === 1;
}

function usernameFromEmail($email) {
    $parts = explode('@', strtolower(trim($email)));
    return preg_replace('/[^a-z0-9._-]/', '', $parts[0] ?? '');
}

function getUserStatusMeta($user) {
    if (($user['status'] ?? '') === STATUS_LOCKED) {
        return ['key' => STATUS_LOCKED, 'label' => 'Bị khóa', 'class' => 'danger'];
    }

    if (!empty($user['must_change_password'])) {
        return ['key' => 'inactive', 'label' => 'Chưa kích hoạt', 'class' => 'warning'];
    }

    return ['key' => STATUS_ACTIVE, 'label' => 'Hoạt động', 'class' => 'success'];
}

function logEmailMessage($to, $subject, $htmlBody) {
    ensureDirectory(EMAIL_LOG_PATH);

    $filename = EMAIL_LOG_PATH . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]/i', '_', $to) . '.html';
    $content = '<h3>To: ' . e($to) . '</h3><h4>Subject: ' . e($subject) . '</h4><hr>' . $htmlBody;

    return file_put_contents($filename, $content) !== false ? $filename : false;
}

function jsonResponse($payload, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function isAjaxRequest() {
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

require_once __DIR__ . '/database.php';
