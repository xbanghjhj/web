<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/employees/list_employees.php'));
}

$employeeId = (int) ($_POST['id'] ?? 0);
if ($employeeId <= 0) {
    setFlashMessage('error', 'Yêu cầu không hợp lệ.');
    redirect(url('modules/employees/list_employees.php'));
}

if ($employeeId === (int) $_SESSION['user_id']) {
    setFlashMessage('error', 'Bạn không thể khóa tài khoản của chính mình.');
    redirect(url('modules/employees/list_employees.php'));
}

$employee = fetchOne("SELECT id, status FROM users WHERE id = ? AND role = 'staff' LIMIT 1", 'i', [$employeeId]);
if (!$employee) {
    setFlashMessage('error', 'Không tìm thấy nhân viên.');
    redirect(url('modules/employees/list_employees.php'));
}

$newStatus = $employee['status'] === STATUS_LOCKED ? STATUS_ACTIVE : STATUS_LOCKED;
$result = executeQuery('UPDATE users SET status = ? WHERE id = ?', 'si', [$newStatus, $employeeId]);

setFlashMessage($result ? 'success' : 'error', $result ? 'Đã cập nhật trạng thái nhân viên.' : 'Không thể cập nhật trạng thái.');
redirect(url('modules/employees/list_employees.php'));
