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
$quantity = (int)$_POST['quantity'];
$user_id = $_SESSION['user_id'];

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

try {
    // Verify cart item belongs to user and get product info
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.stock 
        FROM carts c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Item keranjang tidak ditemukan']);
        exit;
    }
    
    if ($quantity > $cart_item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Jumlah melebihi stok yang tersedia']);
        exit;
    }
    
    // Update cart quantity
    $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_id]);
    
    // Calculate new totals
    $item_total = $cart_item['price'] * $quantity;
    
    // Get cart total
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
        'item_total' => formatPrice($item_total),
        'cart_total' => formatPrice($cart_totals['total']),
        'cart_count' => $cart_totals['count']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
?>