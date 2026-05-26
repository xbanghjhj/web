<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/categories/list_categories.php'));
}

$id = (int) ($_POST['id'] ?? 0);
$name = cleanText($_POST['name'] ?? '');
$description = cleanText($_POST['description'] ?? '');
$createdBy = (int) $_SESSION['user_id'];

if ($name === '') {
    setFlashMessage('error', 'Tên danh mục không được để trống.');
    redirect(url('modules/categories/list_categories.php' . ($id ? '?id=' . $id : '')));
}

$duplicateQuery = 'SELECT id FROM categories WHERE name = ?' . ($id ? ' AND id <> ?' : '') . ' LIMIT 1';
$duplicate = $id
    ? fetchOne($duplicateQuery, 'si', [$name, $id])
    : fetchOne($duplicateQuery, 's', [$name]);

if ($duplicate) {
    setFlashMessage('error', 'Tên danh mục đã tồn tại.');
    redirect(url('modules/categories/list_categories.php' . ($id ? '?id=' . $id : '')));
}

if ($id > 0) {
    $result = executeQuery('UPDATE categories SET name = ?, description = ? WHERE id = ?', 'ssi', [$name, $description, $id]);
    setFlashMessage($result ? 'success' : 'error', $result ? 'Đã cập nhật danh mục.' : 'Không thể cập nhật danh mục.');
} else {
    $result = executeQuery('INSERT INTO categories (name, description, created_by) VALUES (?, ?, ?)', 'ssi', [$name, $description, $createdBy]);
    setFlashMessage($result ? 'success' : 'error', $result ? 'Đã thêm danh mục mới.' : 'Không thể thêm danh mục.');
}

redirect(url('modules/categories/list_categories.php'));
