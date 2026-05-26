<?php
$currentUser = getCurrentUser();
?>
<div class="header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="document.querySelector('.sidebar').classList.toggle('sidebar-open'); document.querySelector('.sidebar-overlay').classList.toggle('active');">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?php echo e($pageTitle ?? 'Dashboard'); ?></h1>
    </div>
    <div class="header-right">
        <div class="user-info">
            <img src="<?php echo getAvatarUrl($currentUser['avatar']); ?>" alt="Avatar" class="user-avatar">
            <div class="user-details">
                <span class="user-name"><?php echo e($currentUser['full_name']); ?></span>
                <span class="user-role"><?php echo $currentUser['role'] === ROLE_ADMIN ? 'Quản trị viên' : 'Nhân viên'; ?></span>
            </div>
        </div>
        <a href="<?php echo url('logout.php'); ?>" class="logout-btn" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
            <i class="fas fa-sign-out-alt"></i> <span class="logout-text">Đăng xuất</span>
        </a>
    </div>
</div>
<div class="sidebar-overlay" onclick="document.querySelector('.sidebar').classList.remove('sidebar-open'); this.classList.remove('active');"></div>
