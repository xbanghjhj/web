<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

$pageTitle = 'Thông tin cá nhân';
$user = fetchOne('SELECT * FROM users WHERE id = ? LIMIT 1', 'i', [$_SESSION['user_id']]);

if (!$user) {
    setFlashMessage('error', 'Không tìm thấy thông tin tài khoản.');
    redirect(routeByRole());
}
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
        <?php $flashMessage = getFlashMessage(); ?>
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo e($flashMessage['type']); ?>">
                <i class="fas fa-info-circle"></i>
                <span><?php echo e($flashMessage['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-circle"></i> Hồ sơ cá nhân</h2>
                <a href="<?php echo url('modules/auth/change_password.php'); ?>" class="btn btn-warning">
                    <i class="fas fa-key"></i> Đổi mật khẩu
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 text-center">
                    <img src="<?php echo getAvatarUrl($user['avatar']); ?>" alt="Avatar" class="rounded-circle border" style="width: 160px; height: 160px; object-fit: cover;">
                    <p class="mt-3 mb-1"><strong><?php echo e($user['full_name']); ?></strong></p>
                    <span class="badge bg-<?php echo isAdmin() ? 'primary' : 'success'; ?>">
                        <?php echo isAdmin() ? 'Admin' : 'Nhân viên'; ?>
                    </span>
                </div>
                <div class="col-lg-8">
                    <form action="process_update_profile.php" method="POST" enctype="multipart/form-data" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ho va ten</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo e($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo e($user['email']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo e($user['username']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($user['phone']); ?>" placeholder="Nhập số điện thoại">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Avatar moi</label>
                            <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                            <div class="form-text">Nhập ảnh JPG, PNG, GIF. Tối đa 5MB.</div>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thông tin
                            </button>
                            <a href="<?php echo routeByRole(); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
