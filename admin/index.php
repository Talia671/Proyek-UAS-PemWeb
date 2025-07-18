<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Dashboard Admin';

// Get statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$stats['total_products'] = $stmt->fetchColumn();

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 2");
$stats['total_users'] = $stmt->fetchColumn();

// Total transactions
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$stats['total_transactions'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Today's transactions
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['today_transactions'] = $stmt->fetchColumn();

// Today's revenue
$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
$stats['today_revenue'] = $stmt->fetchColumn() ?: 0;

// Recent transactions
$stmt = $pdo->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");
$recent_transactions = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->query("
    SELECT * FROM products 
    WHERE stock <= 5 
    ORDER BY stock ASC 
    LIMIT 10
");
$low_stock_products = $stmt->fetchAll();

// Recent activities
$stmt = $pdo->query("
    SELECT a.*, u.name as user_name 
    FROM activity_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 15
");
$recent_activities = $stmt->fetchAll();

include 'includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="admin-card text-center">
                <div class="admin-card-icon text-primary">
                    <i class="fas fa-box"></i>
                </div>
                <div class="admin-card-title">Total Produk</div>
                <div class="admin-card-value"><?php echo number_format($stats['total_products']); ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-card text-center">
                <div class="admin-card-icon text-success">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-card-title">Total Pengguna</div>
                <div class="admin-card-value"><?php echo number_format($stats['total_users']); ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-card text-center">
                <div class="admin-card-icon text-warning">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="admin-card-title">Total Transaksi</div>
                <div class="admin-card-value"><?php echo number_format($stats['total_transactions']); ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-card text-center">
                <div class="admin-card-icon text-info">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="admin-card-title">Total Pendapatan</div>
                <div class="admin-card-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
            </div>
        </div>
    </div>

    <!-- Today's Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="admin-card">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-calendar-day me-2 text-primary"></i>
                    Statistik Hari Ini
                </h5>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary"><?php echo $stats['today_transactions']; ?></h3>
                            <small class="text-muted">Transaksi Hari Ini</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success"><?php echo formatPrice($stats['today_revenue']); ?></h3>
                            <small class="text-muted">Pendapatan Hari Ini</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="admin-card">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                    Stok Menipis
                </h5>
                <?php if ($low_stock_products): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><?php echo $product['name']; ?></span>
                            <span class="badge bg-warning"><?php echo $product['stock']; ?> tersisa</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($low_stock_products) > 5): ?>
                        <div class="text-center mt-2">
                            <a href="products.php?filter=low_stock" class="btn btn-sm btn-outline-warning">
                                Lihat Semua (<?php echo count($low_stock_products); ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">Semua produk memiliki stok yang cukup</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Transactions -->
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-receipt me-2 text-primary"></i>
                        Transaksi Terbaru
                    </h5>
                    <a href="transactions.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                
                <?php if ($recent_transactions): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td>#<?php echo $transaction['id']; ?></td>
                                <td><?php echo $transaction['user_name']; ?></td>
                                <td><?php echo formatPrice($transaction['total_amount']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                                <td>
                                    <a href="transaction-detail.php?id=<?php echo $transaction['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">Belum ada transaksi</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-lg-4">
            <div class="admin-card">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Aktivitas Terbaru
                </h5>
                
                <?php if ($recent_activities): ?>
                <div class="activity-list" style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-start">
                            <div class="activity-icon me-3">
                                <i class="fas fa-user-circle text-muted"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="activity-content">
                                    <strong><?php echo $activity['user_name']; ?></strong>
                                    <span class="text-muted"><?php echo $activity['activity']; ?></span>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">Belum ada aktivitas</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>