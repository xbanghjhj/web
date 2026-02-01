<?php
/**
 * ===================================================
 * STAFF DASHBOARD
 * ===================================================
 */

require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

$pageTitle = "Dashboard Nhân viên";
$currentUser = getCurrentUser();

// Lấy thống kê cá nhân
$staffId = $currentUser['id'];

// Tổng đơn hàng của nhân viên
$myOrders = fetchOne("SELECT COUNT(*) as count FROM orders WHERE staff_id = ?", 'i', [$staffId])['count'] ?? 0;

// Doanh thu cá nhân
$myRevenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE staff_id = ?", 'i', [$staffId])['total'] ?? 0;

// Doanh thu hôm nay
$todayRevenue = fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total 
                          FROM orders 
                          WHERE staff_id = ? AND DATE(order_date) = CURDATE()", 'i', [$staffId])['total'] ?? 0;

// Đơn hàng hôm nay
$todayOrders = fetchOne("SELECT COUNT(*) as count 
                         FROM orders 
                         WHERE staff_id = ? AND DATE(order_date) = CURDATE()", 'i', [$staffId])['count'] ?? 0;

// Lấy đơn hàng gần đây của nhân viên
$recentOrders = fetchAll("SELECT o.*, c.name as customer_name, c.phone
                          FROM orders o
                          JOIN customers c ON o.customer_id = c.id
                          WHERE o.staff_id = ?
                          ORDER BY o.created_at DESC
                          LIMIT 10", 'i', [$staffId]);
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

        <!-- Welcome Message -->
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 30px;">
            <h2 style="margin: 0;">👋 Xin chào, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Chúc bạn một ngày làm việc hiệu quả!</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $todayOrders; ?></h3>
                    <p>Đơn hàng hôm nay</p>
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

            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $myOrders; ?></h3>
                    <p>Tổng đơn hàng</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo formatMoney($myRevenue); ?></h3>
                    <p>Tổng doanh thu</p>
                </div>
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
                <a href="<?php echo url('modules/pos/pos.php'); ?>" class="btn btn-success" style="font-size: 16px; padding: 15px 30px;">
                    <i class="fas fa-cash-register"></i> Bắt đầu bán hàng
                </a>
                <a href="<?php echo url('modules/orders/list_orders.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-list"></i> Xem đơn hàng
                </a>
                <a href="<?php echo url('modules/customers/list_customers.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-users"></i> Khách hàng
                </a>
                <a href="<?php echo url('modules/reports/sales_report.php'); ?>" class="btn btn-warning">
                    <i class="fas fa-chart-bar"></i> Báo cáo
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-history"></i> Đơn hàng gần đây của bạn
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
                            <th>Tổng tiền</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #6c757d;">
                                <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                                <p style="margin-top: 10px;">Chưa có đơn hàng nào</p>
                                <a href="<?php echo url('modules/pos/pos.php'); ?>" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Tạo đơn hàng đầu tiên
                                </a>
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
    </div>

    <!-- JavaScript -->
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</body>
</html>
