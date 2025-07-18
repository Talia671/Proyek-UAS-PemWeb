<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['image_id'])) {
    echo json_encode(['success' => false, 'message' => 'Image ID required']);
    exit;
}

$image_id = intval($_POST['image_id']);

try {
    // Get image info first
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();
    
    if (!$image) {
        echo json_encode(['success' => false, 'message' => 'Image not found']);
        exit;
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$image_id]);
    
    // Delete physical file
    $file_path = '../../assets/img/products/' . $image['image_url'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Log activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Deleted product image ID: $image_id"]);
    
    echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>