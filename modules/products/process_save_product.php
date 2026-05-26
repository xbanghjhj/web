<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/products/list_products.php'));
}

$id = (int) ($_POST['id'] ?? 0);
$barcode = cleanText($_POST['barcode'] ?? '');
$name = cleanText($_POST['name'] ?? '');
$priceBuy = (float) ($_POST['price_buy'] ?? 0);
$priceSell = (float) ($_POST['price_sell'] ?? 0);
$categoryId = (int) ($_POST['category_id'] ?? 0);
$description = cleanText($_POST['description'] ?? '');
$createdBy = (int) $_SESSION['user_id'];

if ($barcode === '' || $name === '' || $categoryId <= 0) {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin sản phẩm.');
    redirect(url('modules/products/add_product.php' . ($id ? '?id=' . $id : '')));
}

if ($priceSell < $priceBuy) {
    setFlashMessage('error', 'Giá bán không được thấp hơn giá nhập.');
    redirect(url('modules/products/add_product.php' . ($id ? '?id=' . $id : '')));
}

$barcodeDuplicateQuery = 'SELECT id FROM products WHERE barcode = ?' . ($id ? ' AND id <> ?' : '') . ' LIMIT 1';
$barcodeDuplicate = $id
    ? fetchOne($barcodeDuplicateQuery, 'si', [$barcode, $id])
    : fetchOne($barcodeDuplicateQuery, 's', [$barcode]);
if ($barcodeDuplicate) {
    setFlashMessage('error', 'Barcode đã tồn tại.');
    redirect(url('modules/products/add_product.php' . ($id ? '?id=' . $id : '')));
}

$currentProduct = null;
$imageName = 'no-image.png';
if ($id > 0) {
    $currentProduct = fetchOne('SELECT * FROM products WHERE id = ? LIMIT 1', 'i', [$id]);
    if (!$currentProduct) {
        setFlashMessage('error', 'Không tìm thấy sản phẩm.');
        redirect(url('modules/products/list_products.php'));
    }
    $imageName = $currentProduct['image'];
}

if (!empty($_FILES['image']['name'])) {
    $uploadedImage = uploadFile($_FILES['image'], 'products');
    if (!$uploadedImage) {
        setFlashMessage('error', 'Upload ảnh sản phẩm thất bại.');
        redirect(url('modules/products/add_product.php' . ($id ? '?id=' . $id : '')));
    }

    if ($id > 0 && !empty($imageName) && $imageName !== 'no-image.png') {
        deleteFile($imageName, 'products');
    }

    $imageName = $uploadedImage;
}

if ($id > 0) {
    $result = executeQuery(
        'UPDATE products SET barcode = ?, name = ?, price_buy = ?, price_sell = ?, category_id = ?, image = ?, description = ? WHERE id = ?',
        'ssddissi',
        [$barcode, $name, $priceBuy, $priceSell, $categoryId, $imageName, $description, $id]
    );
    setFlashMessage($result ? 'success' : 'error', $result ? 'Đã cập nhật sản phẩm.' : 'Không thể cập nhật sản phẩm.');
} else {
    $result = executeQuery(
        'INSERT INTO products (barcode, name, price_buy, price_sell, category_id, image, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        'ssddissi',
        [$barcode, $name, $priceBuy, $priceSell, $categoryId, $imageName, $description, $createdBy]
    );
    setFlashMessage($result ? 'success' : 'error', $result ? 'Đã thêm sản phẩm mới.' : 'Không thể thêm sản phẩm.');
}

redirect(url('modules/products/list_products.php'));
