<?php
require_once '../../config/config.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
}

if (!in_array($_SESSION['role'] ?? '', [ROLE_ADMIN, ROLE_STAFF], true)) {
    jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403);
}

if (mustChangePassword()) {
    jsonResponse(['success' => false, 'message' => 'Bạn phải đổi mật khẩu trước khi sử dụng chức năng này.'], 403);
}

$keyword = cleanText($_GET['q'] ?? '');

if ($keyword === '') {
    // Không có từ khóa -> trả về tất cả sản phẩm (giới hạn 50)
    $products = fetchAll(
        'SELECT p.id, p.barcode, p.name, p.price_sell, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.name ASC LIMIT 50'
    );
    jsonResponse(['success' => true, 'products' => $products]);
}

$products = fetchAll(
    'SELECT p.id, p.barcode, p.name, p.price_sell, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.name LIKE ? OR p.barcode LIKE ? ORDER BY p.name ASC LIMIT 50',
    'ss',
    ['%' . $keyword . '%', '%' . $keyword . '%']
);

jsonResponse(['success' => true, 'products' => $products]);
