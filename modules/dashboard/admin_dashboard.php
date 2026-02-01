<?php
/**
 * ===================================================
 * ADMIN DASHBOARD
 * ===================================================
 */

require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

// Chỉ admin mới truy cập được
if (!isAdmin()) {
    setFlashMessage('error', 'Bạn không có quyền truy cập trang này!');
    redirect(url('modules/dashboard/staff_dashboard.php'));
}

$pageTitle = "Dashboard Admin";

// Lấy thống kê
$conn = getDbConnection();

// Tổng số nhân viên
$staffCount = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'staff'")['count'] ?? 0;

// Tổng số sản phẩm
$productCount = fetchOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0;

// Tổng số khách hàng
$customerCount = fetchOne("SELECT COUNT(*) as count FROM customers")['count'] ?? 0;

// Tổng doanh thu hôm nay
$todayRevenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total 
                          FROM orders 
                          WHERE DATE(order_date) = CURDATE()")['total'] ?? 0;

// Tổng đơn hàng hôm nay
$todayOrders = fetchOne("SELECT COUNT(*) as count 
                         FROM orders 
                         WHERE DATE(order_date) = CURDATE()")['count'] ?? 0;

// Lấy đơn hàng gần đây
$recentOrders = fetchAll("SELECT o.*, c.name as customer_name, c.phone, u.full_name as staff_name
                          FROM orders o
                          JOIN customers c ON o.customer_id = c.id
                          JOIN users u ON o.staff_id = u.id
                          ORDER BY o.created_at DESC
                          LIMIT 10");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - POS System</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>
<body>
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Header -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Flash Message -->
        <?php
        $flashMessage = getFlashMessage();
        if ($flashMessage):
        ?>
        <div class="alert alert-<?php echo $flashMessage['type']; ?>">
            <i class="fas fa-<?php 
                echo $flashMessage['type'] === 'success' ? 'check-circle' : 
                     ($flashMessage['type'] === 'error' ? 'exclamation-circle' : 
                     ($flashMessage['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle')); 
            ?>"></i>
            <span><?php echo $flashMessage['message']; ?></span>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $staffCount; ?></h3>
                    <p>Nhân viên</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $productCount; ?></h3>
                    <p>Sản phẩm</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $customerCount; ?></h3>
                    <p>Khách hàng</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo formatMoney($todayRevenue); ?></h3>
                    <p>Doanh thu hôm nay</p>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-receipt"></i> Đơn hàng gần đây
                </h2>
                <a href="<?php echo url('modules/orders/list_orders.php'); ?>" class="btn btn-primary btn-sm">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Nhân viên</th>
                            <th>Tổng tiền</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #6c757d;">
                                <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                                <p style="margin-top: 10px;">Chưa có đơn hàng nào</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong><?php echo $order['order_code']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                    <small style="color: #6c757d;"><?php echo $order['phone']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($order['staff_name']); ?></td>
                                <td><strong><?php echo formatMoney($order['total_amount']); ?></strong></td>
                                <td><?php echo formatDateTime($order['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo url('modules/orders/view_order.php?id=' . $order['id']); ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-bolt"></i> Thao tác nhanh
                </h2>
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="<?php echo url('modules/pos/pos.php'); ?>" class="btn btn-success">
                    <i class="fas fa-cash-register"></i> Bán hàng ngay
                </a>
                <a href="<?php echo url('modules/employees/add_employee.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Thêm nhân viên
                </a>
                <a href="<?php echo url('modules/products/add_product.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Thêm sản phẩm
                </a>
                <a href="<?php echo url('modules/reports/sales_report.php'); ?>" class="btn btn-warning">
                    <i class="fas fa-chart-line"></i> Xem báo cáo
                </a>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
