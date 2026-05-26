<?php
function posEnsureCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function posGetCartItems() {
    posEnsureCart();
    return array_values($_SESSION['cart']);
}

function posCalculateCartTotals() {
    $items = posGetCartItems();
    $totalAmount = 0;
    $totalQuantity = 0;

    foreach ($items as $item) {
        $totalAmount += (float) $item['subtotal'];
        $totalQuantity += (int) $item['quantity'];
    }

    return [
        'items' => $items,
        'total_amount' => $totalAmount,
        'total_quantity' => $totalQuantity,
    ];
}

function posAddToCart($product, $quantity = 1) {
    posEnsureCart();

    $productId = (int) $product['id'];
    $existingQuantity = isset($_SESSION['cart'][$productId]) ? (int) $_SESSION['cart'][$productId]['quantity'] : 0;
    $newQuantity = max(1, $existingQuantity + (int) $quantity);

    $_SESSION['cart'][$productId] = [
        'product_id' => $productId,
        'barcode' => $product['barcode'],
        'name' => $product['name'],
        'price' => (float) $product['price_sell'],
        'price_buy' => (float) $product['price_buy'],
        'quantity' => $newQuantity,
        'subtotal' => $newQuantity * (float) $product['price_sell'],
    ];

    return posCalculateCartTotals();
}

function posUpdateCartQuantity($productId, $quantity) {
    posEnsureCart();
    $productId = (int) $productId;
    $quantity = (int) $quantity;

    if (!isset($_SESSION['cart'][$productId])) {
        return posCalculateCartTotals();
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return posCalculateCartTotals();
    }

    $_SESSION['cart'][$productId]['quantity'] = $quantity;
    $_SESSION['cart'][$productId]['subtotal'] = $quantity * (float) $_SESSION['cart'][$productId]['price'];

    return posCalculateCartTotals();
}

function posRemoveFromCart($productId) {
    posEnsureCart();
    unset($_SESSION['cart'][(int) $productId]);
    return posCalculateCartTotals();
}

function posClearCart() {
    $_SESSION['cart'] = [];
}

function posGenerateOrderCode() {
    $today = date('Ymd');
    $row = fetchOne(
        "SELECT order_code FROM orders WHERE order_code LIKE ? ORDER BY order_code DESC LIMIT 1",
        's',
        ['ORD' . $today . '%']
    );

    $nextNumber = 1;
    if (!empty($row['order_code'])) {
        $nextNumber = (int) substr($row['order_code'], -3) + 1;
    }

    return 'ORD' . $today . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
}
