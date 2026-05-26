<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$pageTitle = 'Danh sách đơn hàng';
$search = cleanText($_GET['search'] ?? '');
$whereParts = [];
$types = '';
$params = [];

if (isStaff()) {
    $whereParts[] = 'o.staff_id = ?';
    $types .= 'i';
    $params[] = (int) $_SESSION['user_id'];
}

if ($search !== '') {
    $whereParts[] = '(o.order_code LIKE ? OR c.name LIKE ? OR c.phone LIKE ?)';
    $keyword = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
}

$whereSql = $whereParts ? 'WHERE ' . implode(' AND ', $whereParts) : '';
$orders = fetchAll("SELECT o.*, c.name AS customer_name, c.phone, u.full_name AS staff_name FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = o.staff_id {$whereSql} ORDER BY o.created_at DESC", $types, $params);
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
            <div class="card-header flex-wrap gap-3">
                <h2 class="card-title"><i class="fas fa-receipt"></i> Danh sách đơn hàng</h2>
                <a href="<?php echo url('modules/pos/pos.php'); ?>" class="btn btn-success"><i class="fas fa-plus"></i> Tạo đơn mới</a>
            </div>
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-8">
                    <label class="form-label">Tìm theo mã đơn, tên khách, SDT</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
                    <a href="<?php echo url('modules/orders/list_orders.php'); ?>" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <?php if (isAdmin()): ?><th>Nhân viên</th><?php endif; ?>
                            <th>Tổng tiền</th>
                            <th>Tiền khách đưa</th>
                            <th>Tiền thừa</th>
                            <th>Ngày tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$orders): ?>
                            <tr><td colspan="<?php echo isAdmin() ? '8' : '7'; ?>" class="text-center py-5 text-muted">Chưa có đơn hàng nào.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo e($order['order_code']); ?></strong></td>
                                <td><?php echo e($order['customer_name']); ?><br><small class="text-muted"><?php echo e($order['phone']); ?></small></td>
                                <?php if (isAdmin()): ?><td><?php echo e($order['staff_name']); ?></td><?php endif; ?>
                                <td><?php echo e(formatMoney($order['total_amount'])); ?></td>
                                <td><?php echo e(formatMoney($order['customer_paid'])); ?></td>
                                <td><?php echo e(formatMoney($order['change_amount'])); ?></td>
                                <td><?php echo e(formatDateTime($order['created_at'])); ?></td>
                                <td><a href="<?php echo url('modules/orders/view_order.php?id=' . (int) $order['id']); ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Xem</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
