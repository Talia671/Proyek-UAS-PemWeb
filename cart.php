<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Keranjang Belanja';
$user_id = $_SESSION['user_id'];

// Get cart items
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
$cart_items = $stmt->fetchAll() ?: [];

// Calculate total
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

include 'includes/header.php';
?>

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        .header {
            background-color: #00d4aa;
            color: white;
            padding: 15px 0;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .cart-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .cart-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .cart-content {
            display: flex;
            gap: 20px;
        }
        .cart-items {
            flex: 2;
        }
        .cart-item {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .item-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .item-details {
            flex: 1;
        }
        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .item-price {
            color: #00d4aa;
            font-weight: bold;
            font-size: 1.2em;
        }
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .btn-remove {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
        }
        .summary {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        .summary h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-total {
            font-size: 1.2em;
            font-weight: bold;
            color: #00d4aa;
            border-top: 1px solid #ddd;
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
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-checkout:hover {
            background-color: #00c19a;
        }
        .select-all {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .coupon-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .coupon-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .btn-coupon {
            width: 100%;
            padding: 10px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .coupon-success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .cart-content {
                flex-direction: column;
            }
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            .item-quantity {
                justify-content: center;
            }
        }
    </style>
<!-- Cart Page Content -->
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="bg-primary text-white text-center py-3 mb-4 rounded">
                <h1><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h1>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="cart-header">
            <h2>Keranjang</h2>
            <div class="select-all">
                <input type="checkbox" id="select-all" class="item-checkbox">
                <label for="select-all">Pilih Semua (<?php echo count($cart_items); ?>)</label>
                <span style="margin-left: auto; color: #666;">Hapus</span>
            </div>
        </div>
        
        <div class="cart-content">
            <div class="cart-items">
                <form method="post" action="checkout.php" id="cart-form">
                    <?php foreach ($cart_items as $index => $item) { ?>
                    <div class="cart-item">
                        <input type="checkbox" name="selected_items[]" value="<?php echo $index; ?>" class="item-checkbox item-select">
                        
                        <img src="<?php echo $item['image_url'] ?: SITE_URL . '/assets/img/no-image.jpg'; ?>" alt="<?php echo $item['name']; ?>" class="item-image">
                        
                        <div class="item-details">
                            <div class="item-name"><?php echo $item['name']; ?></div>
                            <div class="item-price">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></div>
                            
                            <div class="item-quantity">
                                <button type="button" class="quantity-btn" onclick="changeQuantity(<?php echo $index; ?>, -1)">-</button>
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" id="qty-<?php echo $index; ?>" onchange="updateTotal()">
                                <button type="button" class="quantity-btn" onclick="changeQuantity(<?php echo $index; ?>, 1)">+</button>
                            </div>
                        </div>
                        
                        <div class="item-actions">
                            <button type="button" class="btn-remove" onclick="removeItem(<?php echo $index; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php } ?>
                </form>
            </div>
            
            <div class="summary">
                <h3>Ringkasan Belanja</h3>
                
                <div class="summary-row">
                    <span>Total (<span id="selected-count">0</span> barang)</span>
                    <span id="subtotal">Rp 0</span>
                </div>
                
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span id="total">Rp 0</span>
                </div>
                
                <div class="coupon-section">
                    <div class="coupon-success" style="display: none;" id="coupon-success">
                        <i class="fas fa-check-circle"></i> 1 kupon promo berhasil dipakai<br>
                        Dapat cashback 25.000 💰
                    </div>
                    
                    <input type="text" class="coupon-input" placeholder="Masukkan kode kupon">
                    <button type="button" class="btn-coupon">Gunakan Kupon</button>
                </div>
                
                <button type="button" class="btn-checkout" onclick="checkout()">Beli (<span id="checkout-count">0</span>)</button>
            </div>
        </div>
    </div>
    
    <script>
        const items = <?php echo json_encode($cart_items); ?>;
        
        function updateTotal() {
            let selectedItems = document.querySelectorAll('.item-select:checked');
            let total = 0;
            let count = 0;
            
            selectedItems.forEach(checkbox => {
                let index = parseInt(checkbox.value);
                let quantity = parseInt(document.getElementById('qty-' + index).value);
                total += items[index].price * quantity;
                count++;
            });
            
            document.getElementById('selected-count').textContent = count;
            document.getElementById('checkout-count').textContent = count;
            document.getElementById('subtotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        function changeQuantity(index, change) {
            let qtyInput = document.getElementById('qty-' + index);
            let newValue = parseInt(qtyInput.value) + change;
            if (newValue >= 1) {
                qtyInput.value = newValue;
                updateCartItem(items[index].cart_id, newValue);
            }
        }
        
        function removeItem(index) {
            if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                deleteCartItem(items[index].cart_id);
            }
        }
        
        function updateCartItem(cartId, quantity) {
            fetch('<?php echo SITE_URL; ?>/api/cart_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&cart_id=${cartId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTotal();
                    showMessage(data.message, 'success');
                } else {
                    showMessage(data.message, 'error');
                    location.reload(); // Reload to reset form
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Terjadi kesalahan saat memperbarui keranjang', 'error');
            });
        }
        
        function deleteCartItem(cartId) {
            fetch('<?php echo SITE_URL; ?>/api/cart_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    location.reload(); // Reload to update the cart
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Terjadi kesalahan saat menghapus item', 'error');
            });
        }
        
        function clearCart() {
            if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
                fetch('api/cart_operations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        location.reload();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Terjadi kesalahan saat mengosongkan keranjang', 'error');
                });
            }
        }
        
        function showMessage(message, type) {
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '20px';
            messageDiv.style.right = '20px';
            messageDiv.style.zIndex = '9999';
            messageDiv.style.minWidth = '300px';
            messageDiv.innerHTML = `
                <div style="padding: 15px; border-radius: 5px; color: white; background-color: ${type === 'success' ? '#28a745' : '#dc3545'}">
                    ${message}
                    <button onclick="this.parentElement.parentElement.remove()" style="float: right; background: none; border: none; color: white; font-size: 18px; cursor: pointer;">&times;</button>
                </div>
            `;
            
            document.body.appendChild(messageDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (messageDiv.parentElement) {
                    messageDiv.remove();
                }
            }, 5000);
        }
        
        function checkout() {
            let selectedItems = document.querySelectorAll('.item-select:checked');
            if (selectedItems.length === 0) {
                alert('Pilih minimal satu item untuk checkout');
                return;
            }
            
            // Submit the form
            document.getElementById('cart-form').submit();
        }
        
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.item-select');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateTotal();
        });
        
        // Individual item selection
        document.querySelectorAll('.item-select').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateTotal();
                
                // Update select all checkbox
                let allCheckboxes = document.querySelectorAll('.item-select');
                let checkedCheckboxes = document.querySelectorAll('.item-select:checked');
                document.getElementById('select-all').checked = allCheckboxes.length === checkedCheckboxes.length;
            });
        });
        
        // Initialize
        updateTotal();
    </script>
</div>

<?php include 'includes/footer.php'; ?>
