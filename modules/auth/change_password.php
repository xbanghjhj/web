<?php
/**
 * ===================================================
 * CHANGE PASSWORD - Đổi mật khẩu
 * ===================================================
 */

require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

$pageTitle = "Đổi mật khẩu";
$currentUser = getCurrentUser();
$mustChangePassword = $_SESSION['must_change_password'] ?? 0;
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
    <link rel="stylesheet" href="<?php echo asset('css/login.css'); ?>">
    
    <style>
        .change-password-container {
            max-width: 600px;
            margin: 50px auto;
        }
        
        <?php if ($mustChangePassword): ?>
        /* Nếu bắt buộc đổi mật khẩu -> ẩn sidebar và header */
        .sidebar, .header {
            display: none;
        }
        .main-content {
            margin-left: 0;
            margin-top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <?php if (!$mustChangePassword): ?>
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>

    <!-- Header -->
    <?php include '../../includes/header.php'; ?>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="change-password-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="login-logo">
                        <i class="fas fa-key"></i>
                    </div>
                    <h1>Đổi mật khẩu</h1>
                    <?php if ($mustChangePassword): ?>
                    <div class="alert alert-warning" style="margin-top: 20px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><strong>Lưu ý:</strong> Bạn phải đổi mật khẩu trước khi sử dụng hệ thống!</span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Alert messages -->
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

                <!-- Change Password Form -->
                <form action="process_change_password.php" method="POST" id="changePasswordForm">
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-lock"></i> Mật khẩu hiện tại
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="current_password" 
                            name="current_password" 
                            placeholder="Nhập mật khẩu hiện tại"
                            required
                        >
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('current_password', this)"></i>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-key"></i> Mật khẩu mới
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
                            required
                            minlength="6"
                        >
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password', this)"></i>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-check-circle"></i> Xác nhận mật khẩu mới
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Nhập lại mật khẩu mới"
                            required
                            minlength="6"
                        >
                        <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-save"></i> Đổi mật khẩu
                    </button>

                    <?php if (!$mustChangePassword): ?>
                    <a href="<?php echo url('modules/dashboard/' . ($currentUser['role'] === 'admin' ? 'admin' : 'staff') . '_dashboard.php'); ?>" 
                       class="btn-login" 
                       style="background: #6c757d; margin-top: 10px; text-align: center;">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        // Form validation
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu mới và xác nhận mật khẩu không khớp!');
                return false;
            }

            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Mật khẩu mới phải có ít nhất 6 ký tự!');
                return false;
            }
        });
    </script>
</body>
</html>
