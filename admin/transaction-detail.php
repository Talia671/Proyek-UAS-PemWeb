<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Detail Transaksi';

// Get transaction ID from URL
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($transaction_id === 0) {
    header('Location: transactions.php');
    exit;
}

// Get transaction details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        header('Location: transactions.php');
        exit;
    }
    
    // Get transaction items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$transaction_id]);
    $items = $stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading transaction: " . $e->getMessage();
    header('Location: transactions.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = $_POST['status'];
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $transaction_id]);
            
            // Log activity
            logActivity($_SESSION['user_id'], "Updated order #" . $transaction['order_number'] . " status to $new_status");
            
            $_SESSION['success'] = "Status berhasil diperbarui!";
            header('Location: transaction-detail.php?id=' . $transaction_id);
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating status: " . $e->getMessage();
        }
    }
}

include 'includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-receipt me-2"></i>Detail Transaksi</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="transactions.php">Transaksi</a></li>
                            <li class="breadcrumb-item active">#<?php echo $transaction['order_number']; ?></li>
                        </ol>
                    </nav>
                </div>
                <a href="transactions.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="row">
                <!-- Transaction Info -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Transaksi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Order Number:</strong></td>
                                            <td>#<?php echo $transaction['order_number']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal:</strong></td>
                                            <td><?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'shipped' => 'primary',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $color = $status_colors[$transaction['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?>">
                                                    <?php echo ucfirst($transaction['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total:</strong></td>
                                            <td><strong><?php echo formatPrice($transaction['total_amount']); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Customer:</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['customer_email']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Phone:</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['phone']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Payment Method:</strong></td>
                                            <td><?php echo htmlspecialchars($transaction['payment_method']); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo SITE_URL . '/' . ($item['product_image'] ?: 'assets/img/no-image.jpg'); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                         class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo formatPrice($item['price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th><?php echo formatPrice($transaction['total_amount']); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <!-- Update Status -->
                            <div class="mb-3">
                                <label class="form-label">Update Status</label>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_status">
                                    <div class="input-group">
                                        <select name="status" class="form-select" required>
                                            <option value="pending" <?php echo $transaction['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $transaction['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $transaction['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $transaction['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $transaction['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Shipping Address -->
                            <div class="mb-3">
                                <label class="form-label">Alamat Pengiriman</label>
                                <div class="border rounded p-3 bg-light">
                                    <address class="mb-0">
                                        <?php echo nl2br(htmlspecialchars($transaction['address'])); ?><br>
                                        <?php echo htmlspecialchars($transaction['city']); ?>, 
                                        <?php echo htmlspecialchars($transaction['state']); ?> 
                                        <?php echo htmlspecialchars($transaction['zip']); ?>
                                    </address>
                                </div>
                            </div>

                            <?php if (!empty($transaction['courier_note'])): ?>
                            <div class="mb-3">
                                <label class="form-label">Catatan Kurir</label>
                                <div class="border rounded p-3 bg-light">
                                    <?php echo nl2br(htmlspecialchars($transaction['courier_note'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
