<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$pageTitle = 'Quản lý nhân viên';
$search = cleanText($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * ITEMS_PER_PAGE;

$where = "WHERE role = 'staff'";
$types = '';
$params = [];

if ($search !== '') {
    $where .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $keyword = '%' . $search . '%';
    $types = 'sss';
    $params = [$keyword, $keyword, $keyword];
}

$totalRows = fetchOne("SELECT COUNT(*) AS total FROM users {$where}", $types, $params);
$totalEmployees = (int) ($totalRows['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalEmployees / ITEMS_PER_PAGE));

$employees = fetchAll("SELECT * FROM users {$where} ORDER BY created_at DESC LIMIT {$offset}, " . ITEMS_PER_PAGE, $types, $params);
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
    <link rel="stylesheet" href="<?php echo asset('css/fuzzy-search.css'); ?>">
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
            <div class="card-header flex-wrap gap-3">
                <h2 class="card-title"><i class="fas fa-users"></i> Danh sách nhân viên</h2>
                <a href="<?php echo url('modules/employees/add_employee.php'); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Thêm nhân viên
                </a>
            </div>

            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-8">
                    <label class="form-label">Tìm kiếm theo tên, sdt, email</label>
                    <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>" placeholder="Nhập từ khóa..." data-fuzzy-search="employees" autocomplete="off">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
                    <a href="<?php echo url('modules/employees/list_employees.php'); ?>" class="btn btn-outline-secondary">Dat lai</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Ho ten</th>
                            <th>Gmail</th>
                            <th>Số điện thoại</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$employees): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">Chưa có nhân viên nào.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($employees as $employee): ?>
                            <?php $statusMeta = getUserStatusMeta($employee); ?>
                            <tr>
                                <td><img src="<?php echo getAvatarUrl($employee['avatar']); ?>" alt="Avatar" class="rounded-circle border" style="width: 52px; height: 52px; object-fit: cover;"></td>
                                <td>
                                    <strong><?php echo e($employee['full_name']); ?></strong><br>
                                    <small class="text-muted">@<?php echo e($employee['username']); ?></small>
                                </td>
                                <td><?php echo e($employee['email']); ?></td>
                                <td><?php echo e($employee['phone'] ?: '-'); ?></td>
                                <td><span class="badge bg-<?php echo e($statusMeta['class']); ?>"><?php echo e($statusMeta['label']); ?></span></td>
                                <td><?php echo e(formatDateTime($employee['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                        <a href="<?php echo url('modules/employees/view_employee.php?id=' . (int) $employee['id']); ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                        <a href="<?php echo url('modules/employees/sales_history.php?id=' . (int) $employee['id']); ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-chart-line"></i></a>
                                        <?php if (!empty($employee['must_change_password'])): ?>
                                            <form action="resend_email.php" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo (int) $employee['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-paper-plane"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="toggle_lock.php" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?php echo (int) $employee['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $employee['status'] === STATUS_LOCKED ? 'btn-success' : 'btn-danger'; ?>">
                                                <i class="fas <?php echo $employee['status'] === STATUS_LOCKED ? 'fa-lock-open' : 'fa-lock'; ?>"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
    <script src="<?php echo asset('js/fuzzy-search.js'); ?>"></script>
</body>
</html>
