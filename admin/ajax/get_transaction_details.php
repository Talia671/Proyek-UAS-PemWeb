<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID required']);
    exit;
}

$transaction_id = intval($_GET['id']);

try {
    // Get transaction details
    $stmt = $pdo->prepare("
        SELECT t.*, u.username, u.email, u.full_name, u.phone
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        exit;
    }
    
    // Get transaction items
    $stmt = $pdo->prepare("
        SELECT ti.*, p.name as product_name, p.brand
        FROM transaction_items ti
        JOIN products p ON ti.product_id = p.id
        WHERE ti.transaction_id = ?
    ");
    $stmt->execute([$transaction_id]);
    $items = $stmt->fetchAll();
    
    // Generate HTML
    $status_colors = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    $color = $status_colors[$transaction['status']] ?? 'secondary';
    
    $html = '
    <div class="row">
        <div class="col-md-6">
            <h6>Transaction Information</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Transaction ID:</strong></td>
                    <td>#' . $transaction['id'] . '</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class="badge bg-' . $color . '">' . ucfirst($transaction['status']) . '</span></td>
                </tr>
                <tr>
                    <td><strong>Total Amount:</strong></td>
                    <td><strong>Rp ' . number_format($transaction['total_amount'], 0, ',', '.') . '</strong></td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>' . date('d M Y H:i', strtotime($transaction['created_at'])) . '</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Customer Information</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>' . htmlspecialchars($transaction['full_name'] ?: $transaction['username']) . '</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>' . htmlspecialchars($transaction['email']) . '</td>
                </tr>
                <tr>
                    <td><strong>Phone:</strong></td>
                    <td>' . htmlspecialchars($transaction['phone'] ?: '-') . '</td>
                </tr>
            </table>
        </div>
    </div>';
    
    if (!empty($transaction['shipping_address'])) {
        $html .= '
        <div class="row mt-3">
            <div class="col-12">
                <h6>Shipping Address</h6>
                <div class="alert alert-light">
                    ' . nl2br(htmlspecialchars($transaction['shipping_address'])) . '
                </div>
            </div>
        </div>';
    }
    
    $html .= '
    <div class="row mt-3">
        <div class="col-12">
            <h6>Order Items</h6>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($items as $item) {
        $html .= '
                        <tr>
                            <td>' . htmlspecialchars($item['product_name']) . '</td>
                            <td>' . htmlspecialchars($item['brand']) . '</td>
                            <td>' . htmlspecialchars($item['size']) . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>Rp ' . number_format($item['price'], 0, ',', '.') . '</td>
                            <td>Rp ' . number_format($item['price'] * $item['quantity'], 0, ',', '.') . '</td>
                        </tr>';
    }
    
    $html .= '
                    </tbody>
                    <tfoot>
                        <tr class="table-dark">
                            <th colspan="5">Total</th>
                            <th>Rp ' . number_format($transaction['total_amount'], 0, ',', '.') . '</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>';
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>