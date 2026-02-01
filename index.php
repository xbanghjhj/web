<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - POS System</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <!-- Particles effect background -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h1>POS System</h1>
                <p>Cửa hàng điện thoại & phụ kiện</p>
            </div>

            <!-- Alert messages -->
            <?php
            // Include config để sử dụng flash messages
            require_once 'config/config.php';
            
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

            <!-- Login Form -->
            <form action="modules/auth/process_login.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Tên đăng nhập
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="username" 
                        name="username" 
                        placeholder="Nhập username hoặc email"
                        required
                        autocomplete="username"
                        autofocus
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Nhập mật khẩu"
                        required
                        autocomplete="current-password"
                    >
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>&copy; 2026 POS System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        loginForm.addEventListener('submit', function(e) {
            // Show loading state
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner"></span> Đang đăng nhập...';
        });

        // Create particles effect
        const particlesContainer = document.getElementById('particles');
        const particleCount = 30;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            // Random size between 3-10px
            const size = Math.random() * 7 + 3;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            
            // Random position
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            
            // Random animation delay
            particle.style.animationDelay = Math.random() * 20 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
            
            particlesContainer.appendChild(particle);
        }

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });

        // Form validation
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const passwordInput = document.getElementById('password').value;

            if (username === '') {
                e.preventDefault();
                alert('Vui lòng nhập tên đăng nhập!');
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Đăng nhập';
                return false;
            }

            if (passwordInput === '') {
                e.preventDefault();
                alert('Vui lòng nhập mật khẩu!');
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Đăng nhập';
                return false;
            }
        });
    </script>
</body>
</html>
