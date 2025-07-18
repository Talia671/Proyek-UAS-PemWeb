<?php
require_once '../config.php';

requireAdmin();

// Get parameters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'sales';

// For now, we'll create a simple HTML export that can be printed as PDF
// In a production environment, you might want to use a library like TCPDF or FPDF

// Get the same data as in reports.php
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

// Set headers for PDF display
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - <?php echo date('d/m/Y', strtotime($date_from)); ?> s/d <?php echo date('d/m/Y', strtotime($date_to)); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
            color: #333;
        }
        .stat-card p {
            margin: 0;
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        @media print {
            body {
                margin: 0;
                font-size: 10px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Penjualan</h1>
        <p><strong>ShoeBrand Store</strong></p>
        <p>Periode: <?php echo date('d/m/Y', strtotime($date_from)); ?> s/d <?php echo date('d/m/Y', strtotime($date_to)); ?></p>
        <p>Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <!-- Overall Statistics -->
    <div class="section">
        <h2>Ringkasan Statistik</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo number_format($overall_stats['total_orders'] ?? 0); ?></h3>
                <p>Total Pesanan</p>
            </div>
            <div class="stat-card">
                <h3><?php echo formatPrice($overall_stats['total_revenue'] ?? 0); ?></h3>
                <p>Total Pendapatan</p>
            </div>
            <div class="stat-card">
                <h3><?php echo formatPrice($overall_stats['avg_order_value'] ?? 0); ?></h3>
                <p>Rata-rata Pesanan</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($overall_stats['unique_customers'] ?? 0); ?></h3>
                <p>Pelanggan Unik</p>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="section">
        <h2>Produk Terlaris</h2>
        <?php if ($top_products): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th class="text-center">Qty Terjual</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_products as $index => $product): ?>
                <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="text-center"><?php echo number_format($product['total_sold']); ?></td>
                    <td class="text-right"><?php echo formatPrice($product['price']); ?></td>
                    <td class="text-right"><?php echo formatPrice($product['total_revenue']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">Tidak ada data produk pada periode ini</div>
        <?php endif; ?>
    </div>

    <!-- Top Customers -->
    <div class="section">
        <h2>Pelanggan Terbaik</h2>
        <?php if ($top_customers): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th class="text-center">Total Pesanan</th>
                    <th class="text-right">Total Belanja</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_customers as $index => $customer): ?>
                <tr>
                    <td class="text-center"><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                    <td class="text-center"><?php echo number_format($customer['total_orders']); ?></td>
                    <td class="text-right"><?php echo formatPrice($customer['total_spent']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">Tidak ada data pelanggan pada periode ini</div>
        <?php endif; ?>
    </div>

    <!-- Daily Sales -->
    <div class="section">
        <h2>Detail Penjualan Harian</h2>
        <?php if ($sales_data): ?>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="text-center">Total Pesanan</th>
                    <th class="text-right">Total Penjualan</th>
                    <th class="text-right">Rata-rata Pesanan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_data as $day): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                    <td class="text-center"><?php echo number_format($day['total_orders']); ?></td>
                    <td class="text-right"><?php echo formatPrice($day['total_sales']); ?></td>
                    <td class="text-right"><?php echo formatPrice($day['avg_order_value']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f5f5f5;">
                    <td>TOTAL</td>
                    <td class="text-center"><?php echo number_format(array_sum(array_column($sales_data, 'total_orders'))); ?></td>
                    <td class="text-right"><?php echo formatPrice(array_sum(array_column($sales_data, 'total_sales'))); ?></td>
                    <td class="text-right">-</td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="no-data">Tidak ada data penjualan pada periode ini</div>
        <?php endif; ?>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
