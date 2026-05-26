<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);
$pageTitle = 'Thêm nhân viên';
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
                <h2 class="card-title"><i class="fas fa-user-plus"></i> Thêm nhân viên mới</h2>
            </div>
            <form action="process_add_employee.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gmail</label>
                    <input type="email" name="email" class="form-control" required>
                    <div class="form-text">Username sẽ tự động lấy phần trước dấu @.</div>
                </div>
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i>
                        <span>Mật khẩu tạm mặc định sẽ là <strong><?php echo e(DEFAULT_PASSWORD); ?></strong>. Hệ thống sẽ tạo link kích hoạt có hiệu lực trong 1 phút.</span>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Tạo tài khoản và gửi lời mời</button>
                    <a href="<?php echo url('modules/employees/list_employees.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
