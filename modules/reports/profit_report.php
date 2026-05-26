<?php
require_once '../../config/config.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);
$pageTitle = 'Báo cáo lợi nhuận';
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
                <h2 class="card-title"><i class="fas fa-chart-pie"></i> Báo cáo lợi nhuận</h2>
                <a href="<?php echo url('modules/reports/sales_report.php'); ?>" class="btn btn-outline-secondary"><i class="fas fa-chart-line"></i> Báo cáo tổng hợp</a>
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
                <div class="col-md-3"><label class="form-label">Từ ngày</label><input type="date" id="start-date" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Đến ngày</label><input type="date" id="end-date" class="form-control"></div>
                <div class="col-md-2 d-grid"><button type="button" id="apply-filter" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button></div>
            </div>
            <div class="text-muted mt-3" id="range-label"></div>
        </div>

        <div class="stats-grid" id="stats-grid"></div>

        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-chart-bar"></i> Lợi nhuận theo danh mục</h2></div>
            <canvas id="profit-chart" height="120"></canvas>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title"><i class="fas fa-table"></i> Đơn hàng trong kỳ</h2></div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Nhân viên</th>
                            <th>Doanh thu</th>
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
        const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
        let profitChart = null;

        function stat(label, value, iconClass, colorClass) {
            return `<div class="stat-card"><div class="stat-icon ${colorClass}"><i class="fas ${iconClass}"></i></div><div class="stat-details"><h3>${value}</h3><p>${label}</p></div></div>`;
        }

        function loadProfitReport() {
            $.getJSON('report_data.php', {
                preset: $('#preset').val(),
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val()
            }, function (response) {
                if (!response.success) return;

                $('#range-label').text('Khoảng báo cáo: ' + response.range.label);
                $('#stats-grid').html([
                    stat('Doanh thu', formatMoney(response.stats.total_revenue), 'fa-money-bill-wave', 'info'),
                    stat('Lợi nhuận', formatMoney(response.stats.total_profit || 0), 'fa-chart-pie', 'warning'),
                    stat('Số đơn', response.stats.total_orders, 'fa-receipt', 'primary'),
                    stat('Số sản phẩm', response.stats.total_products, 'fa-box', 'success')
                ].join(''));

                if (profitChart) profitChart.destroy();
                profitChart = new Chart(document.getElementById('profit-chart'), {
                    type: 'bar',
                    data: {
                        labels: response.charts.bar_labels,
                        datasets: [{
                            label: 'Doanh thu',
                            data: response.charts.bar_values,
                            backgroundColor: ['#7395AE','#38bdf8','#34d399','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316']
                        }]
                    }
                });

                const rows = response.orders.map(order => `
                    <tr>
                        <td><strong>${order.order_code}</strong></td>
                        <td>${order.customer_name}<br><small class="text-muted">${order.phone}</small></td>
                        <td>${order.staff_name}</td>
                        <td>${formatMoney(order.total_amount)}</td>
                        <td>${formatMoney(order.total_profit || 0)}</td>
                        <td>${order.created_at}</td>
                        <td><a href="../orders/view_order.php?id=${order.id}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                `).join('');

                $('#orders-body').html(rows || '<tr><td colspan="7" class="text-center py-5 text-muted">Không có dữ liệu.</td></tr>');
            });
        }

        $('#apply-filter').on('click', loadProfitReport);
        $('#preset').on('change', function () {
            const isCustom = $(this).val() === 'custom';
            $('#start-date, #end-date').prop('disabled', !isCustom);
        }).trigger('change');

        loadProfitReport();
    </script>
</body>
</html>
