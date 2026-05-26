<?php
require_once __DIR__ . '/config/config.php';

echo "Bắt đầu thêm dữ liệu doanh thu mẫu cho hôm nay...\n";

// 1. Lấy 1 nhân viên (hoặc admin)
$staff = fetchOne("SELECT id FROM users ORDER BY id ASC LIMIT 1");
if (!$staff) {
    die("Lỗi: Không tìm thấy nhân viên nào trong hệ thống!\n");
}
$staffId = $staff['id'];

// 2. Lấy 3 sản phẩm mẫu
$products = fetchAll("SELECT id, name, price_sell, price_buy FROM products LIMIT 3");
if (count($products) < 1) {
    die("Lỗi: Không tìm thấy sản phẩm nào trong hệ thống. Vui lòng thêm sản phẩm trước!\n");
}

// 3. Khách hàng mẫu (Khách lẻ hoặc tạo mới)
$customer = fetchOne("SELECT id FROM customers LIMIT 1");
if (!$customer) {
    echo "Đang tạo khách hàng mẫu...\n";
    executeQuery("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)", 'sss', ['Khách hàng vãng lai', '0901234567', 'Hà Nội']);
    $customerId = getDbConnection()->insert_id;
} else {
    $customerId = $customer['id'];
}

$todayDate = date('Y-m-d');
$todayPrefix = 'ORD' . date('Ymd');
$count = 0;

for ($i = 1; $i <= 5; $i++) {
    // Randomize amount of items
    $numItems = rand(1, 3);
    $totalAmount = 0;
    
    // Prefix order code
    $orderCode = $todayPrefix . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
    
    // Insert order first to get ID (will update total later)
    $stmt = executeQuery(
        "INSERT INTO orders (order_code, customer_id, staff_id, total_amount, customer_paid, change_amount, order_date) VALUES (?, ?, ?, 0, 0, 0, ?)",
        'siis',
        [$orderCode, $customerId, $staffId, $todayDate]
    );
    
    if (!$stmt) {
        echo "Lỗi khi tạo đơn hàng $orderCode\n";
        continue;
    }
    $orderId = getDbConnection()->insert_id;
    
    // Insert items
    for ($j = 0; $j < $numItems; $j++) {
        $product = $products[array_rand($products)];
        $quantity = rand(1, 2);
        $price = $product['price_sell'];
        $priceBuy = $product['price_buy'];
        $subtotal = $price * $quantity;
        $totalAmount += $subtotal;
        
        executeQuery(
            "INSERT INTO order_details (order_id, product_id, product_name, quantity, price, price_buy, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)",
            'iisiddd',
            [$orderId, $product['id'], $product['name'], $quantity, $price, $priceBuy, $subtotal]
        );
    }
    
    // Update order total
    $customerPaid = $totalAmount;
    executeQuery(
        "UPDATE orders SET total_amount = ?, customer_paid = ? WHERE id = ?",
        'ddi',
        [$totalAmount, $customerPaid, $orderId]
    );
    
    // Update customer stats
    executeQuery(
        "UPDATE customers SET total_spent = total_spent + ?, total_orders = total_orders + 1 WHERE id = ?",
        'di',
        [$totalAmount, $customerId]
    );

    $count++;
    echo "Đã tạo đơn hàng: $orderCode (Tổng: " . number_format($totalAmount) . " VNĐ)\n";
}

echo "Hoàn thành! Đã tạo $count đơn hàng mẫu cho ngày hôm nay ($todayDate).\n";
