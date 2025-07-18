<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Manajemen Produk';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category_id = intval($_POST['category_id']);
                $brand = trim($_POST['brand']);
                $size = trim($_POST['size']);
                $material = trim($_POST['material']);
                
                if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0) {
                    $_SESSION['error'] = 'Semua field harus diisi dengan benar!';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO products (name, description, price, stock, category_id, brand, size, material, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$name, $description, $price, $stock, $category_id, $brand, $size, $material]);
                        
                        $product_id = $pdo->lastInsertId();
                        
                        // Handle image upload
                        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                            $upload_dir = '../assets/img/products/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            $upload_errors = [];
                            $first_image_uploaded = false;
                            
                            foreach ($_FILES['images']['name'] as $key => $filename) {
                                if (!empty($filename)) {
                                    // Check for upload errors
                                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                                        $upload_errors[] = "Error uploading $filename: " . $_FILES['images']['error'][$key];
                                        continue;
                                    }
                                    
                                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                    
                                    if (in_array($file_ext, $allowed_ext)) {
                                        $new_filename = $product_id . '_' . time() . '_' . $key . '.' . $file_ext;
                                        $upload_path = $upload_dir . $new_filename;
                                        
                                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_path)) {
                                            // Set first successfully uploaded image as primary
                                            $is_primary = !$first_image_uploaded ? 1 : 0;
                                            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                                            $stmt->execute([$product_id, 'assets/img/products/' . $new_filename, $is_primary]);
                                            $first_image_uploaded = true;
                                        } else {
                                            $upload_errors[] = "Gagal memindahkan file $filename";
                                        }
                                    } else {
                                        $upload_errors[] = "Format file $filename tidak didukung";
                                    }
                                }
                            }
                            
                            if (!empty($upload_errors)) {
                                $_SESSION['error'] = implode('<br>', $upload_errors);
                            }
                        }
                        
                        logActivity($_SESSION['user_id'], 'Menambahkan produk: ' . $name);
                        $_SESSION['success'] = 'Produk berhasil ditambahkan!';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Gagal menambahkan produk: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category_id = intval($_POST['category_id']);
                $brand = trim($_POST['brand']);
                $size = trim($_POST['size']);
                $material = trim($_POST['material']);
                
                if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0) {
                    $_SESSION['error'] = 'Semua field harus diisi dengan benar!';
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            UPDATE products 
                            SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, brand = ?, size = ?, material = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$name, $description, $price, $stock, $category_id, $brand, $size, $material, $id]);
                        
                        // Handle new image uploads
                        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                            $upload_dir = '../assets/img/products/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            $upload_errors = [];
                            // Check if product has any existing images
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
                            $stmt->execute([$id]);
                            $existing_images = $stmt->fetchColumn();
                            
                            $first_image_uploaded = false;
                            
                            foreach ($_FILES['images']['name'] as $key => $filename) {
                                if (!empty($filename)) {
                                    // Check for upload errors
                                    if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                                        $upload_errors[] = "Error uploading $filename: " . $_FILES['images']['error'][$key];
                                        continue;
                                    }
                                    
                                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                    
                                    if (in_array($file_ext, $allowed_ext)) {
                                        $new_filename = $id . '_' . time() . '_' . $key . '.' . $file_ext;
                                        $upload_path = $upload_dir . $new_filename;
                                        
                                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_path)) {
                                            // Set as primary if it's the first image for this product
                                            $is_primary = ($existing_images == 0 && !$first_image_uploaded) ? 1 : 0;
                                            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                                            $stmt->execute([$id, 'assets/img/products/' . $new_filename, $is_primary]);
                                            $first_image_uploaded = true;
                                        } else {
                                            $upload_errors[] = "Gagal memindahkan file $filename";
                                        }
                                    } else {
                                        $upload_errors[] = "Format file $filename tidak didukung";
                                    }
                                }
                            }
                            
                            if (!empty($upload_errors)) {
                                $_SESSION['error'] = implode('<br>', $upload_errors);
                            }
                        }
                        
                        logActivity($_SESSION['user_id'], 'Mengubah produk: ' . $name);
                        $_SESSION['success'] = 'Produk berhasil diperbarui!';
                    } catch (Exception $e) {
                        $_SESSION['error'] = 'Gagal memperbarui produk: ' . $e->getMessage();
                    }
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                try {
                    // Get product name for log
                    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $product_name = $stmt->fetchColumn();
                    
                    // Delete product images from database and files
                    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
                    $stmt->execute([$id]);
                    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($images as $image_url) {
                        $file_path = '../' . $image_url;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    
                    $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
                    $pdo->prepare("DELETE FROM carts WHERE product_id = ?")->execute([$id]);
                    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
                    
                    logActivity($_SESSION['user_id'], 'Menghapus produk: ' . $product_name);
                    $_SESSION['success'] = 'Produk berhasil dihapus!';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Gagal menghapus produk: ' . $e->getMessage();
                }
                break;
                
            case 'delete_image':
                $image_id = intval($_POST['image_id']);
                try {
                    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ?");
                    $stmt->execute([$image_id]);
                    $image_url = $stmt->fetchColumn();
                    
                    if ($image_url) {
                        $file_path = '../' . $image_url;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        
                        $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$image_id]);
                        $_SESSION['success'] = 'Gambar berhasil dihapus!';
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Gagal menghapus gambar: ' . $e->getMessage();
                }
                break;
        }
    }
    
    header('Location: products.php');
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sort options
$sort_options = [
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'stock_asc' => 'p.stock ASC',
    'stock_desc' => 'p.stock DESC'
];

$order_clause = 'ORDER BY ' . ($sort_options[$sort] ?? $sort_options['newest']);

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Get products
$query = "
    SELECT p.*, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    $where_clause 
    $order_clause 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter and form
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Produk</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-2"></i>Tambah Produk
        </button>
    </div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ukuran</label>
                            <input type="text" class="form-control" name="size" placeholder="Contoh: 38, 39, 40">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga *</label>
                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok *</label>
                            <input type="number" class="form-control" name="stock" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Material</label>
                            <input type="text" class="form-control" name="material" placeholder="Contoh: Kulit asli, Canvas, Sintetis">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi *</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                            <small class="text-muted">Pilih beberapa gambar. Gambar pertama akan menjadi gambar utama.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand" id="edit_brand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ukuran</label>
                            <input type="text" class="form-control" name="size" id="edit_size">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga *</label>
                            <input type="number" class="form-control" name="price" id="edit_price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok *</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Material</label>
                            <input type="text" class="form-control" name="material" id="edit_material">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi *</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tambah Gambar Baru</label>
                            <input type="file" class="form-control" name="images[]" multiple accept="image/*">
                            <small class="text-muted">Pilih gambar baru untuk ditambahkan ke produk.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Product Images Modal -->
<div class="modal fade" id="productImagesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gambar Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="productImagesContainer">
                    <!-- Images will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus produk <strong id="deleteProductName"></strong>?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteProductId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_brand').value = product.brand || '';
    document.getElementById('edit_size').value = product.size || '';
    document.getElementById('edit_material').value = product.material || '';
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function deleteProduct(id, name) {
    document.getElementById('deleteProductId').value = id;
    document.getElementById('deleteProductName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function viewProductImages(productId) {
    fetch(`ajax/get_product_images.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('productImagesContainer');
            
            if (data.success && data.images.length > 0) {
                let html = '<div class="row g-3">';
                data.images.forEach(image => {
                    html += `
                        <div class="col-md-4">
                            <div class="card">
                                <img src="<?php echo SITE_URL; ?>/${image.image_url}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">${image.is_primary ? 'Gambar Utama' : 'Gambar Tambahan'}</small>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteImage(${image.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-center text-muted">Tidak ada gambar untuk produk ini</p>';
            }
            
            new bootstrap.Modal(document.getElementById('productImagesModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat gambar produk');
        });
}

function deleteImage(imageId) {
    if (confirm('Apakah Anda yakin ingin menghapus gambar ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_image">
            <input type="hidden" name="image_id" value="${imageId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Cari nama, deskripsi, atau brand...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="category">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Urutkan</label>
                    <select class="form-select" name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Nama Z-A</option>
                        <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                        <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                        <option value="stock_asc" <?php echo $sort == 'stock_asc' ? 'selected' : ''; ?>>Stok Terendah</option>
                        <option value="stock_desc" <?php echo $sort == 'stock_desc' ? 'selected' : ''; ?>>Stok Tertinggi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Produk (<?php echo number_format($total_products); ?> produk)</h5>
        </div>
        <div class="card-body p-0">
            <?php if ($products): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Brand</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="<?php echo SITE_URL . '/' . ($product['primary_image'] ?: 'assets/img/no-image.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>...</small>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['brand'] ?? ''); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td>
                                <span class="badge <?php echo $product['stock'] <= 5 ? 'bg-warning' : 'bg-success'; ?>">
                                    <?php echo $product['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'Tersedia' : 'Habis'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-info" 
                                            onclick="viewProductImages(<?php echo $product['id']; ?>)">
                                        <i class="fas fa-images"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada produk ditemukan</h5>
                <p class="text-muted">Coba ubah filter pencarian atau tambah produk baru</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
