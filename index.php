<?php
require_once 'config/config.php';
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - POS System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css?v=<?= time() ?>">
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Side - Form -->
        <div class="login-left">
            <div class="login-left-inner">
                <div class="brand">
                    <img src="assets/images/logocuahang.jpg" alt="POS System Logo" class="brand-logo">
                    <span class="brand-name">POS System</span>
                </div>

                <div class="welcome-text">
                    <h1>Chào mừng trở lại</h1>
                    <p>Đăng nhập để tiếp tục sử dụng hệ thống</p>
                </div>

                <?php if ($flashMessage): ?>
                    <div class="alert alert-<?php echo e($flashMessage['type']); ?>">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo e($flashMessage['message']); ?></span>
                    </div>
                <?php endif; ?>

                <form action="modules/auth/process_login.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <div class="input-wrapper floating-label">
                            <i class="fas fa-user input-icon-left"></i>
                            <input type="text" class="form-control" id="username" name="username" placeholder=" " required autocomplete="username" autofocus>
                            <label for="username" class="float-label">Tên đăng nhập</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper floating-label">
                            <i class="fas fa-lock input-icon-left"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder=" " required autocomplete="current-password">
                            <label for="password" class="float-label">Mật khẩu</label>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Ghi nhớ đăng nhập</label>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Đăng nhập</span>
                        <span class="btn-loading" style="display:none;">
                            <span class="spinner"></span> Đang đăng nhập...
                        </span>
                    </button>
                </form>

                <div class="login-footer">
                    <p>Admin mặc định: username <strong>admin</strong> - password <strong>admin</strong></p>
                </div>
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
                <img src="assets/images/logocuahang.jpg" alt="POS System" class="hero-image" style="max-width: 180px;">
                <h2>POS System</h2>
                <p>Hệ thống quản lý bán hàng<br>điện thoại và phụ kiện</p>
            </div>
            <div class="floating-dots">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        loginForm.addEventListener('submit', function () {
            loginBtn.disabled = true;
            loginBtn.classList.add('is-loading');
            loginBtn.querySelector('.btn-text').style.display = 'none';
            loginBtn.querySelector('.btn-loading').style.display = 'inline-flex';
        });
    </script>
</body>
</html>
