<?php
require_once '../../config/config.php';
require_once 'cart_helpers.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
}

if (!in_array($_SESSION['role'] ?? '', [ROLE_ADMIN, ROLE_STAFF], true)) {
    jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403);
}

if (mustChangePassword()) {
    jsonResponse(['success' => false, 'message' => 'Bạn phải đổi mật khẩu trước khi sử dụng chức năng này.'], 403);
}

$cart = posCalculateCartTotals();
if (empty($cart['items'])) {
    jsonResponse(['success' => false, 'message' => 'Giỏ hàng đang trống.'], 422);
}

$phone = normalizePhone($_POST['phone'] ?? '');
$name = cleanText($_POST['name'] ?? '');
$address = cleanText($_POST['address'] ?? '');
$customerPaid = (float) ($_POST['customer_paid'] ?? 0);

if (!isValidPhoneNumber($phone)) {
    jsonResponse(['success' => false, 'message' => 'Số điện thoại không hợp lệ.'], 422);
}

if ($customerPaid < $cart['total_amount']) {
    jsonResponse(['success' => false, 'message' => 'Tiền khách đưa không đủ.'], 422);
}

$conn = getDbConnection();
$conn->begin_transaction();

try {
    $customer = fetchOne('SELECT * FROM customers WHERE phone = ? LIMIT 1', 's', [$phone]);

    if ($customer) {
        $customerId = (int) $customer['id'];
        if ($name !== '' || $address !== '') {
            executeQuery('UPDATE customers SET name = ?, address = ? WHERE id = ?', 'ssi', [
                $name !== '' ? $name : $customer['name'],
                $address !== '' ? $address : $customer['address'],
                $customerId,
            ]);
        }
    } else {
        if ($name === '' || $address === '') {
            throw new RuntimeException('Khách mới cần nhập đầy đủ họ tên và địa chỉ.');
        }

        $insertCustomer = executeQuery('INSERT INTO customers (phone, name, address) VALUES (?, ?, ?)', 'sss', [$phone, $name, $address]);
        if (!$insertCustomer) {
            throw new RuntimeException('Không thể tạo khách hàng mới.');
        }
        $customerId = $conn->insert_id;
    }

    $orderCode = posGenerateOrderCode();
    $changeAmount = $customerPaid - $cart['total_amount'];
    $insertOrder = executeQuery(
        'INSERT INTO orders (order_code, customer_id, staff_id, total_amount, customer_paid, change_amount, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)',
        'siiddds',
        [$orderCode, $customerId, (int) $_SESSION['user_id'], $cart['total_amount'], $customerPaid, $changeAmount, date('Y-m-d')]
    );

    if (!$insertOrder) {
        throw new RuntimeException('Không thể tạo đơn hàng.');
    }

    $orderId = $conn->insert_id;
    foreach ($cart['items'] as $item) {
        $detail = executeQuery(
            'INSERT INTO order_details (order_id, product_id, product_name, quantity, price, price_buy, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'iisiddd',
            [
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['quantity'],
                $item['price'],
                $item['price_buy'],
                $item['subtotal'],
            ]
        );

        if (!$detail) {
            throw new RuntimeException('Không thể lưu chi tiết đơn hàng.');
        }
    }

    $conn->commit();
    posClearCart();

    jsonResponse([
        'success' => true,
        'message' => 'Thanh toán thành công.',
        'cart' => ['items' => [], 'total_amount' => 0, 'total_quantity' => 0],
        'order_id' => $orderId,
        'invoice_url' => url('modules/pos/in_hoa_don.php?id=' . $orderId),
    ]);
} catch (Throwable $throwable) {
    $conn->rollback();
    error_log('Checkout failed: ' . $throwable->getMessage());
    jsonResponse(['success' => false, 'message' => $throwable->getMessage()], 422);
}
