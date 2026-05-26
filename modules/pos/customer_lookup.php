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

$phone = normalizePhone($_GET['phone'] ?? '');
if ($phone === '') {
    jsonResponse(['success' => false, 'message' => 'Vui lòng nhập số điện thoại.'], 400);
}

$customer = fetchOne('SELECT * FROM customers WHERE phone = ? LIMIT 1', 's', [$phone]);
if (!$customer) {
    jsonResponse([
        'success' => true,
        'customer' => [
            'exists' => false,
            'phone' => $phone,
            'name' => '',
            'address' => '',
            'total_orders' => 0,
            'total_spent' => 0,
        ],
    ]);
}

jsonResponse([
    'success' => true,
    'customer' => [
        'exists' => true,
        'id' => (int) $customer['id'],
        'phone' => $customer['phone'],
        'name' => $customer['name'],
        'address' => $customer['address'],
        'total_orders' => (int) $customer['total_orders'],
        'total_spent' => (float) $customer['total_spent'],
    ],
]);
