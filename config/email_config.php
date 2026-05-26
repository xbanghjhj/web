<?php
/**
 * Lightweight email service with SMTP-if-available and local-log fallback.
 */

define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'your-email@gmail.com');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'your-app-password');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: SMTP_USERNAME);
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'POS System - Mobile Shop');

function getMailerFiles() {
    return [
        BASE_PATH . '/libs/phpmailer/PHPMailer.php',
        BASE_PATH . '/libs/phpmailer/SMTP.php',
        BASE_PATH . '/libs/phpmailer/Exception.php',
    ];
}

function canSendRealEmail() {
    if (SMTP_USERNAME === 'your-email@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
        return false;
    }

    foreach (getMailerFiles() as $file) {
        if (!file_exists($file)) {
            return false;
        }
    }

    return true;
}

function createEmailVerificationToken($userId) {
    $token = generateToken(32);
    $expiresAt = date('Y-m-d H:i:s', time() + EMAIL_TOKEN_EXPIRE);

    executeQuery('UPDATE email_tokens SET is_used = 1 WHERE user_id = ? AND is_used = 0', 'i', [$userId]);
    $result = executeQuery(
        'INSERT INTO email_tokens (user_id, token, expires_at, is_used) VALUES (?, ?, ?, 0)',
        'iss',
        [$userId, $token, $expiresAt]
    );

    if (!$result) {
        return false;
    }

    return [
        'token' => $token,
        'expires_at' => $expiresAt,
    ];
}

function getVerificationEmailTemplate($name, $username, $link) {
    $defaultPassword = e(DEFAULT_PASSWORD);

    return '
    <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; background: #f3f4f6; padding: 24px;">
        <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.14);">
            <div style="padding: 28px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff;">
                <h1 style="margin: 0 0 8px; font-size: 28px;">POS System</h1>
                <p style="margin: 0; opacity: 0.92;">Thư mời kích hoạt tài khoản nhân viên</p>
            </div>
            <div style="padding: 32px;">
                <p>Xin chào <strong>' . e($name) . '</strong>,</p>
                <p>Admin đã tạo tài khoản để bạn đăng nhập vào hệ thống quản lý cửa hàng điện thoại.</p>
                <div style="background: #f8fafc; border-left: 4px solid #667eea; padding: 16px 18px; border-radius: 10px; margin: 20px 0;">
                    <p style="margin: 0 0 8px;"><strong>Username:</strong> ' . e($username) . '</p>
                    <p style="margin: 0;"><strong>Mật khẩu tạm:</strong> ' . $defaultPassword . '</p>
                </div>
                <p>Kích hoạt chỉ có hiệu lực trong <strong>1 phút</strong>. Sau đó, vui lòng liên hệ Admin để gửi lại.</p>
                <p style="margin: 24px 0;">
                    <a href="' . e($link) . '" style="display: inline-block; padding: 14px 26px; border-radius: 999px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; font-weight: 700;">
                        Kích hoạt tài khoản
                    </a>
                </p>
                <p style="font-size: 14px; color: #6b7280;">Nếu nút không hoạt động, hãy mở đường dẫn này trong trình duyệt:</p>
                <p style="font-size: 14px; color: #334155; word-break: break-all;">' . e($link) . '</p>
            </div>
        </div>
    </div>';
}

function sendVerificationEmail($to, $name, $token, $username) {
    $subject = 'Kích hoạt tài khoản POS System';
    $verificationLink = BASE_URL . '/verify-email.php?token=' . urlencode($token);
    $htmlBody = getVerificationEmailTemplate($name, $username, $verificationLink);

    if (!canSendRealEmail()) {
        $logPath = logEmailMessage($to, $subject, $htmlBody);

        return [
            'success' => (bool) $logPath,
            'mode' => 'log',
            'message' => $logPath ? 'Email đã được ghi vào log local.' : 'Không thể ghi email vào log local.',
            'log_path' => $logPath,
            'link' => $verificationLink,
        ];
    }

    require_once BASE_PATH . '/libs/phpmailer/PHPMailer.php';
    require_once BASE_PATH . '/libs/phpmailer/SMTP.php';
    require_once BASE_PATH . '/libs/phpmailer/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = SMTP_AUTH;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = 'Xin chào ' . $name . '. Kích hoạt tài khoản tại: ' . $verificationLink;
        $mail->send();

        return [
            'success' => true,
            'mode' => 'smtp',
            'message' => 'Email kích hoạt đã được gửi thành công.',
            'log_path' => null,
            'link' => $verificationLink,
        ];
    } catch (Exception $exception) {
        error_log('Email send failed: ' . $mail->ErrorInfo);
        $logPath = logEmailMessage($to, $subject, $htmlBody);

        return [
            'success' => (bool) $logPath,
            'mode' => 'log',
            'message' => 'Gửi email thất bại, đã chuyển sang log local.',
            'log_path' => $logPath,
            'link' => $verificationLink,
        ];
    }
}
