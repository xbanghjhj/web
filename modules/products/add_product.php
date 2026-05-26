<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$pageTitle = 'Thêm sản phẩm';
$productId = (int) ($_GET['id'] ?? 0);
$product = null;
if ($productId > 0) {
    $product = fetchOne('SELECT * FROM products WHERE id = ? LIMIT 1', 'i', [$productId]);
    if (!$product) {
        setFlashMessage('error', 'Không tìm thấy sản phẩm.');
        redirect(url('modules/products/list_products.php'));
    }
    $pageTitle = 'Cập nhật sản phẩm';
}

$categories = fetchAll('SELECT id, name FROM categories ORDER BY name ASC');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - POS System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <div class="main-content">
        <?php $flashMessage = getFlashMessage(); ?>
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo e($flashMessage['type']); ?>">
                <i class="fas fa-info-circle"></i>
                <span><?php echo e($flashMessage['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-box"></i> <?php echo e($pageTitle); ?></h2>
                <a href="<?php echo url('modules/products/list_products.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <?php if (!$categories): ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Bạn cần tạo danh mục trước khi thêm sản phẩm.</span>
                </div>
            <?php else: ?>
                <form action="process_save_product.php" method="POST" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo (int) ($product['id'] ?? 0); ?>">
                    <div class="col-md-6">
                        <label class="form-label">Mã vạch (Barcode)</label>
                        <input type="text" name="barcode" class="form-control" value="<?php echo e($product['barcode'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($product['name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Giá nhập</label>
                        <input type="number" name="price_buy" class="form-control" min="0" step="1000" value="<?php echo e($product['price_buy'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Giá bán</label>
                        <input type="number" name="price_sell" class="form-control" min="0" step="1000" value="<?php echo e($product['price_sell'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Chọn danh mục</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int) $category['id']; ?>" <?php echo (int) ($product['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : ''; ?>><?php echo e($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo e($product['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                        <?php if (!empty($product['image']) && $product['image'] !== 'no-image.png'): ?>
                            <div class="form-text">Ảnh hiện tại: <?php echo e($product['image']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu sản phẩm</button>
                        <a href="<?php echo url('modules/products/list_products.php'); ?>" class="btn btn-outline-secondary">Hủy</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
