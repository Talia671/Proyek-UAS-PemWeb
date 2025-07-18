<?php
require_once 'config.php';

if (!isLoggedIn()) {
    echo "Please login first. <a href='login.php'>Login</a>";
    exit;
}

$user_id = $_SESSION['user_id'];
echo "User ID: " . $user_id . "<br>";

try {
    // First, let's check if there are any products
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products LIMIT 5");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    echo "Found " . count($products) . " products:<br>";
    
    if (empty($products)) {
        echo "No products found in database!<br>";
        exit;
    }
    
    // Display products
    foreach ($products as $product) {
        echo "- {$product['name']} (ID: {$product['id']}, Price: {$product['price']}, Stock: {$product['stock']})<br>";
    }
    
    // Check current cart
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetchColumn();
    
    echo "<br>Current cart items: " . $cart_count . "<br>";
    
    // Add first 3 products to cart if cart is empty
    if ($cart_count == 0) {
        echo "<br>Adding products to cart...<br>";
        
        foreach (array_slice($products, 0, 3) as $product) {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product['id']]);
            echo "Added {$product['name']} to cart<br>";
        }
        
        echo "<br>Cart updated successfully!<br>";
    }
    
    // Show current cart contents
    echo "<br><strong>Current Cart Contents:</strong><br>";
    $stmt = $pdo->prepare("
        SELECT c.id, c.quantity, p.name, p.price, 
               (p.price * c.quantity) as total
        FROM carts c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $grand_total = 0;
    foreach ($cart_items as $item) {
        echo "- {$item['name']} (Qty: {$item['quantity']}) - Rp " . number_format($item['total'], 0, ',', '.') . "<br>";
        $grand_total += $item['total'];
    }
    
    echo "<br><strong>Grand Total: Rp " . number_format($grand_total, 0, ',', '.') . "</strong><br>";
    
    echo "<br><a href='cart.php'>View Cart</a> | <a href='checkout.php'>Go to Checkout</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString();
}
?>
