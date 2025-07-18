<?php
require_once 'config.php';

echo "<h2>Setting up demo data...</h2>";

try {
    // First, make sure we have categories
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add a sample category
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute(['Sepatu Olahraga', 'Koleksi sepatu untuk kegiatan olahraga']);
        echo "✓ Sample category added<br>";
    }
    
    // Add sample products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $products = [
            ['Nike Air Max 270', 'Sepatu olahraga dengan teknologi Air Max untuk kenyamanan maksimal.', 1299000, 10, 1],
            ['Adidas Ultraboost 22', 'Sepatu lari dengan teknologi Boost yang responsif dan nyaman.', 2199000, 8, 1],
            ['Converse Chuck Taylor', 'Sepatu klasik yang timeless dengan desain ikonik.', 899000, 15, 1],
        ];
        
        foreach ($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($product);
            echo "✓ Added product: {$product[0]}<br>";
        }
    }
    
    // If user is logged in, add items to cart
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        
        // Clear existing cart first
        $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Add sample items to cart
        $stmt = $pdo->prepare("SELECT id FROM products LIMIT 3");
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product['id'], 1]);
            echo "✓ Added product ID {$product['id']} to cart<br>";
        }
        
        echo "<br><strong>Demo setup complete!</strong><br>";
        echo "<a href='checkout.php'>Go to Checkout</a> | <a href='cart.php'>View Cart</a>";
    } else {
        echo "<br>Please <a href='login.php'>login</a> first to add items to cart.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
