<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/categories/list_categories.php'));
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlashMessage('error', 'Yêu cầu không hợp lệ.');
    redirect(url('modules/categories/list_categories.php'));
}

$productCount = fetchOne('SELECT COUNT(*) AS total FROM products WHERE category_id = ?', 'i', [$id]);
if ((int) ($productCount['total'] ?? 0) > 0) {
    setFlashMessage('error', 'Không thể xóa danh mục vì vẫn còn sản phẩm bên trong.');
    redirect(url('modules/categories/list_categories.php'));
}

$result = executeQuery('DELETE FROM categories WHERE id = ?', 'i', [$id]);
setFlashMessage($result ? 'success' : 'error', $result ? 'Đã xóa danh mục.' : 'Không thể xóa danh mục.');
redirect(url('modules/categories/list_categories.php'));
