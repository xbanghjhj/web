<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);
$pageTitle = 'Dashboard Admin';
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
        :root {
            --primary-gradient: linear-gradient(135deg, #7395AE 0%, #557A95 100%);
            --info-gradient: linear-gradient(135deg, #dcfce7 0%, #86efac 100%);
            --warning-gradient: linear-gradient(135deg, #fef9c3 0%, #fde047 100%);
            --primary-soft: rgba(115, 149, 174, 0.1);
            --info-soft: rgba(134, 239, 172, 0.2);
            --warning-soft: rgba(253, 224, 71, 0.2);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 24px;
            margin-right: 18px;
            color: white; /* Revert to white text for original icons */
        }

        .stat-details h3 { font-size: 26px; font-weight: 700; margin-bottom: 2px; }
        .stat-details p { color: #64748b; font-size: 14px; margin: 0; }

        .action-card {
            flex: 1;
            min-width: 200px;
            padding: 24px;
            border-radius: 20px;
            text-decoration: none;
            color: #1e293b;
            background: white;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .action-card:hover {
            background: #f8fafc;
            transform: scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #f1f5f9;
        }

        .table tbody tr { transition: background 0.2s ease; border-bottom: 1px solid #f8fafc; }
        .table tbody tr:hover { background: #f8fafc; }

        /* Skeleton Loading */
        .skeleton {
            background: #f1f5f9;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Ripple Effect */
        .ripple { position: relative; overflow: hidden; }
        .ripple-effect {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to { transform: scale(4); opacity: 0; }
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 4px rgba(115, 149, 174, 0.15);
            border-color: #7395AE;
            transition: all 0.3s ease;
        }
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

        <div class="card mb-4">
            <div class="card-header flex-wrap gap-3">
                <h2 class="card-title"><i class="fas fa-gauge"></i> Tổng quan hoạt động</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <select id="preset" class="form-select" style="min-width: 180px;">
                        <option value="today">Hôm nay</option>
                        <option value="yesterday">Hôm qua</option>
                        <option value="7days">7 ngày qua</option>
                        <option value="this_month">Tháng nay</option>
                        <option value="custom">Tùy chọn</option>
                    </select>
                    <input type="date" id="start-date" class="form-control" style="min-width: 150px;">
                    <input type="date" id="end-date" class="form-control" style="min-width: 150px;">
                    <button type="button" id="apply-filter" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                </div>
            </div>
            <div class="text-muted" id="range-label"></div>
        </div>

        <div class="stats-grid" id="stats-grid"></div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h2 class="card-title"><i class="fas fa-chart-area"></i> Doanh thu theo ngày</h2></div>
                    <canvas id="trend-chart" height="120"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h2 class="card-title"><i class="fas fa-chart-column"></i> Doanh thu theo danh mục</h2></div>
                    <canvas id="bar-chart" height="180"></canvas>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header border-0 pb-0">
                <h2 class="card-title text-muted fw-bold small text-uppercase mb-3"><i class="fas fa-bolt text-warning"></i> Thao tác nhanh</h2>
            </div>
            <div class="d-flex flex-wrap gap-3 p-3 pt-0">
                <a href="<?php echo url('modules/pos/pos.php'); ?>" class="action-card ripple">
                    <div class="action-icon bg-success text-white"><i class="fas fa-cash-register"></i></div>
                    <div><div class="fw-bold">Bán hàng ngay</div><small class="text-muted">Mở trang POS</small></div>
                </a>
                <a href="<?php echo url('modules/employees/add_employee.php'); ?>" class="action-card ripple">
                    <div class="action-icon bg-primary text-white"><i class="fas fa-user-plus"></i></div>
                    <div><div class="fw-bold">Thêm nhân viên</div><small class="text-muted">Quản lý đội ngũ</small></div>
                </a>
                <a href="<?php echo url('modules/products/add_product.php'); ?>" class="action-card ripple">
                    <div class="action-icon bg-primary text-white"><i class="fas fa-box"></i></div>
                    <div><div class="fw-bold">Thêm sản phẩm</div><small class="text-muted">Cập nhật kho</small></div>
                </a>
                <a href="<?php echo url('modules/reports/sales_report.php'); ?>" class="action-card ripple">
                    <div class="action-icon bg-warning text-dark"><i class="fas fa-chart-line"></i></div>
                    <div><div class="fw-bold">Xem báo cáo</div><small class="text-muted">Phân tích dữ liệu</small></div>
                </a>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-table"></i> Đơn hàng gần đây</h2></div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Nhân viên</th>
                            <th>Tổng tiền</th>
                            <th>Lợi nhuận</th>
                            <th>Ngày tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="orders-body"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        let trendChart = null;
        let barChart = null;
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);

        function animateValue(obj, start, end, duration, isMoney = false) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const currentVal = Math.floor(progress * (end - start) + start);
                obj.innerText = isMoney ? formatMoney(currentVal) : currentVal.toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        function statCard(label, value, iconClass, colorClass, isMoney = false, rawValue = 0) {
            return `
                <div class="stat-card">
                    <div class="stat-icon ${colorClass}"><i class="fas ${iconClass}"></i></div>
                    <div class="stat-details">
                        <h3 class="counter" data-value="${rawValue}" data-money="${isMoney}">${value}</h3>
                        <p>${label}</p>
                    </div>
                </div>`;
        }

        function toggleSkeletons(show) {
            if (show) {
                $('#stats-grid').html(Array(4).fill().map(() => `
                    <div class="stat-card">
                        <div class="stat-icon skeleton" style="animation: shimmer 1.5s infinite;"></div>
                        <div class="stat-details w-100">
                            <div class="skeleton mb-2" style="height: 24px; width: 60%;"></div>
                            <div class="skeleton" style="height: 14px; width: 40%;"></div>
                        </div>
                    </div>
                `).join(''));
                $('#orders-body').html(Array(5).fill().map(() => `
                    <tr><td colspan="7"><div class="skeleton" style="height: 20px; width: 100%;"></div></td></tr>
                `).join(''));
            }
        }

        function loadDashboard() {
            toggleSkeletons(true);
            $.getJSON('../reports/report_data.php', {
                preset: $('#preset').val(),
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val()
            }, function (response) {
                if (!response.success) return;

                $('#range-label').text('Khoảng thống kê: ' + response.range.label);
                $('#stats-grid').html([
                    statCard('Doanh thu', formatMoney(response.stats.total_revenue), 'fa-money-bill-wave', 'info', true, response.stats.total_revenue),
                    statCard('Số đơn hàng', response.stats.total_orders, 'fa-receipt', 'primary', false, response.stats.total_orders),
                    statCard('Số sản phẩm', response.stats.total_products, 'fa-box', 'success', false, response.stats.total_products),
                    statCard('Lợi nhuận', formatMoney(response.stats.total_profit || 0), 'fa-chart-pie', 'warning', true, response.stats.total_profit || 0)
                ].join(''));

                // Start counter animation
                $('.counter').each(function() {
                    const $this = $(this);
                    animateValue(this, 0, $this.data('value'), 1200, $this.data('money'));
                });

                if (trendChart) trendChart.destroy();
                if (barChart) barChart.destroy();

                const ctxTrend = document.getElementById('trend-chart').getContext('2d');
                const gradient = ctxTrend.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(115, 149, 174, 0.4)');
                gradient.addColorStop(1, 'rgba(115, 149, 174, 0.0)');

                trendChart = new Chart(ctxTrend, {
                    type: 'line',
                    data: {
                        labels: response.charts.trend_labels,
                        datasets: [{
                            label: 'Doanh thu',
                            data: response.charts.trend_revenue,
                            borderColor: '#7395AE',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#7395AE',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        animation: { duration: 2000, easing: 'easeOutQuart' },
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { display: false } },
                            x: { grid: { display: false } }
                        }
                    }
                });

                barChart = new Chart(document.getElementById('bar-chart'), {
                    type: 'bar',
                    data: {
                        labels: response.charts.bar_labels,
                        datasets: [{ 
                            label: 'Doanh thu', 
                            data: response.charts.bar_values, 
                            backgroundColor: ['#7395AE','#379683','#557A95','#5D5C61','#B1A296','#95a5a6','#7f8c8d','#bdc3c7'], 
                            borderRadius: 10, 
                            maxBarThickness: 40 
                        }]
                    },
                    options: { 
                        indexAxis: 'y',
                        animation: { duration: 1500, delay: 500 },
                        plugins: { legend: { display: false } },
                        scales: { x: { display: false }, y: { grid: { display: false } } }
                    }
                });

                const rows = response.orders.map(order => `
                    <tr>
                        <td><span class="badge bg-light text-dark border p-2">${order.order_code}</span></td>
                        <td><div class="fw-bold">${order.customer_name}</div><small class="text-muted">${order.phone}</small></td>
                        <td>${order.staff_name}</td>
                        <td class="fw-bold">${formatMoney(order.total_amount)}</td>
                        <td class="text-success">${formatMoney(order.total_profit || 0)}</td>
                        <td><small class="text-muted">${order.created_at}</small></td>
                        <td class="text-end"><a href="../orders/view_order.php?id=${order.id}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">Chi tiết</a></td>
                    </tr>
                `).join('');
                $('#orders-body').html(rows || '<tr><td colspan="7" class="text-center py-5 text-muted">Không có đơn hàng nào trong khoảng thời gian này.</td></tr>');
            });
        }

        // Ripple Effect
        $(document).on('click', '.ripple', function(e) {
            const $this = $(this);
            const $ripple = $('<span class="ripple-effect"></span>');
            const offset = $this.offset();
            const x = e.pageX - offset.left;
            const y = e.pageY - offset.top;
            
            $ripple.css({ top: y, left: x });
            $this.append($ripple);
            
            setTimeout(() => $ripple.remove(), 600);
        });

        $('#apply-filter').on('click', loadDashboard);
        $('#preset').on('change', function () {
            const isCustom = $(this).val() === 'custom';
            $('#start-date, #end-date').prop('disabled', !isCustom);
        }).trigger('change');
        loadDashboard();
    </script>
</body>
</html>
