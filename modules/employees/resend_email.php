<?php
require_once '../../config/config.php';
require_once '../../config/email_config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/employees/list_employees.php'));
}

$employeeId = (int) ($_POST['id'] ?? 0);
$employee = fetchOne("SELECT * FROM users WHERE id = ? AND role = 'staff' LIMIT 1", 'i', [$employeeId]);

if (!$employee) {
    setFlashMessage('error', 'Không tìm thấy nhân viên.');
    redirect(url('modules/employees/list_employees.php'));
}

if (empty($employee['must_change_password'])) {
    setFlashMessage('warning', 'Nhân viên này đã kích hoạt tài khoản, không cần gửi lại link.');
    redirect(url('modules/employees/list_employees.php'));
}

$tokenData = createEmailVerificationToken($employeeId);
if (!$tokenData) {
    setFlashMessage('error', 'Không thể tạo token mới.');
    redirect(url('modules/employees/list_employees.php'));
}

$emailResult = sendVerificationEmail($employee['email'], $employee['full_name'], $tokenData['token'], $employee['username']);

if (!empty($emailResult['success'])) {
    $message = ($emailResult['mode'] ?? '') === 'smtp'
        ? 'Đã gửi lại email kích hoạt cho nhân viên.'
        : 'Đã tạo link mới và ghi vào log local: ' . $emailResult['log_path'];
    setFlashMessage('success', $message);
} else {
    setFlashMessage('error', 'Gửi lại email thất bại và không tạo được log local.');
}

redirect(url('modules/employees/list_employees.php'));

