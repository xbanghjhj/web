<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$orderId = (int) ($_GET['id'] ?? 0);
$order = fetchOne(
    'SELECT o.*, c.name AS customer_name, c.phone, c.address, u.full_name AS staff_name FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = o.staff_id WHERE o.id = ? LIMIT 1',
    'i',
    [$orderId]
);

if (!$order) {
    setFlashMessage('error', 'Không tìm thấy đơn hàng.');
    redirect(url('modules/orders/list_orders.php'));
}

if (isStaff() && (int) $order['staff_id'] !== (int) $_SESSION['user_id']) {
    setFlashMessage('error', 'Bạn không có quyền xem đơn hàng này.');
    redirect(url('modules/orders/list_orders.php'));
}

$pageTitle = 'Chi tiết đơn hàng';
$orderDetails = fetchAll('SELECT * FROM order_details WHERE order_id = ? ORDER BY id ASC', 'i', [$orderId]);
$profit = 0;
if (isAdmin()) {
    foreach ($orderDetails as $detail) {
        $profit += ((float) $detail['price'] - (float) $detail['price_buy']) * (int) $detail['quantity'];
    }
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
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <div class="main-content">
        <div class="card mb-4">
            <div class="card-header flex-wrap gap-3">
                <h2 class="card-title"><i class="fas fa-file-invoice"></i> Chi tiết đơn hàng <?php echo e($order['order_code']); ?></h2>
                <div class="d-flex gap-2">
                    <a href="<?php echo url('modules/pos/invoice.php?id=' . $orderId); ?>" class="btn btn-outline-primary"><i class="fas fa-print"></i> Hóa đơn</a>
                    <a href="<?php echo url('modules/orders/list_orders.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><strong>Khách hàng:</strong><br><?php echo e($order['customer_name']); ?> - <?php echo e($order['phone']); ?></div>
                <div class="col-md-4"><strong>Nhân viên:</strong><br><?php echo e($order['staff_name']); ?></div>
                <div class="col-md-4"><strong>Ngày giờ:</strong><br><?php echo e(formatDateTime($order['created_at'])); ?></div>
                <div class="col-md-4"><strong>Tổng tiền:</strong><br><?php echo e(formatMoney($order['total_amount'])); ?></div>
                <div class="col-md-4"><strong>Tiền khách đưa:</strong><br><?php echo e(formatMoney($order['customer_paid'])); ?></div>
                <div class="col-md-4"><strong>Tiền thừa:</strong><br><?php echo e(formatMoney($order['change_amount'])); ?></div>
                <?php if (isAdmin()): ?><div class="col-md-4"><strong>Lợi nhuận đơn:</strong><br><?php echo e(formatMoney($profit)); ?></div><?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-list"></i> Sản phẩm trong đơn</h2>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                            <?php if (isAdmin()): ?><th>Lợi nhuận</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderDetails as $detail): ?>
                            <tr>
                                <td><?php echo e($detail['product_name']); ?></td>
                                <td><?php echo e((int) $detail['quantity']); ?></td>
                                <td><?php echo e(formatMoney($detail['price'])); ?></td>
                                <td><?php echo e(formatMoney($detail['subtotal'])); ?></td>
                                <?php if (isAdmin()): ?><td><?php echo e(formatMoney(((float) $detail['price'] - (float) $detail['price_buy']) * (int) $detail['quantity'])); ?></td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
