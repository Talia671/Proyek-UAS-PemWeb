<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Manajemen Transaksi';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $transaction_id = intval($_POST['transaction_id']);
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $transaction_id]);
            
            // Log activity
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Updated order #$transaction_id status to $new_status"]);
            
            $_SESSION['success'] = "Order status updated successfully!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating status: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Invalid status!";
    }
    
    header('Location: transactions.php');
    exit;
}

// Pagination and filters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR o.id LIKE ? OR o.order_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get transactions (orders)
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as username, u.email,
               COUNT(oi.id) as item_count
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        $where_clause
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();
    
    // Get total count for pagination
    $count_stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT o.id)
        FROM orders o
        JOIN users u ON o.user_id = u.id
        $where_clause
    ");
    $count_stmt->execute($params);
    $total_transactions = $count_stmt->fetchColumn();
    $total_pages = ceil($total_transactions / $limit);
} catch (Exception $e) {
    $transactions = [];
    $total_pages = 0;
    $_SESSION['error'] = "Error fetching orders: " . $e->getMessage();
}

include 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-shopping-cart"></i> Transaction Management</h2>
                <div class="d-flex gap-2">
                    <span class="badge bg-info">Total: <?= $total_transactions ?></span>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by transaction ID, username, or email">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="transactions.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No transactions found</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><strong>#<?= $transaction['order_number'] ?></strong></td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($transaction['username']) ?></strong>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($transaction['email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= $transaction['item_count'] ?> items</span>
                                </td>
                                <td>
                                    <strong>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></strong>
                                </td>
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
                                        <span class="badge bg-<?= $color ?>"><?= ucfirst($transaction['status']) ?></span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($transaction['created_at'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="viewTransaction(<?= $transaction['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="updateStatus(<?= $transaction['id'] ?>, '<?= $transaction['status'] ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">Previous</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Update Transaction Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="transaction_id" id="status_transaction_id">
                    
                    <div class="mb-3">
                        <label for="status_select" class="form-label">New Status</label>
                        <select class="form-select" id="status_select" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewTransaction(id) {
    const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
    modal.show();
    
    // Load transaction details via AJAX
    fetch(`ajax/get_transaction_details.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('transactionDetails').innerHTML = data.html;
            } else {
                document.getElementById('transactionDetails').innerHTML = 
                    '<div class="alert alert-danger">Error loading transaction details</div>';
            }
        })
        .catch(error => {
            document.getElementById('transactionDetails').innerHTML = 
                '<div class="alert alert-danger">Error loading transaction details</div>';
        });
}

function updateStatus(id, currentStatus) {
    document.getElementById('status_transaction_id').value = id;
    document.getElementById('status_select').value = currentStatus;
    
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}
</script>

<?php include 'includes/admin_footer.php'; ?>