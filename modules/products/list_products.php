<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

$pageTitle  = 'Quản lý sản phẩm';
$search     = cleanText($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$isAdminView = isAdmin();

$categories = fetchAll('SELECT id, name FROM categories ORDER BY name ASC');

$whereParts = [];
$types      = '';
$params     = [];
if ($search !== '') {
    $whereParts[] = '(p.name LIKE ? OR p.barcode LIKE ?)';
    $keyword = '%' . $search . '%';
    $types  .= 'ss';
    $params[] = $keyword;
    $params[] = $keyword;
}
if ($categoryId > 0) {
    $whereParts[] = 'p.category_id = ?';
    $types .= 'i';
    $params[] = $categoryId;
}

$whereSql = $whereParts ? 'WHERE ' . implode(' AND ', $whereParts) : '';
$products = fetchAll(
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     {$whereSql}
     ORDER BY p.created_at DESC",
    $types,
    $params
);

// Helper: resolve product image
function productImgUrl($product) {
    if (!empty($product['image']) && $product['image'] !== 'no-image.png') {
        return url('assets/uploads/products/' . $product['image']);
    }
    // Fallback: keyword match from assets/images
    $name = mb_strtolower($product['name'] ?? '', 'UTF-8');
    $imageMap = [
        'iphone'        => 'iphone-15-pro-max.jpg',
        'samsung'       => 'samsung-s24-ultra.jpg',
        'xiaomi'        => 'xiaomi-14-ultra.jpg',
        'ipad'          => 'ipad-pro.jpg',
        'galaxy tab'    => 'galaxy-tab-s9.jpg',
        'airpod'        => 'airpods-pro.jpg',
        'ốp lưng'       => 'phone-case.jpg',
        'kính cường'    => 'screen-protector.jpg',
        'màn hình'      => 'screen-protector.jpg',
    ];
    foreach ($imageMap as $key => $file) {
        if (mb_strpos($name, $key) !== false) {
            return url('assets/images/' . $file);
        }
    }
    return url('assets/images/no-image.svg');
}

// Helper: is newly added (within 30 days)
function isNewProduct($product) {
    return strtotime($product['created_at']) >= strtotime('-30 days');
}
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
    <link rel="stylesheet" href="<?php echo asset('css/categories-products.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/fuzzy-search.css'); ?>">
</head>
<body class="cp-page">
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>

    <div class="main-content">

        <?php $flashMessage = getFlashMessage(); ?>
        <?php if ($flashMessage): ?>
            <div class="cp-alert cp-alert-<?php echo e($flashMessage['type']); ?>">
                <i class="fas fa-info-circle"></i>
                <?php echo e($flashMessage['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Page Bar -->
        <div class="cp-page-bar">
            <h2><i class="fas fa-box-open"></i> Danh sách sản phẩm</h2>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- View toggle -->
                <div class="view-toggle">
                    <button class="view-toggle-btn active" id="btnGrid"
                            onclick="switchView('grid')">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="view-toggle-btn" id="btnTable"
                            onclick="switchView('table')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>

                <?php if ($isAdminView): ?>
                <a href="<?php echo url('modules/products/add_product.php'); ?>"
                   class="cp-btn cp-btn-success">
                    <i class="fas fa-plus-circle"></i> Thêm sản phẩm
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="cp-toolbar">
            <form method="GET"
                  class="d-flex align-items-center gap-3 flex-wrap w-100"
                  id="filterForm">
                <div class="cp-search-wrap" style="flex:1; min-width:220px;">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search"
                           value="<?php echo e($search); ?>"
                           placeholder="Tìm theo tên hoặc barcode..."
                           data-fuzzy-search="products"
                           autocomplete="off">
                </div>
                <div class="cp-select-wrap" style="min-width:180px;">
                    <select name="category_id">
                        <option value="0">Tất cả danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int) $cat['id']; ?>"
                                <?php echo $categoryId === (int) $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="cp-btn cp-btn-primary">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <?php if ($search || $categoryId > 0): ?>
                <a href="<?php echo url('modules/products/list_products.php'); ?>"
                   class="cp-btn cp-btn-outline">
                    <i class="fas fa-undo"></i> Đặt lại
                </a>
                <?php endif; ?>
            </form>
            <div style="color:#aaa; font-size:13px; white-space:nowrap;">
                <?php echo count($products); ?> sản phẩm
            </div>
        </div>

        <!-- Promo Banners -->
        <div class="promo-banners">
            <div class="promo-banner promo-banner-1">
                <h3>Ưu đãi đặc biệt</h3>
                <p>Giảm giá đến 40% cho các sản phẩm điện thoại mới nhất</p>
                <a href="<?php echo url('modules/products/list_products.php?search=điện+thoại'); ?>"
                   class="promo-btn">
                    <i class="fas fa-bolt"></i> Xem ngay
                </a>
                <img src="<?php echo url('assets/images/iphone-15-pro-max.jpg'); ?>"
                     alt="" class="promo-banner-img">
            </div>
            <div class="promo-banner promo-banner-2">
                <h3>Sản phẩm nổi bật</h3>
                <p>Bộ sưu tập máy tính bảng cao cấp, hiệu suất vượt trội</p>
                <a href="<?php echo url('modules/products/list_products.php?search=ipad'); ?>"
                   class="promo-btn">
                    <i class="fas fa-star"></i> Khám phá
                </a>
                <img src="<?php echo url('assets/images/ipad-pro.jpg'); ?>"
                     alt="" class="promo-banner-img">
            </div>
        </div>

        <!-- ── GRID VIEW ── -->
        <div id="viewGrid">
            <?php if ($products): ?>
                <div class="cp-section-label">
                    <i class="fas fa-th-large" style="color:#7395AE"></i> Lưới sản phẩm
                </div>
                <div class="prod-grid">
                    <?php foreach ($products as $product):
                        $canDelete = empty($product['has_sold']);
                        $imgSrc    = productImgUrl($product);
                        $isNew     = isNewProduct($product);
                    ?>
                    <div class="prod-card">
                        <!-- Image -->
                        <div class="prod-card-img">
                            <img src="<?php echo e($imgSrc); ?>"
                                 alt="<?php echo e($product['name']); ?>"
                                 onerror="this.src='<?php echo url('assets/images/no-image.svg'); ?>'">
                            <?php if ($isNew): ?>
                                <span class="prod-badge-new">Mới</span>
                            <?php endif; ?>
                            <?php if (!$canDelete): ?>
                                <span class="prod-badge-sold">Đã bán</span>
                            <?php endif; ?>

                            <!-- Admin hover overlay -->
                            <?php if ($isAdminView): ?>
                            <div class="prod-card-overlay">
                                <a href="<?php echo url('modules/products/add_product.php?id=' . (int) $product['id']); ?>"
                                   class="prod-overlay-btn" title="Chỉnh sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="delete_product.php" method="POST"
                                      onsubmit="return confirm('Xoá sản phẩm này?');"
                                      class="d-inline">
                                    <input type="hidden" name="id"
                                           value="<?php echo (int) $product['id']; ?>">
                                    <button type="submit"
                                            class="prod-overlay-btn danger"
                                            title="Xóa"
                                            <?php echo $canDelete ? '' : 'disabled style="opacity:.5"'; ?>>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Body -->
                        <div class="prod-card-body">
                            <div class="prod-card-cat">
                                <i class="fas fa-tag"></i>
                                <?php echo e($product['category_name']); ?>
                            </div>
                            <p class="prod-card-name"><?php echo e($product['name']); ?></p>
                            <div class="prod-card-barcode">
                                <i class="fas fa-barcode"></i>
                                <?php echo e($product['barcode']); ?>
                            </div>
                            <div class="prod-card-price">
                                <span class="prod-price-sell">
                                    <?php echo e(formatMoney($product['price_sell'])); ?>
                                </span>
                                <?php if ($isAdminView): ?>
                                <span class="prod-price-buy">
                                    NK: <?php echo e(formatMoney($product['price_buy'])); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="cp-empty">
                    <i class="fas fa-box-open"></i>
                    <p>Không tìm thấy sản phẩm nào.
                        <?php if ($search || $categoryId > 0): ?>
                            <a href="<?php echo url('modules/products/list_products.php'); ?>">
                                Xóa bộ lọc
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div><!-- /#viewGrid -->

        <!-- ── TABLE VIEW ── -->
        <div id="viewTable" style="display:none;">
            <div class="cp-section-label">
                <i class="fas fa-list" style="color:#7395AE"></i> Bảng sản phẩm
            </div>
            <div class="prod-table-wrap">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Ảnh</th>
                                <th>Sản phẩm</th>
                                <th>Barcode</th>
                                <th>Danh mục</th>
                                <?php if ($isAdminView): ?><th>Giá nhập</th><?php endif; ?>
                                <th>Giá bán</th>
                                <th>Trạng thái</th>
                                <?php if ($isAdminView): ?><th class="text-end">Thao tác</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$products): ?>
                                <tr>
                                    <td colspan="<?php echo $isAdminView ? '8' : '6'; ?>"
                                        class="text-center py-5 text-muted">
                                        Chưa có sản phẩm nào.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($products as $product):
                                $canDelete = empty($product['has_sold']);
                                $imgSrc    = productImgUrl($product);
                            ?>
                            <tr>
                                <td>
                                    <img src="<?php echo e($imgSrc); ?>"
                                         alt="<?php echo e($product['name']); ?>"
                                         class="prod-table-img"
                                         onerror="this.src='<?php echo url('assets/images/no-image.svg'); ?>'">
                                </td>
                                <td>
                                    <strong><?php echo e($product['name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo e($product['description'] ?: 'Không có mô tả'); ?>
                                    </small>
                                </td>
                                <td><code><?php echo e($product['barcode']); ?></code></td>
                                <td>
                                    <span class="cp-badge cp-badge-purple">
                                        <?php echo e($product['category_name']); ?>
                                    </span>
                                </td>
                                <?php if ($isAdminView): ?>
                                <td><?php echo e(formatMoney($product['price_buy'])); ?></td>
                                <?php endif; ?>
                                <td>
                                    <strong style="color:#7395AE;">
                                        <?php echo e(formatMoney($product['price_sell'])); ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="cp-badge <?php echo $canDelete ? 'cp-badge-green' : 'cp-badge-orange'; ?>">
                                        <?php echo $canDelete ? 'Có thể xoá' : 'Đã phát sinh đơn'; ?>
                                    </span>
                                </td>
                                <?php if ($isAdminView): ?>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo url('modules/products/add_product.php?id=' . (int) $product['id']); ?>"
                                           class="cp-btn cp-btn-primary cp-btn-icon cp-btn-sm">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="delete_product.php" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Xoá sản phẩm này?');">
                                            <input type="hidden" name="id"
                                                   value="<?php echo (int) $product['id']; ?>">
                                            <button type="submit"
                                                    class="cp-btn cp-btn-danger cp-btn-icon cp-btn-sm"
                                                    <?php echo $canDelete ? '' : 'disabled'; ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /#viewTable -->

    </div><!-- .main-content -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo asset('js/fuzzy-search.js'); ?>"></script>
    <script>
        function switchView(mode) {
            if (mode === 'grid') {
                $('#viewGrid').show();
                $('#viewTable').hide();
                $('#btnGrid').addClass('active');
                $('#btnTable').removeClass('active');
                localStorage.setItem('prodView', 'grid');
            } else {
                $('#viewGrid').hide();
                $('#viewTable').show();
                $('#btnTable').addClass('active');
                $('#btnGrid').removeClass('active');
                localStorage.setItem('prodView', 'table');
            }
        }

        // Restore last view preference
        $(document).ready(function () {
            const saved = localStorage.getItem('prodView');
            if (saved === 'table') switchView('table');

            // Sidebar active
            $('.menu-item').each(function () {
                if ($(this).attr('href') &&
                    window.location.href.indexOf($(this).attr('href')) !== -1) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>
