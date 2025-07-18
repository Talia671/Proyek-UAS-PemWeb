<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit;
}

$product_id = intval($_GET['product_id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'images' => $images
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>