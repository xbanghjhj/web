<?php
/**
 * ===================================================
 * DATABASE CONNECTION - Kết nối Database
 * ===================================================
 * File: config/database.php
 * Mục đích: Thiết lập kết nối đến MySQL database
 * ===================================================
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Thay đổi nếu cần
define('DB_PASS', '');              // Thay đổi nếu cần
define('DB_NAME', 'pos_system');

// Character set
define('DB_CHARSET', 'utf8mb4');

/**
 * Tạo kết nối DATABASE
 * @return mysqli|false Connection object hoặc false nếu lỗi
 */
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        // Tắt hiển thị lỗi mặc định
        mysqli_report(MYSQLI_REPORT_OFF);
        
        // Tạo kết nối
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Kiểm tra lỗi kết nối
        if ($conn->connect_error) {
            error_log("Database Connection Error: " . $conn->connect_error);
            die("Lỗi kết nối database. Vui lòng liên hệ quản trị viên.");
        }
        
        // Set charset
        if (!$conn->set_charset(DB_CHARSET)) {
            error_log("Error loading character set " . DB_CHARSET . ": " . $conn->error);
        }
        
        // Set timezone
        $conn->query("SET time_zone = '+07:00'");
    }
    
    return $conn;
}

/**
 * Đóng kết nối database
 */
function closeDbConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
        $conn = null;
    }
}

/**
 * Thực thi prepared statement an toàn
 * @param string $query SQL query với placeholders (?)
 * @param string $types Kiểu dữ liệu (i = integer, d = double, s = string, b = blob)
 * @param array $params Mảng tham số
 * @return mysqli_stmt|false
 */
function executeQuery($query, $types = '', $params = []) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    // Bind parameters nếu có
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Thực thi
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

/**
 * Lấy một dòng kết quả
 * @param string $query
 * @param string $types
 * @param array $params
 * @return array|null
 */
function fetchOne($query, $types = '', $params = []) {
    $stmt = executeQuery($query, $types, $params);
    
    if (!$stmt) {
        return null;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

/**
 * Lấy tất cả kết quả
 * @param string $query
 * @param string $types
 * @param array $params
 * @return array
 */
function fetchAll($query, $types = '', $params = []) {
    $stmt = executeQuery($query, $types, $params);
    
    if (!$stmt) {
        return [];
    }
    
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $rows;
}

/**
 * Escape string để tránh SQL injection
 * @param string $string
 * @return string
 */
function escapeString($string) {
    $conn = getDbConnection();
    return $conn->real_escape_string($string);
}

// Khởi tạo kết nối
$conn = getDbConnection();

?>