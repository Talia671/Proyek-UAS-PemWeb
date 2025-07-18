<?php
require_once '../config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            addToCart($pdo, $user_id);
            break;
        case 'update':
            updateCart($pdo, $user_id);
            break;
        case 'delete':
            deleteFromCart($pdo, $user_id);
            break;
        case 'clear':
            clearCart($pdo, $user_id);
            break;
        case 'get':
            getCartItems($pdo, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function addToCart($pdo, $user_id) {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (!$product_id || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
        return;
    }
    
    // Check if product exists and has enough stock
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }
    
    if ($product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        return;
    }
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();
    
    if ($existing_item) {
        // Update existing item
        $new_quantity = $existing_item['quantity'] + $quantity;
        if ($new_quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            return;
        }
        
        $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $existing_item['id']]);
    } else {
        // Add new item
        $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
}

function updateCart($pdo, $user_id) {
    $cart_id = $_POST['cart_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    
    if (!$cart_id || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID or quantity']);
        return;
    }
    
    if ($quantity == 0) {
        // Remove item if quantity is 0
        $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        return;
    }
    
    // Check if cart item belongs to user and get product info
    $stmt = $pdo->prepare("
        SELECT c.id, c.product_id, p.stock, p.name 
        FROM carts c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        return;
    }
    
    if ($quantity > $cart_item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        return;
    }
    
    // Update quantity
    $stmt = $pdo->prepare("UPDATE carts SET quantity = ? WHERE id = ?");
    $stmt->execute([$quantity, $cart_id]);
    
    echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
}

function deleteFromCart($pdo, $user_id) {
    $cart_id = $_POST['cart_id'] ?? 0;
    
    if (!$cart_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    }
}

function clearCart($pdo, $user_id) {
    $stmt = $pdo->prepare("DELETE FROM carts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
}

function getCartItems($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, c.product_id,
               p.name, p.price, p.stock,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
        FROM carts c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.id DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'items' => $cart_items,
        'total' => $total,
        'count' => count($cart_items)
    ]);
}
?>
