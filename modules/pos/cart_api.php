<?php
require_once '../../config/config.php';
require_once 'cart_helpers.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
}

if (!in_array($_SESSION['role'] ?? '', [ROLE_ADMIN, ROLE_STAFF], true)) {
    jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403);
}

if (mustChangePassword()) {
    jsonResponse(['success' => false, 'message' => 'Bạn phải đổi mật khẩu trước khi sử dụng chức năng này.'], 403);
}

function posCartResponseData() {
    $cart = posCalculateCartTotals();
    $items = [];
    foreach ($cart['items'] as $item) {
        $items[] = [
            'product_id' => $item['product_id'],
            'barcode' => $item['barcode'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['subtotal'],
        ];
    }

    return [
        'items' => $items,
        'total_amount' => $cart['total_amount'],
        'total_quantity' => $cart['total_quantity'],
    ];
}

$action = cleanText($_POST['action'] ?? 'get');

if ($action === 'add') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $barcode = cleanText($_POST['barcode'] ?? '');

    if ($productId > 0) {
        $product = fetchOne('SELECT id, barcode, name, price_sell, price_buy FROM products WHERE id = ? LIMIT 1', 'i', [$productId]);
    } elseif ($barcode !== '') {
        $product = fetchOne('SELECT id, barcode, name, price_sell, price_buy FROM products WHERE barcode = ? LIMIT 1', 's', [$barcode]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
    }

    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy sản phẩm.'], 404);
    }
    posAddToCart($product, 1);
}

if ($action === 'update') {
    posUpdateCartQuantity((int) ($_POST['product_id'] ?? 0), (int) ($_POST['quantity'] ?? 1));
}

if ($action === 'remove') {
    posRemoveFromCart((int) ($_POST['product_id'] ?? 0));
}

if ($action === 'clear') {
    posClearCart();
}

jsonResponse(['success' => true, 'cart' => posCartResponseData()]);
