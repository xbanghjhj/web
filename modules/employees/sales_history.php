<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$employeeId = (int) ($_GET['id'] ?? 0);
$employee = fetchOne("SELECT * FROM users WHERE id = ? AND role = 'staff' LIMIT 1", 'i', [$employeeId]);

if (!$employee) {
    setFlashMessage('error', 'Không tìm thấy nhân viên.');
    redirect(url('modules/employees/list_employees.php'));
}

$pageTitle = 'Lịch sử bán hàng';
$orders = fetchAll(
    'SELECT o.*, c.name AS customer_name, c.phone FROM orders o JOIN customers c ON c.id = o.customer_id WHERE o.staff_id = ? ORDER BY o.created_at DESC',
    'i',
    [$employeeId]
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
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-chart-line"></i> Lịch sử bán hàng - <?php echo e($employee['full_name']); ?></h2>
                <a href="<?php echo url('modules/employees/list_employees.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Tiền khách đưa</th>
                            <th>Tiền thừa</th>
                            <th>Ngày tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$orders): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">Nhân viên chưa có giao dịch nào.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo e($order['order_code']); ?></td>
                                <td><?php echo e($order['customer_name']); ?><br><small class="text-muted"><?php echo e($order['phone']); ?></small></td>
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
