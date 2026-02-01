<?php
/**
 * ===================================================
 * EMAIL CONFIGURATION - Cấu hình gửi email
 * ===================================================
 * File: config/email_config.php
 * Mục đích: Cấu hình PHPMailer để gửi email
 * ===================================================
 */

// ===================================================
// SMTP SETTINGS - Sử dụng Gmail SMTP
// ===================================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // TLS port
define('SMTP_SECURE', 'tls'); // tls hoặc ssl
define('SMTP_AUTH', true);

// ⚠️ QUAN TRỌNG: Thay đổi thành email & password của bạn
// Để sử dụng Gmail SMTP:
// 1. Vào https://myaccount.google.com/security
// 2. Bật "2-Step Verification"
// 3. Tạo "App Password" tại https://myaccount.google.com/apppasswords
// 4. Sử dụng App Password thay vì mật khẩu Gmail

define('SMTP_USERNAME', 'your-email@gmail.com'); // Email của bạn
define('SMTP_PASSWORD', 'your-app-password'); // App Password (không phải mật khẩu Gmail)

// Email người gửi
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'POS System - Mobile Shop');

// ===================================================
// EMAIL TEMPLATES
// ===================================================

/**
 * Gửi email xác thực cho nhân viên mới
 * @param string $to Email nhận
 * @param string $name Tên nhân viên
 * @param string $token Token xác thực
 * @param string $username Username
 * @return bool
 */
function sendVerificationEmail($to, $name, $token, $username) {
    // Load PHPMailer
    require_once BASE_PATH . '/libs/phpmailer/PHPMailer.php';
    require_once BASE_PATH . '/libs/phpmailer/SMTP.php';
    require_once BASE_PATH . '/libs/phpmailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = SMTP_AUTH;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Xác thực tài khoản POS System';
        
        // Tạo link xác thực
        $verificationLink = BASE_URL . '/verify-email.php?token=' . $token;
        
        // Email body
        $mail->Body = getVerificationEmailTemplate($name, $username, $verificationLink);
        $mail->AltBody = "Xin chào $name,\n\nVui lòng nhấp vào link sau để xác thực tài khoản:\n$verificationLink\n\nLưu ý: Link này chỉ có hiệu lực trong 1 phút.\n\nUsername: $username\nPassword mặc định: " . DEFAULT_PASSWORD . "\n\nBạn sẽ được yêu cầu đổi mật khẩu khi đăng nhập lần đầu.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML cho email xác thực
 */
function getVerificationEmailTemplate($name, $username, $link) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 30px; }
            .content h2 { color: #667eea; margin-top: 0; }
            .info-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px; }
            .info-box p { margin: 5px 0; }
            .info-box strong { color: #667eea; }
            .btn { display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .btn:hover { opacity: 0.9; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; color: #856404; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎉 Chào mừng đến với POS System</h1>
            </div>
            <div class="content">
                <h2>Xin chào ' . htmlspecialchars($name) . ',</h2>
                <p>Tài khoản của bạn đã được tạo thành công tại <strong>POS System - Cửa hàng điện thoại & phụ kiện</strong>.</p>
                
                <div class="info-box">
                    <p><strong>👤 Username:</strong> ' . htmlspecialchars($username) . '</p>
                    <p><strong>🔒 Password mặc định:</strong> ' . DEFAULT_PASSWORD . '</p>
                </div>
                
                <div class="warning">
                    <p><strong>⚠️ LƯU Ý QUAN TRỌNG:</strong></p>
                    <ul style="margin: 10px 0;">
                        <li>Link xác thực này chỉ có hiệu lực trong <strong>1 phút</strong></li>
                        <li>Sau 1 phút, link sẽ hết hạn và bạn cần liên hệ Admin để gửi lại</li>
                        <li>Bạn <strong>BẮT BUỘC</strong> phải đổi mật khẩu khi đăng nhập lần đầu</li>
                    </ul>
                </div>
                
                <center>
                    <a href="' . $link . '" class="btn">🔐 Xác thực tài khoản ngay</a>
                </center>
                
                <p style="margin-top: 20px; font-size: 14px; color: #6c757d;">
                    Nếu nút không hoạt động, vui lòng copy link sau vào trình duyệt:<br>
                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 3px; display: inline-block; margin-top: 5px;">' . $link . '</code>
                </p>
            </div>
            <div class="footer">
                <p>Email này được gửi tự động, vui lòng không reply.</p>
                <p>&copy; 2026 POS System. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

/**
 * Gửi email test (để kiểm tra cấu hình)
 */
function sendTestEmail($to) {
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
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = 'Test Email - POS System';
        $mail->Body = '<h1>Email configuration is working!</h1><p>Your SMTP settings are correct.</p>';
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Test Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

?>
