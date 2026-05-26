<?php
require_once '../../config/config.php';

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn.'], 401);
}

if (!in_array($_SESSION['role'] ?? '', [ROLE_ADMIN, ROLE_STAFF], true)) {
    jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập.'], 403);
}

function resolveReportRange($preset, $startDate, $endDate) {
    $today = new DateTime('today');
    $start = clone $today;
    $end = clone $today;

    switch ($preset) {
        case 'yesterday':
            $start->modify('-1 day');
            $end->modify('-1 day');
            break;
        case '7days':
            $start->modify('-6 days');
            break;
        case 'this_month':
            $start = new DateTime(date('Y-m-01'));
            $end = new DateTime(date('Y-m-t'));
            break;
        case 'custom':
            if ($startDate && $endDate) {
                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
            }
            break;
        case 'today':
        default:
            break;
    }

    if ($start > $end) {
        [$start, $end] = [$end, $start];
    }

    return [
        'start_date' => $start->format('Y-m-d'),
        'end_date' => $end->format('Y-m-d'),
        'label' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
    ];
}

$preset = cleanText($_GET['preset'] ?? 'today');
$range = resolveReportRange($preset, $_GET['start_date'] ?? null, $_GET['end_date'] ?? null);
$isAdminUser = isAdmin();

$conditions = ['DATE(o.created_at) BETWEEN ? AND ?'];
$types = 'ss';
$params = [$range['start_date'], $range['end_date']];

if (!$isAdminUser) {
    $conditions[] = 'o.staff_id = ?';
    $types .= 'i';
    $params[] = (int) $_SESSION['user_id'];
}

$whereSql = 'WHERE ' . implode(' AND ', $conditions);

$summary = fetchOne(
    "SELECT COUNT(DISTINCT o.id) AS total_orders, COALESCE(SUM(od.quantity), 0) AS total_products, COALESCE(SUM(od.subtotal), 0) AS total_revenue, COALESCE(SUM((od.price - od.price_buy) * od.quantity), 0) AS total_profit FROM orders o JOIN order_details od ON od.order_id = o.id {$whereSql}",
    $types,
    $params
);

$trend = fetchAll(
    "SELECT DATE(o.created_at) AS sale_date, COUNT(DISTINCT o.id) AS total_orders, COALESCE(SUM(od.subtotal), 0) AS total_revenue FROM orders o JOIN order_details od ON od.order_id = o.id {$whereSql} GROUP BY DATE(o.created_at) ORDER BY sale_date ASC",
    $types,
    $params
);

$categoryBars = fetchAll(
    "SELECT c.name AS label, COALESCE(SUM(od.subtotal), 0) AS revenue FROM order_details od JOIN orders o ON o.id = od.order_id JOIN products p ON p.id = od.product_id JOIN categories c ON c.id = p.category_id {$whereSql} GROUP BY c.id, c.name ORDER BY revenue DESC LIMIT 8",
    $types,
    $params
);

$orderQuery = "SELECT o.id, o.order_code, o.total_amount, o.customer_paid, o.change_amount, o.created_at, c.name AS customer_name, c.phone, u.full_name AS staff_name";
if ($isAdminUser) {
    $orderQuery .= ", (SELECT COALESCE(SUM((od2.price - od2.price_buy) * od2.quantity), 0) FROM order_details od2 WHERE od2.order_id = o.id) AS total_profit";
}
$orderQuery .= " FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = o.staff_id {$whereSql} ORDER BY o.created_at DESC LIMIT 50";
$orders = fetchAll($orderQuery, $types, $params);

foreach ($orders as &$order) {
    $order['created_at'] = formatDateTime($order['created_at']);
}
unset($order);

$response = [
    'success' => true,
    'is_admin' => $isAdminUser,
    'range' => $range,
    'stats' => [
        'total_orders' => (int) ($summary['total_orders'] ?? 0),
        'total_products' => (int) ($summary['total_products'] ?? 0),
        'total_revenue' => (float) ($summary['total_revenue'] ?? 0),
    ],
    'charts' => [
        'trend_labels' => array_map(static fn($row) => formatDate($row['sale_date']), $trend),
        'trend_revenue' => array_map(static fn($row) => (float) $row['total_revenue'], $trend),
        'bar_labels' => array_map(static fn($row) => $row['label'], $categoryBars),
        'bar_values' => array_map(static fn($row) => (float) $row['revenue'], $categoryBars),
    ],
    'orders' => $orders,
];

if ($isAdminUser) {
    $response['stats']['total_profit'] = (float) ($summary['total_profit'] ?? 0);
}

jsonResponse($response);

