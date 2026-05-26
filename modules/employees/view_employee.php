<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$employeeId = (int) ($_GET['id'] ?? 0);
$pageTitle = 'Chi tiết nhân viên';
$employee = fetchOne("SELECT * FROM users WHERE id = ? AND role = 'staff' LIMIT 1", 'i', [$employeeId]);

if (!$employee) {
    setFlashMessage('error', 'Không tìm thấy nhân viên.');
    redirect(url('modules/employees/list_employees.php'));
}

$statusMeta = getUserStatusMeta($employee);
$stats = fetchOne(
    'SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS total_sales FROM orders WHERE staff_id = ?',
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
                <h2 class="card-title"><i class="fas fa-id-badge"></i> Chi tiết nhân viên</h2>
                <a href="<?php echo url('modules/employees/list_employees.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 text-center">
                    <img src="<?php echo getAvatarUrl($employee['avatar']); ?>" alt="Avatar" class="rounded-circle border" style="width: 160px; height: 160px; object-fit: cover;">
                    <h4 class="mt-3 mb-1"><?php echo e($employee['full_name']); ?></h4>
                    <span class="badge bg-<?php echo e($statusMeta['class']); ?>"><?php echo e($statusMeta['label']); ?></span>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6"><strong>Username:</strong><br><?php echo e($employee['username']); ?></div>
                        <div class="col-md-6"><strong>Email:</strong><br><?php echo e($employee['email']); ?></div>
                        <div class="col-md-6"><strong>Số điện thoại:</strong><br><?php echo e($employee['phone'] ?: '-'); ?></div>
                        <div class="col-md-6"><strong>Ngày tham gia:</strong><br><?php echo e(formatDateTime($employee['created_at'])); ?></div>
                        <div class="col-md-6"><strong>Tổng đơn hàng:</strong><br><?php echo e((int) ($stats['total_orders'] ?? 0)); ?></div>
                        <div class="col-md-6"><strong>Tổng doanh số:</strong><br><?php echo e(formatMoney($stats['total_sales'] ?? 0)); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
