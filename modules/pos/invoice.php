<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$orderId = (int) ($_GET['id'] ?? 0);
$order = fetchOne(
    'SELECT o.*, c.name AS customer_name, c.phone, c.address, u.full_name AS staff_name FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = o.staff_id WHERE o.id = ? LIMIT 1',
    'i',
    [$orderId]
);

if (!$order) {
    setFlashMessage('error', 'Không tìm thấy hóa đơn.');
    redirect(routeByRole());
}

if (isStaff() && (int) $order['staff_id'] !== (int) $_SESSION['user_id']) {
    setFlashMessage('error', 'Bạn không có quyền xem hóa đơn này.');
    redirect(url('modules/orders/list_orders.php'));
}

$orderDetails = fetchAll('SELECT * FROM order_details WHERE order_id = ? ORDER BY id ASC', 'i', [$orderId]);
$pdfRequested = cleanText($_GET['format'] ?? '') === 'pdf';
$pdfAvailable = file_exists(BASE_PATH . '/libs/fpdf/fpdf.php') || file_exists(BASE_PATH . '/libs/tcpdf/tcpdf.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn <?php echo e($order['order_code']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f8fafc; }
        .invoice-card { max-width: 900px; margin: 32px auto; background: #fff; border-radius: 20px; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); padding: 32px; }
        @media print { .no-print { display: none !important; } body { background: #fff; } .invoice-card { box-shadow: none; margin: 0; max-width: 100%; } }
    </style>
</head>
<body>
    <div class="invoice-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h2 class="mb-1">POS System</h2>
                <div>Cửa hàng điện thoại và phụ kiện</div>
                <div class="text-muted">Hóa đơn bán hàng</div>
            </div>
            <div class="text-end">
                <div><strong>Mã đơn:</strong> <?php echo e($order['order_code']); ?></div>
                <div><strong>Ngày giờ:</strong> <?php echo e(formatDateTime($order['created_at'])); ?></div>
                <div><strong>Nhân viên:</strong> <?php echo e($order['staff_name']); ?></div>
            </div>
        </div>

        <?php if ($pdfRequested && !$pdfAvailable): ?>
            <div class="alert alert-warning no-print">Thư viện PDF chưa có sẵn trong dự án. Hệ thống đang hiển thị bản in HTML.</div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Thông tin khách hàng</h5>
                <div><strong>Tên:</strong> <?php echo e($order['customer_name']); ?></div>
                <div><strong>SDT:</strong> <?php echo e($order['phone']); ?></div>
                <div><strong>Địa chỉ:</strong> <?php echo e($order['address']); ?></div>
            </div>
            <div class="col-md-6">
                <h5>Thông tin thanh toán</h5>
                <div><strong>Tổng tiền:</strong> <?php echo e(formatMoney($order['total_amount'])); ?></div>
                <div><strong>Tiền khách đưa:</strong> <?php echo e(formatMoney($order['customer_paid'])); ?></div>
                <div><strong>Tiền trả lại:</strong> <?php echo e(formatMoney($order['change_amount'])); ?></div>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-end">Đơn giá</th>
                        <th class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderDetails as $detail): ?>
                        <tr>
                            <td><?php echo e($detail['product_name']); ?></td>
                            <td class="text-center"><?php echo e((int) $detail['quantity']); ?></td>
                            <td class="text-end"><?php echo e(formatMoney($detail['price'])); ?></td>
                            <td class="text-end"><?php echo e(formatMoney($detail['subtotal'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center no-print">
            <a href="<?php echo url('modules/orders/view_order.php?id=' . $orderId); ?>" class="btn btn-outline-secondary">Quay lại đơn hàng</a>
            <div class="d-flex gap-2">
                <a href="?id=<?php echo $orderId; ?>&format=pdf" class="btn btn-outline-primary" style="display: none;">Tải PDF</a>
                <button type="button" onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> In hóa đơn / Lưu PDF</button>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['auto_print']) && $_GET['auto_print'] == '1'): ?>
    <script>
        // Tự động kích hoạt hộp thoại in của trình duyệt để mô phỏng xuất PDF
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500); // Đợi 1 chút để DOM và css render hoàn tất
        });
    </script>
    <?php endif; ?>
</body>
</html>
