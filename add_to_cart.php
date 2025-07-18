<?php
require_once 'config.php';

if (!isLoggedIn()) {
    echo "Please login first";
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Get some sample products first
    $stmt = $pdo->prepare("SELECT id, name, price FROM products LIMIT 3");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "No products found. Please add some products first.";
        exit;
    }
    
    // Add each product to cart
    foreach ($products as $product) {
        // Check if already in cart
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product['id']]);
        
        if (!$stmt->fetch()) {
            // Add to cart
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product['id']]);
            echo "Added {$product['name']} to cart<br>";
        } else {
            echo "{$product['name']} already in cart<br>";
        }
    }
    
    echo "<br><a href='cart.php'>View Cart</a> | <a href='checkout.php'>Go to Checkout</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
