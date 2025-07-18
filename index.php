<?php
require_once 'config.php';

$page_title = 'Beranda';

// Get featured products
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name, 
           (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$featured_products = $stmt->fetchAll();

// Get categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold">Koleksi Sepatu & Sandal Branded Terlengkap</h1>
                    <p class="lead">Temukan sepatu dan sandal berkualitas tinggi dari brand ternama dengan harga terbaik. Gaya, kenyamanan, dan kualitas dalam satu tempat.</p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-shopping-bag me-2"></i>Belanja Sekarang
                        </a>
                        <a href="#categories" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-list me-2"></i>Lihat Kategori
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image text-center">
                    <img src="<?php echo SITE_URL; ?>/assets/img/hero-shoes.svg" alt="Hero Shoes" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="products.php" method="GET" id="search-form">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" name="search" id="search-input" 
                               placeholder="Cari sepatu, sandal, atau brand favorit Anda..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section id="categories" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Kategori Produk</h2>
                <p class="lead text-muted">Pilih kategori sesuai kebutuhan Anda</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-6 col-lg-3">
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card d-block">
                    <div class="category-icon">
                        <?php
                        $icons = [
                            'Sepatu Sneakers' => 'fas fa-running',
                            'Sepatu Formal' => 'fas fa-user-tie',
                            'Sandal Casual' => 'fas fa-flip-flops',
                            'Sandal Sport' => 'fas fa-swimmer',
                            'Sepatu Olahraga' => 'fas fa-dumbbell'
                        ];
                        echo '<i class="' . ($icons[$category['name']] ?? 'fas fa-shoe-prints') . '"></i>';
                        ?>
                    </div>
                    <h5 class="category-title"><?php echo $category['name']; ?></h5>
                    <p class="text-muted mb-0">
                        <?php
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                        $stmt->execute([$category['id']]);
                        echo $stmt->fetchColumn();
                        ?> Produk
                    </p>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5 fw-bold">Produk Terbaru</h2>
                <p class="lead text-muted">Koleksi terbaru dari brand ternama</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($featured_products as $product): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card product-card">
                    <div class="product-image">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="card-img-top">
                        <?php else: ?>
                            <img src="<?php echo SITE_URL; ?>/assets/img/no-image.jpg" alt="No Image" class="card-img-top">
                        <?php endif; ?>
                        <div class="product-badge">Baru</div>
                    </div>
                    <div class="card-body product-info">
                        <h6 class="product-title"><?php echo $product['name']; ?></h6>
                        <p class="product-description"><?php echo substr($product['description'], 0, 80) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                            <small class="text-muted"><?php echo $product['category_name']; ?></small>
                        </div>
                        <div class="mt-3">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-eye me-1"></i>Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-th-large me-2"></i>Lihat Semua Produk
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                </div>
                <h5>Pengiriman Cepat</h5>
                <p class="text-muted">Pengiriman ke seluruh Indonesia dengan jaminan aman dan cepat</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-shield-alt fa-3x text-success"></i>
                </div>
                <h5>Produk Original</h5>
                <p class="text-muted">100% produk original dari brand resmi dengan garansi keaslian</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-undo fa-3x text-warning"></i>
                </div>
                <h5>Easy Return</h5>
                <p class="text-muted">Kebijakan return mudah dalam 7 hari jika tidak sesuai</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="feature-icon mb-3">
                    <i class="fas fa-headset fa-3x text-info"></i>
                </div>
                <h5>Customer Support</h5>
                <p class="text-muted">Tim customer service siap membantu 24/7 untuk kepuasan Anda</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-dark text-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="fw-bold">Dapatkan Update Terbaru</h3>
                <p class="mb-0">Berlangganan newsletter untuk mendapatkan info promo dan produk terbaru</p>
            </div>
            <div class="col-md-6">
                <form class="d-flex">
                    <input type="email" class="form-control me-2" placeholder="Masukkan email Anda">
                    <button class="btn btn-primary" type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>