<?php
/**
 * Fuzzy Search API - Tìm kiếm gợi ý thông minh
 * Tự viết từ đầu, không sử dụng framework hay template
 * 
 * Params: ?type=employees|products|customers&q=keyword
 * Returns: JSON array of matching results
 */
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

// Chỉ cho phép người dùng đã đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit;
}

$type = cleanText($_GET['type'] ?? '');
$query = cleanText($_GET['q'] ?? '');

// Từ khóa quá ngắn thì không tìm
if (mb_strlen($query) < 1) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$keyword = '%' . $query . '%';
$results = [];

switch ($type) {
    case 'employees':
        // Chỉ admin mới được tìm nhân viên
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $rows = fetchAll(
            "SELECT id, full_name, email, phone, username, avatar 
             FROM users 
             WHERE role = 'staff' 
               AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR username LIKE ?)
             ORDER BY full_name ASC 
             LIMIT 8",
            'ssss',
            [$keyword, $keyword, $keyword, $keyword]
        );
        foreach ($rows as $row) {
            $results[] = [
                'id'    => (int) $row['id'],
                'title' => $row['full_name'],
                'sub'   => $row['email'],
                'extra' => $row['phone'] ?: '',
                'icon'  => 'user',
            ];
        }
        break;

    case 'products':
        $rows = fetchAll(
            "SELECT p.id, p.name, p.barcode, p.price_sell, c.name AS category_name 
             FROM products p 
             JOIN categories c ON c.id = p.category_id 
             WHERE p.name LIKE ? OR p.barcode LIKE ?
             ORDER BY p.name ASC 
             LIMIT 8",
            'ss',
            [$keyword, $keyword]
        );
        foreach ($rows as $row) {
            $results[] = [
                'id'    => (int) $row['id'],
                'title' => $row['name'],
                'sub'   => $row['category_name'],
                'extra' => number_format((float) $row['price_sell'], 0, ',', '.') . ' VND',
                'icon'  => 'box',
            ];
        }
        break;

    case 'customers':
        $rows = fetchAll(
            "SELECT id, name, phone, address 
             FROM customers 
             WHERE name LIKE ? OR phone LIKE ?
             ORDER BY name ASC 
             LIMIT 8",
            'ss',
            [$keyword, $keyword]
        );
        foreach ($rows as $row) {
            $results[] = [
                'id'    => (int) $row['id'],
                'title' => $row['name'],
                'sub'   => $row['phone'] ?: '',
                'extra' => $row['address'] ?: '',
                'icon'  => 'user-friends',
            ];
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type'], JSON_UNESCAPED_UNICODE);
        exit;
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);
