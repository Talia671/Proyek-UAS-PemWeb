<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Laporan';

// Get date range filters
$date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today

// Get report type
$report_type = $_GET['report_type'] ?? 'sales';

// Sales Summary
$sales_query = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_sales,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE status IN ('delivered', 'processing', 'shipped') 
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
";

$stmt = $pdo->prepare($sales_query);
$stmt->execute([$date_from, $date_to]);
$sales_data = $stmt->fetchAll();

// Overall statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        COUNT(DISTINCT user_id) as unique_customers
    FROM orders 
    WHERE status IN ('delivered', 'processing', 'shipped') 
    AND DATE(created_at) BETWEEN ? AND ?
";

$stmt = $pdo->prepare($stats_query);
$stmt->execute([$date_from, $date_to]);
$overall_stats = $stmt->fetch();

// Top selling products
$top_products_query = "
    SELECT 
        p.name,
        p.price,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('delivered', 'processing', 'shipped') 
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY p.id, p.name, p.price
    ORDER BY total_sold DESC
    LIMIT 10
";

$stmt = $pdo->prepare($top_products_query);
$stmt->execute([$date_from, $date_to]);
$top_products = $stmt->fetchAll();

// Customer analytics
$customer_query = "
    SELECT 
        u.name,
        u.email,
        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as total_spent
    FROM users u
    JOIN orders o ON u.id = o.user_id
    WHERE o.status IN ('delivered', 'processing', 'shipped') 
    AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY u.id, u.name, u.email
    ORDER BY total_spent DESC
    LIMIT 10
";

$stmt = $pdo->prepare($customer_query);
$stmt->execute([$date_from, $date_to]);
$top_customers = $stmt->fetchAll();

// Monthly comparison
$monthly_query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as orders_count,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE status IN ('delivered', 'processing', 'shipped') 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
";

$stmt = $pdo->prepare($monthly_query);
$stmt->execute();
$monthly_data = $stmt->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Laporan Penjualan</h2>
        <button type="button" class="btn btn-success" onclick="exportReport()">
            <i class="fas fa-download me-2"></i>Export PDF
        </button>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Laporan</label>
                    <select class="form-select" name="report_type">
                        <option value="sales" <?php echo $report_type == 'sales' ? 'selected' : ''; ?>>Penjualan</option>
                        <option value="products" <?php echo $report_type == 'products' ? 'selected' : ''; ?>>Produk</option>
                        <option value="customers" <?php echo $report_type == 'customers' ? 'selected' : ''; ?>>Pelanggan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($overall_stats['total_orders'] ?? 0); ?></h4>
                            <p class="mb-0">Total Pesanan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo formatPrice($overall_stats['total_revenue'] ?? 0); ?></h4>
                            <p class="mb-0">Total Pendapatan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo formatPrice($overall_stats['avg_order_value'] ?? 0); ?></h4>
                            <p class="mb-0">Rata-rata Pesanan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0"><?php echo number_format($overall_stats['unique_customers'] ?? 0); ?></h4>
                            <p class="mb-0">Pelanggan Unik</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Grafik Penjualan Harian</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Perbandingan Bulanan</h5>
                </div>
                <div class="card-body">
                    <?php if ($monthly_data): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th>Pesanan</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly_data as $month): ?>
                                <tr>
                                    <td><?php echo date('M Y', strtotime($month['month'] . '-01')); ?></td>
                                    <td><?php echo number_format($month['orders_count']); ?></td>
                                    <td><?php echo formatPrice($month['revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Tidak ada data bulanan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Produk Terlaris</h5>
                </div>
                <div class="card-body">
                    <?php if ($top_products): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Terjual</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo number_format($product['total_sold']); ?></td>
                                    <td><?php echo formatPrice($product['total_revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Tidak ada data produk</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pelanggan Terbaik</h5>
                </div>
                <div class="card-body">
                    <?php if ($top_customers): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>Pesanan</th>
                                    <th>Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_customers as $customer): ?>
                                <tr>
                                    <td>
                                        <div><?php echo htmlspecialchars($customer['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></small>
                                    </td>
                                    <td><?php echo number_format($customer['total_orders']); ?></td>
                                    <td><?php echo formatPrice($customer['total_spent']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">Tidak ada data pelanggan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Sales Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detail Penjualan Harian</h5>
                </div>
                <div class="card-body">
                    <?php if ($sales_data): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Total Pesanan</th>
                                    <th>Total Penjualan</th>
                                    <th>Rata-rata Pesanan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales_data as $day): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                                    <td><?php echo number_format($day['total_orders']); ?></td>
                                    <td><?php echo formatPrice($day['total_sales']); ?></td>
                                    <td><?php echo formatPrice($day['avg_order_value']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada data penjualan</h5>
                        <p class="text-muted">Belum ada penjualan pada periode yang dipilih</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Sales Chart
const salesData = <?php echo json_encode(array_reverse($sales_data)); ?>;
const ctx = document.getElementById('salesChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesData.map(day => {
            const date = new Date(day.date);
            return date.toLocaleDateString('id-ID');
        }),
        datasets: [{
            label: 'Penjualan',
            data: salesData.map(day => day.total_sales),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Jumlah Pesanan',
            data: salesData.map(day => day.total_orders),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Penjualan (Rp)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Jumlah Pesanan'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Grafik Penjualan Harian'
            }
        }
    }
});

function exportReport() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    window.open('reports_export.php?' + params.toString(), '_blank');
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
