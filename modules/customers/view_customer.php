<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$customerId = (int) ($_GET['id'] ?? 0);
$customer = fetchOne('SELECT * FROM customers WHERE id = ? LIMIT 1', 'i', [$customerId]);

if (!$customer) {
    setFlashMessage('error', 'Không tìm thấy khách hàng.');
    redirect(url('modules/customers/list_customers.php'));
}

$pageTitle = 'Chi tiết khách hàng';
$orders = fetchAll(
    'SELECT o.*, u.full_name AS staff_name, (SELECT SUM(quantity) FROM order_details od WHERE od.order_id = o.id) AS total_items FROM orders o JOIN users u ON u.id = o.staff_id WHERE o.customer_id = ? ORDER BY o.created_at DESC',
    'i',
    [$customerId]
);
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
        <div class="card mb-4">
            <div class="card-header flex-wrap gap-3">
                <h2 class="card-title"><i class="fas fa-user"></i> Chi tiết khách hàng</h2>
                <a href="<?php echo url('modules/customers/list_customers.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><strong>Họ tên:</strong><br><?php echo e($customer['name']); ?></div>
                <div class="col-md-4"><strong>Số điện thoại:</strong><br><?php echo e($customer['phone']); ?></div>
                <div class="col-md-4"><strong>Địa chỉ:</strong><br><?php echo e($customer['address'] ?: '-'); ?></div>
                <div class="col-md-4"><strong>Tổng đơn:</strong><br><?php echo e((int) $customer['total_orders']); ?></div>
                <div class="col-md-4"><strong>Tổng chi tiêu:</strong><br><?php echo e(formatMoney($customer['total_spent'])); ?></div>
                <div class="col-md-4"><strong>Ngày tạo:</strong><br><?php echo e(formatDateTime($customer['created_at'])); ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-history"></i> Lịch sử mua hàng</h2>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Ngày mua</th>
                            <th>Mã đơn</th>
                            <th>Nhân viên</th>
                            <th>Số sản phẩm</th>
                            <th>Tổng tiền</th>
                            <th>Tiền khách đưa</th>
                            <th>Tiền thừa</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$orders): ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">Khách hàng chưa có lỌh sử mua hàng.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo e(formatDateTime($order['created_at'])); ?></td>
                                <td><?php echo e($order['order_code']); ?></td>
                                <td><?php echo e($order['staff_name']); ?></td>
                                <td><?php echo e((int) $order['total_items']); ?></td>
                                <td><?php echo e(formatMoney($order['total_amount'])); ?></td>
                                <td><?php echo e(formatMoney($order['customer_paid'])); ?></td>
                                <td><?php echo e(formatMoney($order['change_amount'])); ?></td>
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
