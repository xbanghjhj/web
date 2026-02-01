<?php
/**
 * ===================================================
 * HEADER - Include file cho header của dashboard
 * ===================================================
 */

$currentUser = getCurrentUser();
?>

<div class="header">
    <div class="header-left">
        <h1><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
    </div>
    
    <div class="header-right">
        <!-- User Info -->
        <div class="user-info" onclick="toggleUserMenu()">
            <img src="<?php echo getAvatarUrl($currentUser['avatar']); ?>" 
                 alt="Avatar" 
                 class="user-avatar">
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                <span class="user-role">
                    <?php echo $currentUser['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên'; ?>
                </span>
            </div>
        </div>

        <!-- Logout Button -->
        <a href="<?php echo url('logout.php'); ?>" class="logout-btn" 
           onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>
