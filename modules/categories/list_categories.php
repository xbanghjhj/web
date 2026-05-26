<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$pageTitle = 'Quản lý danh mục';
$editingId = (int) ($_GET['id'] ?? 0);
$search    = cleanText($_GET['search'] ?? '');

$editCategory = null;
if ($editingId > 0) {
    $editCategory = fetchOne('SELECT * FROM categories WHERE id = ? LIMIT 1', 'i', [$editingId]);
}

$where  = '';
$types  = '';
$params = [];
if ($search !== '') {
    $where   = 'WHERE name LIKE ? OR description LIKE ?';
    $keyword = '%' . $search . '%';
    $types   = 'ss';
    $params  = [$keyword, $keyword];
}

$categories = fetchAll(
    "SELECT c.*, u.full_name AS creator_name,
            (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
     FROM categories c
     LEFT JOIN users u ON u.id = c.created_by
     {$where}
     ORDER BY c.created_at DESC",
    $types,
    $params
);

// Category icon mapping
$catIcons = [
    'điện thoại'    => 'fa-mobile-alt',
    'laptop'        => 'fa-laptop',
    'máy tính'      => 'fa-desktop',
    'âm thanh'      => 'fa-headphones',
    'tai nghe'      => 'fa-headphones',
    'tablet'        => 'fa-tablet-alt',
    'máy tính bảng' => 'fa-tablet-alt',
    'phụ kiện'      => 'fa-plug',
    'ốp lưng'       => 'fa-mobile',
    'màn hình'      => 'fa-tv',
    'camera'        => 'fa-camera',
    'smartwatch'    => 'fa-clock',
    'đồng hồ'       => 'fa-clock',
    'default'       => 'fa-tags',
];

function getCatIcon($name, $map) {
    $nameLower = mb_strtolower($name, 'UTF-8');
    foreach ($map as $key => $icon) {
        if ($key !== 'default' && mb_strpos($nameLower, $key) !== false) {
            return $icon;
        }
    }
    return $map['default'];
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
            <h2><i class="fas fa-tags"></i> Quản lý danh mục</h2>
            <button class="cp-btn cp-btn-success" id="toggleFormBtn"
                    onclick="toggleForm()">
                <i class="fas fa-plus"></i>
                <?php echo $editCategory ? 'Đang chỉnh sửa' : 'Thêm danh mục'; ?>
            </button>
        </div>

        <!-- Add / Edit Form Panel -->
        <div class="cat-form-panel" id="catFormPanel"
             style="<?php echo (!$editCategory && !isset($_GET['add'])) ? 'display:none;' : ''; ?>">
            <h3>
                <i class="fas <?php echo $editCategory ? 'fa-pen' : 'fa-plus-circle'; ?>"></i>
                <?php echo $editCategory ? 'Cập nhật danh mục' : 'Thêm danh mục mới'; ?>
            </h3>
            <form action="process_save_category.php" method="POST">
                <input type="hidden" name="id"
                       value="<?php echo (int) ($editCategory['id'] ?? 0); ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="cp-form-group">
                            <label>Tên danh mục</label>
                            <input type="text" name="name"
                                   value="<?php echo e($editCategory['name'] ?? ''); ?>"
                                   placeholder="Nhập tên danh mục..." required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="cp-form-group">
                            <label>Mô tả</label>
                            <input type="text" name="description"
                                   value="<?php echo e($editCategory['description'] ?? ''); ?>"
                                   placeholder="Mô tả ngắn gọn...">
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-2 flex-wrap">
                        <button type="submit" class="cp-btn cp-btn-primary">
                            <i class="fas fa-save"></i> Lưu danh mục
                        </button>
                        <?php if ($editCategory): ?>
                            <a href="<?php echo url('modules/categories/list_categories.php'); ?>"
                               class="cp-btn cp-btn-outline">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        <?php else: ?>
                            <button type="button" class="cp-btn cp-btn-outline"
                                    onclick="toggleForm()">
                                <i class="fas fa-times"></i> Đóng
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Category Icon Row -->
        <?php if ($categories): ?>
        <div class="cp-section-label">
            <i class="fas fa-grip-horizontal" style="color:#7395AE"></i> Tổng quan danh mục
        </div>
        <div class="cat-icon-row">
            <?php foreach ($categories as $cat): ?>
            <a href="?search=<?php echo urlencode($cat['name']); ?>"
               class="cat-icon-item text-decoration-none
                      <?php echo (trim($search) === trim($cat['name'])) ? 'active' : ''; ?>">
                <div class="cat-circle">
                    <i class="fas <?php echo getCatIcon($cat['name'], $catIcons); ?>"></i>
                </div>
                <span class="cat-name"><?php echo e($cat['name']); ?></span>
                <span class="cat-count"><?php echo (int) $cat['product_count']; ?> SP</span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Search Toolbar -->
        <div class="cp-toolbar">
            <form method="GET" class="d-flex align-items-center gap-3 flex-wrap w-100">
                <div class="cp-search-wrap" style="flex:1; min-width:220px;">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search"
                           value="<?php echo e($search); ?>"
                           placeholder="Tìm theo tên hoặc mô tả...">
                </div>
                <button type="submit" class="cp-btn cp-btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if ($search): ?>
                <a href="<?php echo url('modules/categories/list_categories.php'); ?>"
                   class="cp-btn cp-btn-outline">
                    <i class="fas fa-undo"></i> Đặt lại
                </a>
                <?php endif; ?>
            </form>
            <div style="color:#aaa; font-size:13px; white-space:nowrap;">
                <?php echo count($categories); ?> danh mục
            </div>
        </div>

        <!-- Category Grid -->
        <?php if ($categories): ?>
        <div class="cp-section-label">
            <i class="fas fa-list" style="color:#7395AE"></i> Danh sách danh mục
        </div>
        <div class="cat-grid">
            <?php foreach ($categories as $category): ?>
            <div class="cat-card">
                <div class="cat-card-top">
                    <div class="cat-card-icon">
                        <i class="fas <?php echo getCatIcon($category['name'], $catIcons); ?>"></i>
                    </div>
                    <div class="cat-card-info">
                        <p class="cat-card-name"><?php echo e($category['name']); ?></p>
                        <p class="cat-card-desc">
                            <?php echo e($category['description'] ?: 'Chưa có mô tả.'); ?>
                        </p>
                    </div>
                </div>

                <div class="cat-card-meta">
                    <span class="cp-badge cp-badge-purple">
                        <i class="fas fa-box"></i>
                        <?php echo (int) $category['product_count']; ?> sản phẩm
                    </span>
                    <span class="cp-badge cp-badge-blue">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('d/m/Y', strtotime($category['created_at'])); ?>
                    </span>
                </div>

                <div class="cat-card-footer">
                    <span class="cat-card-creator">
                        <i class="fas fa-user-circle" style="color:#ccc"></i>
                        <?php echo e($category['creator_name'] ?: 'Admin'); ?>
                    </span>
                    <div class="cat-card-actions">
                        <a href="?id=<?php echo (int) $category['id']; ?>"
                           class="cp-btn cp-btn-primary cp-btn-icon cp-btn-sm"
                           title="Chỉnh sửa">
                            <i class="fas fa-pen"></i>
                        </a>
                        <form action="delete_category.php" method="POST" class="d-inline"
                              onsubmit="return confirm('Xóa danh mục này?');">
                            <input type="hidden" name="id"
                                   value="<?php echo (int) $category['id']; ?>">
                            <button type="submit"
                                    class="cp-btn cp-btn-danger cp-btn-icon cp-btn-sm"
                                    title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="cp-empty">
            <i class="fas fa-tags"></i>
            <p>Chưa có danh mục nào.
               <?php if ($search): ?>
                   Thử <a href="<?php echo url('modules/categories/list_categories.php'); ?>">xóa bộ lọc</a>.
               <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

    </div><!-- .main-content -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleForm() {
            const panel = document.getElementById('catFormPanel');
            const btn   = document.getElementById('toggleFormBtn');
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
                btn.innerHTML = '<i class="fas fa-times"></i> Đóng form';
            } else {
                panel.style.display = 'none';
                btn.innerHTML = '<i class="fas fa-plus"></i> Thêm danh mục';
            }
        }

        // Auto-open form if editing
        <?php if ($editCategory): ?>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('catFormPanel').style.display = 'block';
            document.getElementById('catFormPanel').scrollIntoView({ behavior: 'smooth' });
        });
        <?php endif; ?>

        // Sidebar active menu highlight
        $(document).ready(function () {
            $('.menu-item').each(function () {
                if ($(this).attr('href') && window.location.href.indexOf($(this).attr('href')) !== -1) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html>
