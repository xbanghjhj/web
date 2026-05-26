<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/products/list_products.php'));
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlashMessage('error', 'Yêu cầu không hợp lệ.');
    redirect(url('modules/products/list_products.php'));
}

$product = fetchOne('SELECT * FROM products WHERE id = ? LIMIT 1', 'i', [$id]);
if (!$product) {
    setFlashMessage('error', 'Không tìm thấy sản phẩm.');
    redirect(url('modules/products/list_products.php'));
}

$orderUsage = fetchOne('SELECT COUNT(*) AS total FROM order_details WHERE product_id = ?', 'i', [$id]);
if ((int) ($orderUsage['total'] ?? 0) > 0 || !empty($product['has_sold'])) {
    setFlashMessage('error', 'Không thể xóa sản phẩm đã phát sinh trong đơn hàng.');
    redirect(url('modules/products/list_products.php'));
}

$result = executeQuery('DELETE FROM products WHERE id = ?', 'i', [$id]);
if ($result && !empty($product['image']) && $product['image'] !== 'no-image.png') {
    deleteFile($product['image'], 'products');
}

setFlashMessage($result ? 'success' : 'error', $result ? 'Đã xóa sản phẩm.' : 'Không thể xóa sản phẩm.');
redirect(url('modules/products/list_products.php'));
