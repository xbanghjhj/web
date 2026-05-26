<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';
require_once 'cart_helpers.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$pageTitle = 'POS - Bán hàng';
$currentUser = getCurrentUser();
$cart = posCalculateCartTotals();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - POS System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <style>
        .pos-shell { display: grid; grid-template-columns: 1.25fr 0.95fr; gap: 24px; }
        .search-results { max-height: 320px; overflow-y: auto; }
        .search-item { cursor: pointer; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; margin-bottom: 10px; transition: 0.2s ease; }
        .search-item:hover { border-color: #7395AE; background: #f8faff; }
        .cart-row { border-bottom: 1px solid #eef2f7; padding: 14px 0; transition: background-color 0.5s ease; }
        .summary-box { border-radius: 18px; background: linear-gradient(135deg, #eff6ff 0%, #eef2ff 100%); padding: 20px; }
        .item-flash { background-color: #dcfce7 !important; }
        @keyframes flash-green { 0% { background-color: #dcfce7; } 100% { background-color: transparent; } }
        .animate-flash { animation: flash-green 1s ease-out; }
        
        /* Custom Hand-coded Modal */
        #custom-success-modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden; transition: all 0.3s ease;
        }
        #custom-success-modal.show { opacity: 1; visibility: visible; }
        .custom-modal-content {
            background: #fff; padding: 30px 40px; border-radius: 16px;
            text-align: center; transform: translateY(-30px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            min-width: 350px;
        }
        #custom-success-modal.show .custom-modal-content { 
            transform: translateY(0) scale(1); 
        }
        .success-check-icon {
            width: 70px; height: 70px; background: #10b981; color: white;
            font-size: 35px; line-height: 70px; border-radius: 50%;
            margin: 0 auto 20px auto;
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
        }

        @media (max-width: 992px) { .pos-shell { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>

    <div class="main-content">
        <?php $flashMessage = getFlashMessage(); ?>
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?php echo e($flashMessage['type']); ?>">
                <i class="fas fa-info-circle"></i>
                <span><?php echo e($flashMessage['message']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Custom Hand-coded Success Modal -->
        <div id="custom-success-modal">
            <div class="custom-modal-content">
                <div class="success-check-icon"><i class="fas fa-check"></i></div>
                <h3 style="color: #111827; font-weight: 700; margin-bottom: 8px;">Thanh toán thành công!</h3>
                <p style="color: #6b7280; font-size: 15px; margin-bottom: 24px;">Đơn hàng đã được ghi nhận.</p>
                
                <div class="d-flex gap-2">
                    <button type="button" id="close-success-modal" class="btn btn-outline-secondary w-50" style="border-radius: 12px; font-weight: 600;">Đóng</button>
                    <a href="#" id="print-invoice-btn" target="_blank" class="btn btn-success w-50 d-flex align-items-center justify-content-center" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-print me-2"></i> In hóa đơn
                    </a>
                </div>
            </div>
        </div>

        <div class="pos-shell">
            <div>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-barcode"></i> Tìm sản phẩm hoặc quét mã vạch</h2>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <input type="text" id="product-search" class="form-control form-control-lg" placeholder="Nhập tên sản phẩm hoặc barcode..." autocomplete="off">
                        </div>
                        <div class="col-md-4 d-grid">
                            <button type="button" id="clear-cart" class="btn btn-outline-danger btn-lg"><i class="fas fa-trash"></i> Xóa giỏ</button>
                        </div>
                    </div>
                    <div id="search-results" class="search-results"></div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-shopping-cart"></i> Danh sách chờ thanh toán</h2>
                    </div>
                    <div id="cart-container"></div>
                </div>
            </div>

            <div>
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-user"></i> Khách hàng</h2>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" id="customer-phone" class="form-control" placeholder="Nhập số điện thoại khách hàng" autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <div id="customer-status" class="small text-muted">Nhập số điện thoại để kiểm tra khách cũ hoặc tạo khách mới.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" id="customer-name" class="form-control" placeholder="Tự động điền nếu là khách cũ" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" id="customer-address" class="form-control" placeholder="Nhập địa chỉ khách hàng" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-wallet"></i> Thanh toán</h2>
                    </div>
                    <div class="summary-box mb-3">
                        <div class="d-flex justify-content-between mb-2"><span>Tổng số lượng</span><strong id="summary-quantity"><?php echo (int) $cart['total_quantity']; ?></strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>Tổng tiền</span><strong id="summary-total"><?php echo e(formatMoney($cart['total_amount'])); ?></strong></div>
                        <div class="d-flex justify-content-between"><span>Nhân viên</span><strong><?php echo e($currentUser['full_name']); ?></strong></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tiền khách đưa</label>
                            <input type="number" id="customer-paid" class="form-control" min="0" step="1000" placeholder="Nhập số tiền khách đưa" autocomplete="new-password">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tiền trả lại</label>
                            <input type="text" id="change-amount" class="form-control" value="0 VND" readonly>
                        </div>
                        <div class="col-12 d-grid gap-2">
                            <button type="button" id="checkout-btn" class="btn btn-success btn-lg"><i class="fas fa-cash-register"></i> Thanh toán</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
        let currentCustomer = null;

        function renderSearchResults(products) {
            const container = $('#search-results');
            if (!products.length) {
                container.html('<div class="text-muted">Không tìm thấy sản phẩm phù hợp.</div>');
                return;
            }

            container.html(products.map(product => `
                <div class="search-item" data-id="${product.id}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <strong>${product.name}</strong><br>
                            <small class="text-muted">Barcode: ${product.barcode}</small>
                        </div>
                        <strong>${formatMoney(product.price_sell)}</strong>
                    </div>
                    <div class="text-muted mt-2">Danh mục: ${product.category_name}</div>
                </div>
            `).join(''));
        }

        function loadCart(highlightId = null) {
            $.post('cart_api.php', { action: 'get' }, function (response) {
                if (response.success) {
                    renderCart(response.cart, highlightId);
                }
            }, 'json');
        }

        function renderCart(cart, highlightId = null) {
            const container = $('#cart-container');
            $('#summary-total').text(formatMoney(cart.total_amount));
            $('#summary-quantity').text(cart.total_quantity);
            updateChangeAmount();

            if (!cart.items.length) {
                container.html('<div class="text-center py-5 text-muted">Giỏ hàng đang trống.</div>');
                return;
            }

            container.html(cart.items.map(item => `
                <div class="cart-row ${highlightId == item.product_id ? 'animate-flash' : ''}" data-id="${item.product_id}">
                    <div class="d-flex justify-content-between gap-3 flex-wrap">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small class="text-muted">${item.barcode}</small>
                        </div>
                        <div class="text-end">
                            <div><strong>${formatMoney(item.subtotal)}</strong></div>
                            <div class="text-muted">${formatMoney(item.price)} / sp</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                        <input type="number" class="form-control form-control-sm cart-qty" data-id="${item.product_id}" min="1" value="${item.quantity}" style="max-width: 120px;">
                        <button type="button" class="btn btn-sm btn-outline-danger cart-remove" data-id="${item.product_id}"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            `).join(''));
        }

        function addToCart(params) {
            $.post('cart_api.php', { action: 'add', ...params }, function (response) {
                if (response.success) {
                    const productId = params.product_id || (response.cart.items.find(i => i.barcode === params.barcode)?.product_id);
                    renderCart(response.cart, productId);
                    
                    // Visual feedback in search results if still exists
                    if (params.product_id) {
                        $(`.search-item[data-id="${params.product_id}"]`).addClass('item-flash');
                        setTimeout(() => $(`.search-item[data-id="${params.product_id}"]`).removeClass('item-flash'), 1000);
                    }

                    $('#product-search').val('').trigger('input').focus();
                } else {
                    alert(response.message || 'Không thể thêm sản phẩm vào giỏ.');
                }
            }, 'json');
        }

        function updateChangeAmount() {
            const paid = parseFloat($('#customer-paid').val() || 0);
            const totalText = $('#summary-total').text().replace(/[^0-9]/g, '');
            const total = parseFloat(totalText || 0);
            $('#change-amount').val(formatMoney(Math.max(0, paid - total)));
        }

        function lookupCustomer() {
            const phone = $('#customer-phone').val().trim();
            if (!phone) {
                currentCustomer = null;
                $('#customer-status').text('Nhap so dien thoai de kiem tra khach cu hoac tao khach moi.');
                $('#customer-name').val('');
                $('#customer-address').val('');
                return;
            }

            $.getJSON('customer_lookup.php', { phone }, function (response) {
                if (!response.success) {
                    $('#customer-status').text(response.message || 'Không tìm thấy khách hàng.');
                    return;
                }

                currentCustomer = response.customer;
                if (response.customer.exists) {
                    $('#customer-status').html(`<span class="text-success">Khách cũ: ${response.customer.name}. Có ${response.customer.total_orders} đơn hàng.</span>`);
                    $('#customer-name').val(response.customer.name);
                    $('#customer-address').val(response.customer.address);
                } else {
                    $('#customer-status').html('<span class="text-warning">Số điện thoại chưa tồn tại. Vui lòng nhập họ tên và địa chỉ để tạo khách khi thanh toán.</span>');
                    $('#customer-name').val('');
                    $('#customer-address').val('');
                }
            });
        }

        $('#product-search').on('input', function () {
            const keyword = $(this).val().trim();
            if (keyword.length < 2) {
                $('#search-results').html('');
                return;
            }

            $.getJSON('find_product.php', { q: keyword }, function (response) {
                renderSearchResults(response.products || []);
            });
        });

        $('#product-search').on('keydown', function (e) {
            if (e.key === 'Enter') {
                const keyword = $(this).val().trim();
                if (!keyword) return;

                // 1. Try to add by exact barcode match
                $.post('cart_api.php', { action: 'add', barcode: keyword }, function (response) {
                    if (response.success) {
                        const productId = response.cart.items.find(i => i.barcode === keyword)?.product_id;
                        renderCart(response.cart, productId);
                        $('#product-search').val('').trigger('input').focus();
                    } else {
                        // 2. If barcode fails, try to add the first search result if there's only one or many
                        const firstItem = $('#search-results .search-item').first();
                        if (firstItem.length) {
                            addToCart({ product_id: firstItem.data('id') });
                        } else {
                            alert('Không tìm thấy sản phẩm với mã hoặc tên này.');
                        }
                    }
                }, 'json');
            }
        });

        $('#search-results').on('click', '.search-item', function () {
            addToCart({ product_id: $(this).data('id') });
        });

        $('#cart-container').on('change', '.cart-qty', function () {
            $.post('cart_api.php', { action: 'update', product_id: $(this).data('id'), quantity: $(this).val() }, function (response) {
                if (response.success) {
                    renderCart(response.cart);
                }
            }, 'json');
        });

        $('#cart-container').on('click', '.cart-remove', function () {
            $.post('cart_api.php', { action: 'remove', product_id: $(this).data('id') }, function (response) {
                if (response.success) {
                    renderCart(response.cart);
                }
            }, 'json');
        });

        $('#clear-cart').on('click', function () {
            if (!confirm('Xóa toàn bộ giỏ hàng hiện tại?')) return;
            $.post('cart_api.php', { action: 'clear' }, function (response) {
                if (response.success) {
                    renderCart(response.cart);
                }
            }, 'json');
        });

        $('#customer-phone').on('change blur', lookupCustomer);
        $('#customer-paid').on('input', updateChangeAmount);

        $('#checkout-btn').on('click', function () {
            const payload = {
                phone: $('#customer-phone').val().trim(),
                name: $('#customer-name').val().trim(),
                address: $('#customer-address').val().trim(),
                customer_paid: $('#customer-paid').val()
            };

            $.post('checkout.php', payload, function (response) {
                if (!response.success) {
                    alert(response.message || 'Thanh toán thất bại.');
                    return;
                }

                renderCart(response.cart);
                $('#customer-paid').val('');
                $('#change-amount').val('0 VND');
                $('#customer-phone, #customer-name, #customer-address, #product-search').val('');
                $('#customer-status').text('Thanh toán thành công. Bạn có thể phục vụ lượt khách tiếp theo.');
                
                // Gắn link hóa đơn PDF vào nút trong modal
                document.getElementById('print-invoice-btn').href = response.invoice_url;
                
                // Hiển thị modal code tay
                const successModal = document.getElementById('custom-success-modal');
                successModal.classList.add('show');

                // Xử lý nút đóng modal
                document.getElementById('close-success-modal').addEventListener('click', function() {
                    successModal.classList.remove('show');
                });
            }, 'json');
        });

        loadCart();
    </script>
</body>
</html>
