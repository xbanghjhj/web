<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

if (!file_exists(BASE_PATH . '/libs/fpdf/fpdf.php')) {
    die('Thư viện FPDF không tồn tại! Vui lòng kiểm tra thư mục /libs/fpdf/');
}
require(BASE_PATH . '/libs/fpdf/fpdf.php');

$orderId = (int) ($_GET['id'] ?? 0);
$order = fetchOne(
    'SELECT o.*, c.name AS customer_name, c.phone, c.address, u.full_name AS staff_name 
    FROM orders o 
    JOIN customers c ON c.id = o.customer_id 
    JOIN users u ON u.id = o.staff_id 
    WHERE o.id = ? LIMIT 1',
    'i',
    [$orderId]
);

if (!$order) {
    die('Không tìm thấy hóa đơn.');
}

if (isStaff() && (int) $order['staff_id'] !== (int) $_SESSION['user_id']) {
    die('Bạn không có quyền in hóa đơn này.');
}

$orderDetails = fetchAll('SELECT * FROM order_details WHERE order_id = ? ORDER BY id ASC', 'i', [$orderId]);

// Hàm loại bỏ dấu tiếng Việt để xuất FPDF chuẩn (vì FPDF cơ bản không hỗ trợ UTF-8 trọn vẹn)
function removeVietnameseAccents($str) {
    if (!$str) return '';
    $accents = [
        'a' => ['á','à','ả','ã','ạ','ă','ắ','ằ','ẳ','ẵ','ặ','â','ấ','ầ','ẩ','ẫ','ậ'],
        'A' => ['Á','À','Ả','Ã','Ạ','Ă','Ắ','Ằ','Ẳ','Ẵ','Ặ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ'],
        'd' => ['đ'],
        'D' => ['Đ'],
        'e' => ['é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ'],
        'E' => ['É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ'],
        'i' => ['í','ì','ỉ','ĩ','ị'],
        'I' => ['Í','Ì','Ỉ','Ĩ','Ị'],
        'o' => ['ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ'],
        'O' => ['Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ'],
        'u' => ['ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự'],
        'U' => ['Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự'],
        'y' => ['ý','ỳ','ỷ','ỹ','ỵ'],
        'Y' => ['Ý','Ỳ','Ỷ','Ỹ','Ỵ'],
    ];
    foreach ($accents as $nonAccent => $accentArray) {
        $str = str_replace($accentArray, $nonAccent, $str);
    }
    return $str;
}

$pdf = new FPDF('P', 'mm', 'A5'); // Khổ A5 cho hóa đơn POS
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'HOA DON BAN HANG', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Cua hang: POS PHONE SYSTEM', 0, 1, 'L');
$pdf->Cell(0, 8, 'Ma don: ' . $order['order_code'], 0, 1, 'L');
$pdf->Cell(0, 8, 'Ky thuat vien: ' . removeVietnameseAccents($order['staff_name']), 0, 1, 'L');
$pdf->Cell(0, 8, 'Ngay tao: ' . formatDateTime($order['created_at']), 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, 'Thong tin khach hang:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Ten: ' . removeVietnameseAccents($order['customer_name']), 0, 1, 'L');
$pdf->Cell(0, 6, 'SDT: ' . $order['phone'], 0, 1, 'L');
$pdf->Cell(0, 6, 'Dia chi: ' . removeVietnameseAccents($order['address']), 0, 1, 'L');
$pdf->Ln(5);

// Bảng sản phẩm
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(65, 8, 'Ten SP', 1);
$pdf->Cell(15, 8, 'SL', 1, 0, 'C');
$pdf->Cell(25, 8, 'Don gia', 1, 0, 'R');
$pdf->Cell(25, 8, 'Thanh tien', 1, 1, 'R');

$pdf->SetFont('Arial', '', 10);
foreach ($orderDetails as $detail) {
    // Rút gọn tên SP nếu quá dài
    $pName = substr(removeVietnameseAccents($detail['product_name']), 0, 28);
    $pdf->Cell(65, 8, $pName, 1);
    $pdf->Cell(15, 8, $detail['quantity'], 1, 0, 'C');
    $pdf->Cell(25, 8, number_format($detail['price']), 1, 0, 'R');
    $pdf->Cell(25, 8, number_format($detail['subtotal']), 1, 1, 'R');
}

$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(95, 8, 'Tong tien:', 0, 0, 'R');
$pdf->Cell(35, 8, number_format($order['total_amount']) . ' VND', 0, 1, 'R');

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(95, 8, 'Tien khach dua:', 0, 0, 'R');
$pdf->Cell(35, 8, number_format($order['customer_paid']) . ' VND', 0, 1, 'R');

$pdf->Cell(95, 8, 'Tien tra lai:', 0, 0, 'R');
$pdf->Cell(35, 8, number_format($order['change_amount']) . ' VND', 0, 1, 'R');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 6, 'Cam on quy khach da mua hang!', 0, 1, 'C');
$pdf->Cell(0, 6, 'Hen gap lai!', 0, 1, 'C');

// Xuất file PDF rực tiếp trên trình duyệt
$pdf->Output('I', 'HoaDon_' . $order['order_code'] . '.pdf');
