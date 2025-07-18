
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function downloadReceipt() {
        const receipt = document.querySelector('.success-card');
        html2canvas(receipt).then(canvas => {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/jpeg');
            link.download = 'receipt.jpg';
            link.click();
        });
    }
</script>

<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$order_number = $_GET['order'] ?? '';
if (empty($order_number)) {
    header('Location: cart.php');
    exit;
}

// Create tables if they don't exist
try {
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
} catch (Exception $e) {
    // Tables might already exist
}

// Get order details
$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->execute([$order_number, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: cart.php');
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, p.name,
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();

$page_title = 'Pesanan Berhasil';
include 'includes/header.php';
?>

<style>
    .success-container {
        max-width: 600px;
        margin: 50px auto;
        padding: 20px;
        text-align: center;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background-color: #28a745;
        border-radius: 50%;
        margin: 0 auto 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: white;
    }
    
    .success-card {
        background: white;
        border-radius: 12px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    
    .success-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 15px;
    }
    
    .success-subtitle {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .order-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: left;
    }
    
    .order-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .order-row:last-child {
        margin-bottom: 0;
    }
    
    .order-label {
        font-weight: 500;
        color: #333;
    }
    
    .order-value {
        color: #666;
    }
    
    .order-number {
        font-size: 18px;
        font-weight: bold;
        color: #007bff;
        margin: 20px 0;
    }
    
    .total-amount {
        font-size: 20px;
        font-weight: bold;
        color: #28a745;
        margin: 15px 0;
    }
    
    .btn-continue {
        background-color: #dc3545;
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 20px;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-continue:hover {
        background-color: #c82333;
        color: white;
        text-decoration: none;
    }
    
    .payment-method {
        background: #e8f5e8;
        border: 1px solid #28a745;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
    }
    
    .payment-method-title {
        font-weight: bold;
        color: #28a745;
        margin-bottom: 5px;
    }
    
    .items-summary {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        text-align: left;
    }
    
    .item-row {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .item-row:last-child {
        border-bottom: none;
    }
    
    .item-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 15px;
    }
    
    .item-details {
        flex: 1;
    }
    
    .item-name {
        font-weight: 500;
        color: #333;
        margin-bottom: 5px;
    }
    
    .item-price {
        color: #666;
        font-size: 14px;
    }
</style>

<div class="container">
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <div class="success-title">PEMBAYARAN ANDA BERHASIL</div>
            
            <div class="success-subtitle">
                Terima kasih telah berbelanja di Piero Indonesia<br>
                Anda akan menerima email konfirmasi pesanan sesuai detail pemesanan.<br>
                Pesanan Anda akan diproses secepatnya
            </div>
            
            <div class="order-number">
                Nomor pesanan Anda adalah: <?php echo $order['order_number']; ?>
            </div>
            
            <div class="total-amount">
                Jumlah Pembayaran: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
            </div>
            
            <div class="payment-method">
                <div class="payment-method-title">Metode Pembayaran</div>
                <div><?php echo $order['payment_method']; ?> Virtual Account</div>
            </div>
            
            <div class="items-summary">
                <div class="order-label" style="margin-bottom: 15px; font-size: 16px;">Ringkasan Pesanan</div>
                <?php foreach ($order_items as $item): ?>
                    <div class="item-row">
                        <img src="<?php echo $item['image_url'] ?: 'assets/img/no-image.jpg'; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                        <div class="item-details">
                            <div class="item-name"><?php echo $item['name']; ?></div>
                            <div class="item-price"><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-info">
                <div class="order-row">
                    <span class="order-label">Alamat Pengiriman:</span>
                    <span class="order-value"><?php echo $order['address']; ?></span>
                </div>
                <div class="order-row">
                    <span class="order-label">Kota:</span>
                    <span class="order-value"><?php echo $order['city']; ?>, <?php echo $order['state']; ?> <?php echo $order['zip']; ?></span>
                </div>
                <div class="order-row">
                    <span class="order-label">No. Telepon:</span>
                    <span class="order-value"><?php echo $order['phone']; ?></span>
                </div>
                <?php if (!empty($order['courier_note'])): ?>
                <div class="order-row">
                    <span class="order-label">Catatan Kurir:</span>
                    <span class="order-value"><?php echo $order['courier_note']; ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 30px; font-size: 14px; color: #666;">
                Jika Anda memiliki pertanyaan, silakan menghubungi kami. <a href="#" style="color: #007bff;">Klik Disini</a>
            </div>
            
    <a href="index.php" class="btn-continue">KEMBALI BERBELANJA</a>
    <button class="btn-continue" onclick="downloadReceipt()">DOWNLOAD STRUK</button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
