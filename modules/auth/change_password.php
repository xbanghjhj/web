<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

$pageTitle = 'Đổi mật khẩu';
$currentUser = getCurrentUser();
$mustChange = mustChangePassword();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - POS System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?php if ($mustChange): ?>
        <link rel="stylesheet" href="<?php echo asset('css/login.css'); ?>?v=<?= time() ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?php echo asset('css/login.css'); ?>?v=<?= time() ?>">
        <style>
            .change-password-container { max-width: 560px; margin: 40px auto; }
        </style>
    <?php endif; ?>
</head>
<body>
    <?php if ($mustChange): ?>
    <!-- ===== FULL PAGE LAYOUT (First-time login) ===== -->
    <div class="login-wrapper">
        <!-- Left Side - Form -->
        <div class="login-left">
            <div class="login-left-inner" style="max-width: 420px;">
                <div class="brand">
                    <img src="<?php echo url('assets/images/logocuahang.jpg'); ?>" alt="POS System Logo" class="brand-logo">
                    <span class="brand-name">POS System</span>
                </div>

                <div class="welcome-text">
                    <h1>Đổi mật khẩu</h1>
                    <p>Vui lòng đổi mật khẩu trước khi truy cập hệ thống</p>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Bạn phải đổi mật khẩu trước khi truy cập các chức năng khác.</span>
                </div>

                <?php $flashMessage = getFlashMessage(); ?>
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo e($flashMessage['type']); ?>">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo e($flashMessage['message']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Lần đăng nhập đầu tiên không cần nhập mật khẩu cũ.</span>
                </div>

                <form action="process_change_password.php" method="POST" id="changePasswordForm">
                    <div class="form-group">
                        <div class="input-wrapper floating-label">
                            <i class="fas fa-key input-icon-left"></i>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder=" " minlength="6" required>
                            <label for="new_password" class="float-label">Mật khẩu mới</label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('new_password', this)"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper floating-label">
                            <i class="fas fa-check-circle input-icon-left"></i>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder=" " minlength="6" required>
                            <label for="confirm_password" class="float-label">Xác nhận mật khẩu mới</label>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn">
                        <span class="btn-text"><i class="fas fa-save"></i> Cập nhật mật khẩu</span>
                        <span class="btn-loading" style="display:none;">
                            <span class="spinner"></span> Đang cập nhật...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Side - Decorative -->
        <div class="login-right">
            <div class="right-decoration">
                <div class="decoration-shape shape-1"></div>
                <div class="decoration-shape shape-2"></div>
                <div class="decoration-shape shape-3"></div>
            </div>
            <div class="right-content">
                <img src="<?php echo url('assets/images/logocuahang.jpg'); ?>" alt="POS System" class="hero-image" style="max-width: 180px;">
                <h2>POS System</h2>
                <p>Hệ thống quản lý bán hàng<br>điện thoại và phụ kiện</p>
            </div>
            <div class="floating-dots">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ===== NORMAL LAYOUT (Already logged in) ===== -->
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>

    <div class="main-content">
        <div class="change-password-container w-100">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-key"></i> Đổi mật khẩu</h2>
                </div>

                <?php $flashMessage = getFlashMessage(); ?>
                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo e($flashMessage['type']); ?>">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo e($flashMessage['message']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="process_change_password.php" method="POST" id="changePasswordForm">
                    <div class="form-group mb-3">
                        <label for="current_password" class="form-label"><i class="fas fa-lock"></i> Mật khẩu hiện tại</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('current_password', this.querySelector('i'))">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="new_password" class="form-label"><i class="fas fa-key"></i> Mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('new_password', this.querySelector('i'))">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="confirm_password" class="form-label"><i class="fas fa-check-circle"></i> Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password', this.querySelector('i'))">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật mật khẩu
                        </button>
                        <a href="<?php echo routeByRole($currentUser['role']); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (!field) return;
            field.type = field.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }

        document.getElementById('changePasswordForm').addEventListener('submit', function (event) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                event.preventDefault();
                alert('Mật khẩu mới và xác nhận mật khẩu không khớp.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('is-loading');
                submitBtn.querySelector('.btn-text').style.display = 'none';
                submitBtn.querySelector('.btn-loading').style.display = 'inline-flex';
            }
        });
    </script>
</body>
</html>
