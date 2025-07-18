<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$cart_id = (int)$_POST['cart_id'];
$user_id = $_SESSION['user_id'];

if ($cart_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

try {
    // Verify cart item belongs to user
    $stmt = $pdo->prepare("SELECT product_id FROM carts WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Item keranjang tidak ditemukan']);
        exit;
    }
    
    // Delete cart item
    $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ?");
    $stmt->execute([$cart_id]);
    
    // Get updated cart totals
    $stmt = $pdo->prepare("
        SELECT SUM(p.price * c.quantity) as total, SUM(c.quantity) as count
        FROM carts c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_totals = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'cart_total' => formatPrice($cart_totals['total'] ?: 0),
        'cart_count' => $cart_totals['count'] ?: 0
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>