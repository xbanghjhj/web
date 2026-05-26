<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);
$pageTitle = isAdmin() ? 'Báo cáo doanh thu và lợi nhuận' : 'Báo cáo doanh số cá nhân';
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
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>
    <?php include '../../includes/header.php'; ?>
    <div class="main-content">
        <div class="card mb-4">
            <div class="card-header flex-wrap gap-3">
                <h2 class="card-title"><i class="fas fa-chart-line"></i> <?php echo e($pageTitle); ?></h2>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Mốc thời gian</label>
                    <select id="preset" class="form-select">
                        <option value="today">Hôm nay</option>
                        <option value="yesterday">Hôm qua</option>
                        <option value="7days">7 ngày qua</option>
                        <option value="this_month">Tháng này</option>
                        <option value="custom">Tùy chọn</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" id="start-date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" id="end-date" class="form-control">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="button" id="apply-filter" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
                </div>
            </div>
            <div class="text-muted mt-3" id="range-label"></div>
        </div>

        <div class="stats-grid" id="stats-grid"></div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h2 class="card-title"><i class="fas fa-chart-area"></i> Xu hướng doanh thu</h2></div>
                    <canvas id="trend-chart" height="120"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h2 class="card-title"><i class="fas fa-chart-bar"></i> Doanh thu theo danh mục</h2></div>
                    <canvas id="bar-chart" height="180"></canvas>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-table"></i> Danh sách đơn hàng</h2></div>
            <div class="table-responsive">
                <table class="table align-middle" id="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <?php if (isAdmin()): ?><th>Nhân viên</th><?php endif; ?>
                            <th>Tổng tiền</th>
                            <?php if (isAdmin()): ?><th>Lợi nhuận</th><?php endif; ?>
                            <th>Ngày tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const isAdminUser = <?php echo isAdmin() ? 'true' : 'false'; ?>;
        let trendChart = null;
        let barChart = null;

        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);

        function buildStatCard(label, value, iconClass, colorClass) {
            return `
                <div class="stat-card">
                    <div class="stat-icon ${colorClass}"><i class="fas ${iconClass}"></i></div>
                    <div class="stat-details">
                        <h3>${value}</h3>
                        <p>${label}</p>
                    </div>
                </div>
            `;
        }

        function renderStats(stats) {
            const cards = [
                buildStatCard('Doanh thu', formatMoney(stats.total_revenue), 'fa-money-bill-wave', 'info'),
                buildStatCard('Số đơn hàng', stats.total_orders, 'fa-receipt', 'primary'),
                buildStatCard('Số sản phẩm', stats.total_products, 'fa-box', 'success')
            ];

            if (isAdminUser) {
                cards.push(buildStatCard('Lợi nhuận', formatMoney(stats.total_profit || 0), 'fa-chart-pie', 'warning'));
            }

            $('#stats-grid').html(cards.join(''));
        }

        function renderOrders(orders) {
            const tbody = $('#orders-table tbody');
            if (!orders.length) {
                tbody.html(`<tr><td colspan="${isAdminUser ? 7 : 5}" class="text-center py-5 text-muted">Không có dữ liệu trong khoảng này.</td></tr>`);
                return;
            }

            tbody.html(orders.map(order => `
                <tr>
                    <td><strong>${order.order_code}</strong></td>
                    <td>${order.customer_name}<br><small class="text-muted">${order.phone}</small></td>
                    ${isAdminUser ? `<td>${order.staff_name}</td>` : ''}
                    <td>${formatMoney(order.total_amount)}</td>
                    ${isAdminUser ? `<td>${formatMoney(order.total_profit || 0)}</td>` : ''}
                    <td>${order.created_at}</td>
                    <td><a href="../orders/view_order.php?id=${order.id}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a></td>
                </tr>
            `).join(''));
        }

        function renderCharts(charts) {
            if (trendChart) trendChart.destroy();
            if (barChart) barChart.destroy();

            trendChart = new Chart(document.getElementById('trend-chart'), {
                type: 'line',
                data: {
                    labels: charts.trend_labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: charts.trend_revenue,
                        borderColor: '#7395AE',
                        backgroundColor: 'rgba(102, 126, 234, 0.18)',
                        fill: true,
                        tension: 0.35
                    }]
                }
            });

            barChart = new Chart(document.getElementById('bar-chart'), {
                type: 'bar',
                data: {
                    labels: charts.bar_labels,
                    datasets: [{
                        label: 'Doanh thu',
                        data: charts.bar_values,
                        backgroundColor: ['#7395AE','#38bdf8','#34d399','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316']
                    }]
                },
                options: { indexAxis: 'y' }
            });
        }

        function loadReport() {
            $.getJSON('report_data.php', {
                preset: $('#preset').val(),
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val()
            }, function (response) {
                if (!response.success) {
                    return;
                }

                $('#range-label').text('Khoảng báo cáo: ' + response.range.label);
                renderStats(response.stats);
                renderCharts(response.charts);
                renderOrders(response.orders);
            });
        }

        $('#apply-filter').on('click', loadReport);
        $('#preset').on('change', function () {
            const isCustom = $(this).val() === 'custom';
            $('#start-date, #end-date').prop('disabled', !isCustom);
        }).trigger('change');

        loadReport();
    </script>
</body>
</html>
