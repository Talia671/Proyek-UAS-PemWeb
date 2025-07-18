<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Checkout';
$user_id = $_SESSION['user_id'];

// Debug session info
echo "<!-- Debug: User ID = $user_id, Session: " . print_r($_SESSION, true) . " -->";

// Initialize address data (using default values)
$address = [
    'address' => '',
    'city' => '',
    'state' => '',
    'zip' => '',
    'phone' => ''
];

// Get selected items from cart
if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    $selected_indices = $_POST['selected_items'];
    $placeholders = str_repeat('?,', count($selected_indices) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, c.product_id,
               p.name, p.price, p.stock,
               (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
        FROM carts c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ? AND c.id IN ($placeholders)
        ORDER BY c.id DESC
    ");
    
    $params = array_merge([$user_id], $selected_indices);
    $stmt->execute($params);
    $selected_items = $stmt->fetchAll();
} else {
    // If no specific items selected, get all cart items
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
    $selected_items = $stmt->fetchAll();
}

// Calculate total
$total = 0;
foreach ($selected_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle empty cart
if (empty($selected_items)) {
    // Try to add a sample product for testing
    try {
        // Get first available product
        $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE stock > 0 LIMIT 1");
        $stmt->execute();
        $product = $stmt->fetch();
        
        if ($product) {
            // Add to cart
            $stmt = $pdo->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $product['id']]);
            
            // Reload the page to show the added item
            echo "<script>window.location.href = 'checkout.php';</script>";
            exit;
        }
    } catch (Exception $e) {
        // Ignore errors
    }
    
    // If still no items, show message but don't redirect
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; margin: 20px; border-radius: 5px;'>";
    echo "Keranjang kosong. <a href='products.php'>Tambah produk</a> terlebih dahulu.";
    echo "</div>";
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="bg-primary text-white text-center py-3 mb-4 rounded">
                <h1><i class="fas fa-credit-card"></i> Checkout</h1>
            </div>
        </div>
    </div>

    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .checkout-row {
            display: flex;
            gap: 20px;
        }
        .checkout-left {
            flex: 2;
        }
        .checkout-right {
            flex: 1;
        }
        .address-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .payment-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover {
            border-color: #00d4aa;
            background-color: #f8f9fa;
        }
        .payment-option.selected {
            border-color: #00d4aa;
            background-color: #e8f5e8;
        }
        .payment-icon {
            width: 40px;
            height: 40px;
            background-color: #f0f0f0;
            border-radius: 4px;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
        }
        .order-summary {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .product-info {
            flex: 1;
        }
        .product-name {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        .product-price {
            font-size: 14px;
            color: #666;
        }
        .product-quantity {
            font-size: 12px;
            color: #999;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .summary-total {
            font-size: 16px;
            font-weight: bold;
            color: #00d4aa;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background-color: #00d4aa;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            background-color: #00c19a;
        }
        @media (max-width: 768px) {
            .checkout-row {
                flex-direction: column;
            }
        }
    </style>

    <div class="checkout-container">
        <div class="checkout-row">
            <div class="checkout-left">
                <form action="process_checkout.php" method="post" id="checkout-form">
                    <!-- Hidden inputs for selected items -->
                    <?php foreach ($selected_items as $item): ?>
                        <input type="hidden" name="selected_items[]" value="<?php echo $item['cart_id']; ?>">
                    <?php endforeach; ?>
                    
                    <!-- Address Section -->
                    <div class="address-section">
                        <div class="section-title">📍 Alamat Pengiriman</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Alamat Lengkap</label>
                                <input type="text" name="address" class="form-control" placeholder="Masukkan alamat lengkap" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kota</label>
                                <input type="text" name="city" class="form-control" placeholder="Kota" required>
                            </div>
                            <div class="form-group">
                                <label>Provinsi</label>
                                <input type="text" name="state" class="form-control" placeholder="Provinsi" required>
                            </div>
                            <div class="form-group">
                                <label>Kode Pos</label>
                                <input type="text" name="zip" class="form-control" placeholder="Kode Pos" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>No Telepon</label>
                                <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
                            </div>
                        </div>
                    </div>

                    <!-- Courier Note Section -->
                    <div class="address-section">
                        <div class="section-title">📝 Pesan untuk Kurir</div>
                        <textarea name="courier_note" class="form-control" rows="3" placeholder="Tambahkan catatan untuk kurir (opsional)..."></textarea>
                    </div>

                    <!-- Payment Method Section -->
                    <div class="address-section">
                        <div class="section-title">💳 Metode Pembayaran</div>
                        <div class="payment-options">
                            <div class="payment-option" data-value="BRI">
                                <div class="payment-icon">BRI</div>
                                <div>
                                    <div style="font-weight: 500;">BRI Virtual Account</div>
                                    <div style="font-size: 12px; color: #666;">Bayar melalui ATM, Internet Banking, atau Mobile Banking BRI</div>
                                </div>
                            </div>
                            <div class="payment-option" data-value="Alfamart">
                                <div class="payment-icon">ALF</div>
                                <div>
                                    <div style="font-weight: 500;">Alfamart / Alfamidi / Lawson / Dan+Dan</div>
                                    <div style="font-size: 12px; color: #666;">Bayar di counter Alfamart, Alfamidi, Lawson, atau Dan+Dan</div>
                                </div>
                            </div>
                            <div class="payment-option" data-value="BCA">
                                <div class="payment-icon">BCA</div>
                                <div>
                                    <div style="font-weight: 500;">BCA Virtual Account</div>
                                    <div style="font-size: 12px; color: #666;">Bayar melalui ATM, KlikBCA, atau BCA Mobile</div>
                                </div>
                            </div>
                            <div class="payment-option" data-value="Mandiri">
                                <div class="payment-icon">MDR</div>
                                <div>
                                    <div style="font-weight: 500;">Mandiri Virtual Account</div>
                                    <div style="font-size: 12px; color: #666;">Bayar melalui ATM, Internet Banking, atau Livin' by Mandiri</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" id="payment_method" required>
                    </div>
                </form>
            </div>

            <div class="checkout-right">
                <div class="order-summary">
                    <div class="section-title">Ringkasan Pesanan</div>
                    
                    <?php if (!empty($selected_items)): ?>
                        <?php foreach ($selected_items as $item): ?>
                            <div class="product-item">
                    <img src="<?php echo $item['image_url'] ?: SITE_URL . '/assets/img/no-image.jpg'; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?php echo $item['name']; ?></div>
                                    <div class="product-quantity"><?php echo $item['quantity']; ?> barang</div>
                                    <div class="product-price">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada item yang dipilih.</p>
                    <?php endif; ?>
                    
                    <div class="summary-row">
                        <span>Total (<?php echo count($selected_items); ?> barang)</span>
                        <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="summary-row summary-total">
                        <span>Total Tagihan</span>
                        <span>Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    
                    <button type="submit" form="checkout-form" class="btn-checkout">Bayar Sekarang</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Payment method selection
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Set hidden input value
                document.getElementById('payment_method').value = this.dataset.value;
            });
        });
    </script>
</div>

<?php include 'includes/footer.php'; ?>

