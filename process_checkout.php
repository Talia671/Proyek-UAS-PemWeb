<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get form data
$address = sanitize($_POST['address']);
$city = sanitize($_POST['city']);
$state = sanitize($_POST['state']);
$zip = sanitize($_POST['zip']);
$phone = sanitize($_POST['phone']);
$courier_note = sanitize($_POST['courier_note']);
$payment_method = sanitize($_POST['payment_method']);

// Validate required fields
if (empty($address) || empty($city) || empty($state) || empty($zip) || empty($phone) || empty($payment_method)) {
    $_SESSION['error'] = 'Semua field harus diisi!';
    header('Location: checkout.php');
    exit;
}

// Get selected items
$selected_items = $_POST['selected_items'] ?? [];
if (empty($selected_items)) {
    // If no specific items selected, get all cart items
    $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($cart_ids)) {
        $_SESSION['error'] = 'Keranjang kosong!';
        header('Location: cart.php');
        exit;
    }
    
    $selected_items = $cart_ids;
}

try {
    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        zip VARCHAR(20) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        courier_note TEXT,
        payment_method VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->beginTransaction();
    
    // Get cart items
    $placeholders = str_repeat('?,', count($selected_items) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, c.product_id,
               p.name, p.price, p.stock
        FROM carts c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND c.id IN ($placeholders)
    ");
    
    $params = array_merge([$user_id], $selected_items);
    $stmt->execute($params);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        throw new Exception('Item tidak ditemukan!');
    }
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Create order
    $order_number = 'ORD-' . date('YmdHis') . '-' . $user_id;
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, user_id, total_amount, status, 
            address, city, state, zip, phone, courier_note, payment_method,
            created_at
        ) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $order_number, $user_id, $total, $address, $city, $state, $zip, $phone, $courier_note, $payment_method
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert order items
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        
        // Update product stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Remove items from cart
    $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ? AND id IN ($placeholders)");
    $stmt->execute($params);
    
    $pdo->commit();
    
    // Redirect to payment page
    header('Location: payment.php?order=' . $order_number);
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    header('Location: checkout.php');
    exit;
}
?>
