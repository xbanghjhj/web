<?php
$currentUser = getCurrentUser();
$isAdmin = isAdmin();
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="<?php echo url('assets/images/logocuahang.jpg'); ?>" alt="POS System Logo">
        </div>
        <h2>POS SYSTEM</h2>
    </div>

    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-section-title">Dashboard</div>
            <a href="<?php echo $isAdmin ? url('modules/dashboard/admin_dashboard.php') : url('modules/dashboard/staff_dashboard.php'); ?>" class="menu-item">
                <i class="fas fa-gauge"></i> Dashboard
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Tài khoản</div>
            <a href="<?php echo url('modules/profile/view_profile.php'); ?>" class="menu-item">
                <i class="fas fa-user"></i> Thông tin cá nhân
            </a>
            <a href="<?php echo url('modules/auth/change_password.php'); ?>" class="menu-item">
                <i class="fas fa-key"></i> Đổi mật khẩu
            </a>
        </div>

        <?php if ($isAdmin): ?>
            <div class="menu-section">
                <div class="menu-section-title">Nhân viên</div>
                <a href="<?php echo url('modules/employees/list_employees.php'); ?>" class="menu-item">
                    <i class="fas fa-users"></i> Danh sách nhân viên
                </a>
                <a href="<?php echo url('modules/employees/add_employee.php'); ?>" class="menu-item">
                    <i class="fas fa-user-plus"></i> Thêm nhân viên
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Danh mục và sản phẩm</div>
                <a href="<?php echo url('modules/categories/list_categories.php'); ?>" class="menu-item">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a href="<?php echo url('modules/products/list_products.php'); ?>" class="menu-item">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
            </div>
        <?php else: ?>
            <div class="menu-section">
                <div class="menu-section-title">Sản phẩm</div>
                <a href="<?php echo url('modules/products/list_products.php'); ?>" class="menu-item">
                    <i class="fas fa-box"></i> Danh sách sản phẩm
                </a>
            </div>
        <?php endif; ?>

        <div class="menu-section">
            <div class="menu-section-title">Bán hàng</div>
            <a href="<?php echo url('modules/pos/pos.php'); ?>" class="menu-item">
                <i class="fas fa-cash-register"></i> POS - Bán hàng
            </a>
            <a href="<?php echo url('modules/orders/list_orders.php'); ?>" class="menu-item">
                <i class="fas fa-receipt"></i> Đơn hàng
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Khách hàng</div>
            <a href="<?php echo url('modules/customers/list_customers.php'); ?>" class="menu-item">
                <i class="fas fa-user-friends"></i> Danh sách khách hàng
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Báo cáo</div>
            <a href="<?php echo url('modules/reports/sales_report.php'); ?>" class="menu-item">
                <i class="fas fa-chart-line"></i> Báo cáo doanh thu
            </a>
            <?php if ($isAdmin): ?>
                <a href="<?php echo url('modules/reports/profit_report.php'); ?>" class="menu-item">
                    <i class="fas fa-chart-pie"></i> Báo cáo lợi nhuận
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
