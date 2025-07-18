<?php
require_once 'config.php';

try {
    echo "<h2>Database Check</h2>";
    
    // Check if tables exist
    $tables = ['products', 'carts', 'users'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->fetch()) {
            echo "✓ Table '$table' exists<br>";
            
            // Count records
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $count_stmt->execute();
            $count = $count_stmt->fetchColumn();
            echo "&nbsp;&nbsp;Records: $count<br>";
        } else {
            echo "✗ Table '$table' does not exist<br>";
        }
    }
    
    // Check if we have products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $product_count = $stmt->fetchColumn();
    
    if ($product_count == 0) {
        echo "<br><strong>No products found! Adding sample products...</strong><br>";
        
        // Add sample products
        $sample_products = [
            ['Nike Air Max 270', 'Sepatu olahraga dengan teknologi Air Max untuk kenyamanan maksimal.', 1299000, 10, 1],
            ['Adidas Ultraboost 22', 'Sepatu lari dengan teknologi Boost yang responsif dan nyaman.', 2199000, 8, 1],
            ['Converse Chuck Taylor', 'Sepatu klasik yang timeless dengan desain ikonik.', 899000, 15, 1],
            ['Vans Old Skool', 'Sepatu skateboard klasik dengan stripe khas Vans.', 1099000, 12, 1],
            ['New Balance 574', 'Sepatu lifestyle dengan comfort yang luar biasa.', 1599000, 7, 1]
        ];
        
        foreach ($sample_products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($product);
            echo "Added: {$product[0]}<br>";
        }
        
        echo "<br>Sample products added successfully!<br>";
    }
    
    // Check current user's cart
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetchColumn();
        
        echo "<br><strong>Your cart: $cart_count items</strong><br>";
        
        if ($cart_count == 0) {
            echo "Your cart is empty. <a href='fix_cart.php'>Add some items</a><br>";
        }
    } else {
        echo "<br>Not logged in. <a href='login.php'>Login first</a><br>";
    }
    
    echo "<br><a href='index.php'>Home</a> | <a href='products.php'>Products</a> | <a href='cart.php'>Cart</a> | <a href='checkout.php'>Checkout</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString();
}
?>
