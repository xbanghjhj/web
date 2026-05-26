<?php
require_once '../../config/config.php';
require_once '../../config/email_config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

if (!isPostRequest()) {
    redirect(url('modules/employees/add_employee.php'));
}

$fullName = cleanText($_POST['full_name'] ?? '');
$email = strtolower(cleanText($_POST['email'] ?? ''));
$username = usernameFromEmail($email);
$createdBy = (int) $_SESSION['user_id'];

if ($fullName === '' || $email === '') {
    setFlashMessage('error', 'Vui lòng nhập đầy đủ thông tin.');
    redirect(url('modules/employees/add_employee.php'));
}

if (!isValidEmailAddress($email) || substr($email, -10) !== '@gmail.com') {
    setFlashMessage('error', 'Vui lòng nhập địa chỉ Gmail hợp lệ.');
    redirect(url('modules/employees/add_employee.php'));
}

if ($username === '') {
    setFlashMessage('error', 'Không thể tạo username từ email này.');
    redirect(url('modules/employees/add_employee.php'));
}

$duplicate = fetchOne('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1', 'ss', [$username, $email]);
if ($duplicate) {
    setFlashMessage('error', 'Username hoặc email đã tồn tại.');
    redirect(url('modules/employees/add_employee.php'));
}

$conn = getDbConnection();
$conn->begin_transaction();

try {
    $insert = executeQuery(
        'INSERT INTO users (username, password, email, full_name, role, status, must_change_password, created_by) VALUES (?, ?, ?, ?, ?, ?, 1, ?)',
        'ssssssi',
        [$username, hashPassword(DEFAULT_PASSWORD), $email, $fullName, ROLE_STAFF, STATUS_ACTIVE, $createdBy]
    );

    if (!$insert) {
        throw new RuntimeException('Không thể tạo tài khoản.');
    }

    $userId = $conn->insert_id;
    $tokenData = createEmailVerificationToken($userId);

    if (!$tokenData) {
        throw new RuntimeException('Không thể tạo token email.');
    }

    $emailResult = sendVerificationEmail($email, $fullName, $tokenData['token'], $username);
    $conn->commit();

    $message = 'Đã tạo tài khoản nhân viên thành công.';
    if (($emailResult['mode'] ?? '') === 'log') {
        $message .= ' Lời mời đang nhập được ghi vào log local: ' . $emailResult['log_path'];
    } else {
        $message .= ' Email kích hoạt đã được gửi.';
    }

    setFlashMessage('success', $message);
    redirect(url('modules/employees/list_employees.php'));
} catch (Throwable $throwable) {
    $conn->rollback();
    error_log('Add employee failed: ' . $throwable->getMessage());
    setFlashMessage('error', 'Không thể tạo nhân viên mới. Vui lòng thử lại.');
    redirect(url('modules/employees/add_employee.php'));
}

