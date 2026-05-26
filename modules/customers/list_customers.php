<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$pageTitle = 'Khách hàng';
$search = cleanText($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;
$where = '';
$types = '';
$params = [];

if ($search !== '') {
    $where = 'WHERE name LIKE ? OR phone LIKE ?';
    $keyword = '%' . $search . '%';
    $types = 'ss';
    $params = [$keyword, $keyword];
}

$totalRows = fetchOne("SELECT COUNT(*) AS total FROM customers {$where}", $types, $params);
$total = (int) ($totalRows['total'] ?? 0);
$totalPages = max(1, (int) ceil($total / ITEMS_PER_PAGE));
$customers = fetchAll("SELECT * FROM customers {$where} ORDER BY updated_at DESC LIMIT {$offset}, " . ITEMS_PER_PAGE, $types, $params);
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
    <link rel="stylesheet" href="<?php echo asset('css/fuzzy-search.css'); ?>">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <div class="main-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-friends"></i> Danh sách khách hàng</h2>
            </div>
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-8">
                    <label class="form-label">Tìm theo tên hoặc số điện thoại</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="Nhập tên hoặc số điện thoại..." data-fuzzy-search="customers" autocomplete="off">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
                    <a href="<?php echo url('modules/customers/list_customers.php'); ?>" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Khách hàng</th>
                            <th>Địa chỉ</th>
                            <th>Tổng đơn</th>
                            <th>Tổng chi tiêu</th>
                            <th>Cập nhật gần nhất</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$customers): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Chưa có khách hàng nào.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><strong><?php echo e($customer['name']); ?></strong><br><small class="text-muted"><?php echo e($customer['phone']); ?></small></td>
                                <td><?php echo e($customer['address'] ?: '-'); ?></td>
                                <td><?php echo e((int) $customer['total_orders']); ?></td>
                                <td><?php echo e(formatMoney($customer['total_spent'])); ?></td>
                                <td><?php echo e(formatDateTime($customer['updated_at'])); ?></td>
                                <td><a href="<?php echo url('modules/customers/view_customer.php?id=' . (int) $customer['id']); ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Xem</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4"><ul class="pagination"><?php for ($i = 1; $i <= $totalPages; $i++): ?><li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav>
            <?php endif; ?>
        </div>
    </div>
    <script src="<?php echo asset('js/fuzzy-search.js'); ?>"></script>
</body>
</html>
