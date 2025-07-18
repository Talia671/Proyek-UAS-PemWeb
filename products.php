<?php
require_once 'config.php';

$page_title = 'Semua Produk';

// Get filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_id) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Sort options
$sort_options = [
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC',
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC'
];

$order_by = isset($sort_options[$sort]) ? $sort_options[$sort] : $sort_options['newest'];

// Get total products for pagination
$count_sql = "SELECT COUNT(*) FROM products p $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $per_page);

// Get products
$sql = "
    SELECT p.*, c.name as category_name,
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause
    ORDER BY $order_by
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categories_stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item active">Produk</li>
                </ol>
            </nav>
            <h1 class="display-6 fw-bold">
                <?php if ($search): ?>
                    Hasil Pencarian: "<?php echo htmlspecialchars($search); ?>"
                <?php elseif ($category_id): ?>
                    <?php
                    $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                    $cat_stmt->execute([$category_id]);
                    echo $cat_stmt->fetchColumn();
                    ?>
                <?php else: ?>
                    Semua Produk
                <?php endif; ?>
            </h1>
            <p class="text-muted">Ditemukan <?php echo $total_products; ?> produk</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3 mb-4">
            <div class="filter-section">
                <h5 class="filter-title">Filter Produk</h5>
                
                <!-- Search -->
                <form action="" method="GET" class="mb-3">
                    <div class="search-box">
                        <input type="text" class="form-control" name="search" placeholder="Cari produk..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <?php if ($category_id): ?>
                        <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                    <?php endif; ?>
                </form>
                
                <!-- Categories -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Kategori</h6>
                    <div class="list-group">
                        <a href="products.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" 
                           class="list-group-item list-group-item-action <?php echo !$category_id ? 'active' : ''; ?>">
                            Semua Kategori
                        </a>
                        <?php foreach ($categories as $category): ?>
                        <a href="products.php?category=<?php echo $category['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                           class="list-group-item list-group-item-action <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                            <?php echo $category['name']; ?>
                            <span class="badge bg-secondary float-end">
                                <?php
                                $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                                $count_stmt->execute([$category['id']]);
                                echo $count_stmt->fetchColumn();
                                ?>
                            </span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Price Range -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Rentang Harga</h6>
                    <div class="list-group">
                        <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['price_range' => ''])); ?>" 
                           class="list-group-item list-group-item-action">Semua Harga</a>
                        <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['price_range' => '0-500000'])); ?>" 
                           class="list-group-item list-group-item-action">Di bawah Rp 500.000</a>
                        <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['price_range' => '500000-1000000'])); ?>" 
                           class="list-group-item list-group-item-action">Rp 500.000 - Rp 1.000.000</a>
                        <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['price_range' => '1000000-2000000'])); ?>" 
                           class="list-group-item list-group-item-action">Rp 1.000.000 - Rp 2.000.000</a>
                        <a href="<?php echo $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($_GET, ['price_range' => '2000000-'])); ?>" 
                           class="list-group-item list-group-item-action">Di atas Rp 2.000.000</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Sort Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="text-muted">Menampilkan <?php echo min($per_page, $total_products - $offset); ?> dari <?php echo $total_products; ?> produk</span>
                </div>
                <div>
                    <form action="" method="GET" class="d-flex align-items-center">
                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        <?php if ($category_id): ?>
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                        <?php endif; ?>
                        <label for="sort" class="form-label me-2 mb-0">Urutkan:</label>
                        <select name="sort" id="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                            <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Nama Z-A</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Products -->
            <?php if ($products): ?>
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <div class="product-image">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="card-img-top">
                            <?php else: ?>
                                <img src="assets/img/no-image.jpg" alt="No Image" class="card-img-top">
                            <?php endif; ?>
                            <?php if ($product['stock'] <= 5): ?>
                                <div class="product-badge bg-warning">Stok Terbatas</div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body product-info">
                            <h6 class="product-title"><?php echo $product['name']; ?></h6>
                            <p class="product-description"><?php echo substr($product['description'], 0, 80) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                                <small class="text-muted"><?php echo $product['category_name']; ?></small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                                <div class="btn-group btn-group-sm">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                                    <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Product pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
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
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4>Produk tidak ditemukan</h4>
                <p class="text-muted">Coba ubah kata kunci pencarian atau filter yang Anda gunakan</p>
                <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>