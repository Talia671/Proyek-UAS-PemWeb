<?php
require_once '../config.php';

requireAdmin();

$page_title = 'Pengaturan';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                try {
                    $settings = [
                        'site_name' => trim($_POST['site_name']),
                        'site_description' => trim($_POST['site_description']),
                        'contact_email' => trim($_POST['contact_email']),
                        'contact_phone' => trim($_POST['contact_phone']),
                        'contact_address' => trim($_POST['contact_address']),
                        'facebook_url' => trim($_POST['facebook_url']),
                        'instagram_url' => trim($_POST['instagram_url']),
                        'twitter_url' => trim($_POST['twitter_url']),
                        'whatsapp_number' => trim($_POST['whatsapp_number']),
                        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
                        'allow_registration' => isset($_POST['allow_registration']) ? 1 : 0,
                        'min_order_amount' => floatval($_POST['min_order_amount']),
                        'shipping_cost' => floatval($_POST['shipping_cost']),
                        'free_shipping_min' => floatval($_POST['free_shipping_min']),
                        'tax_rate' => floatval($_POST['tax_rate'])
                    ];
                    
                    foreach ($settings as $key => $value) {
                        $stmt = $pdo->prepare("
                            INSERT INTO settings (name, value) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE value = ?
                        ");
                        $stmt->execute([$key, $value, $value]);
                    }
                    
                    logActivity($_SESSION['user_id'], 'Memperbarui pengaturan sistem');
                    $_SESSION['success'] = 'Pengaturan berhasil diperbarui!';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Gagal memperbarui pengaturan: ' . $e->getMessage();
                }
                break;
                
            case 'clear_cache':
                try {
                    // Clear any cache files if they exist
                    $cache_dir = '../cache/';
                    if (is_dir($cache_dir)) {
                        $files = glob($cache_dir . '*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                            }
                        }
                    }
                    
                    logActivity($_SESSION['user_id'], 'Membersihkan cache sistem');
                    $_SESSION['success'] = 'Cache berhasil dibersihkan!';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Gagal membersihkan cache: ' . $e->getMessage();
                }
                break;
        }
    }
    
    header('Location: settings.php');
    exit;
}

// Get current settings
$stmt = $pdo->query("SELECT name, value FROM settings");
$settings_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Default values
$default_settings = [
    'site_name' => 'ShoeBrand Store',
    'site_description' => 'Toko sepatu online terpercaya',
    'contact_email' => 'info@shoestore.com',
    'contact_phone' => '08123456789',
    'contact_address' => 'Jl. Contoh No. 123, Jakarta',
    'facebook_url' => '',
    'instagram_url' => '',
    'twitter_url' => '',
    'whatsapp_number' => '628123456789',
    'maintenance_mode' => 0,
    'allow_registration' => 1,
    'min_order_amount' => 50000,
    'shipping_cost' => 10000,
    'free_shipping_min' => 100000,
    'tax_rate' => 0
];

// Merge with current settings
$settings = array_merge($default_settings, $settings_data);

require_once 'includes/admin_header.php';
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pengaturan Sistem</h2>
        <div>
            <button type="button" class="btn btn-outline-warning me-2" onclick="clearCache()">
                <i class="fas fa-broom me-2"></i>Bersihkan Cache
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">
                                <i class="fas fa-cog me-2"></i>Umum
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#contact" role="tab">
                                <i class="fas fa-address-book me-2"></i>Kontak
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#social" role="tab">
                                <i class="fas fa-share-alt me-2"></i>Media Sosial
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#ecommerce" role="tab">
                                <i class="fas fa-shopping-cart me-2"></i>E-Commerce
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="tab-content">
                            <!-- General Settings -->
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Website</label>
                                        <input type="text" class="form-control" name="site_name" 
                                               value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Deskripsi Website</label>
                                        <input type="text" class="form-control" name="site_description" 
                                               value="<?php echo htmlspecialchars($settings['site_description']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                                   id="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="maintenance_mode">
                                                Mode Maintenance
                                            </label>
                                            <small class="form-text text-muted d-block">
                                                Aktifkan untuk menonaktifkan sementara akses ke website
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="allow_registration" 
                                                   id="allow_registration" <?php echo $settings['allow_registration'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="allow_registration">
                                                Izinkan Registrasi Pengguna Baru
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Settings -->
                            <div class="tab-pane fade" id="contact" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Email Kontak</label>
                                        <input type="email" class="form-control" name="contact_email" 
                                               value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Telepon</label>
                                        <input type="text" class="form-control" name="contact_phone" 
                                               value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Alamat</label>
                                        <textarea class="form-control" name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Media Settings -->
                            <div class="tab-pane fade" id="social" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Facebook URL</label>
                                        <input type="url" class="form-control" name="facebook_url" 
                                               value="<?php echo htmlspecialchars($settings['facebook_url']); ?>" 
                                               placeholder="https://facebook.com/yourpage">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Instagram URL</label>
                                        <input type="url" class="form-control" name="instagram_url" 
                                               value="<?php echo htmlspecialchars($settings['instagram_url']); ?>" 
                                               placeholder="https://instagram.com/yourpage">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Twitter URL</label>
                                        <input type="url" class="form-control" name="twitter_url" 
                                               value="<?php echo htmlspecialchars($settings['twitter_url']); ?>" 
                                               placeholder="https://twitter.com/yourpage">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor WhatsApp</label>
                                        <input type="text" class="form-control" name="whatsapp_number" 
                                               value="<?php echo htmlspecialchars($settings['whatsapp_number']); ?>" 
                                               placeholder="628123456789">
                                        <small class="form-text text-muted">
                                            Format: 628123456789 (tanpa tanda +)
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- E-Commerce Settings -->
                            <div class="tab-pane fade" id="ecommerce" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Minimum Order (Rp)</label>
                                        <input type="number" class="form-control" name="min_order_amount" 
                                               value="<?php echo $settings['min_order_amount']; ?>" min="0" step="1000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Biaya Pengiriman (Rp)</label>
                                        <input type="number" class="form-control" name="shipping_cost" 
                                               value="<?php echo $settings['shipping_cost']; ?>" min="0" step="1000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Gratis Ongkir Minimal (Rp)</label>
                                        <input type="number" class="form-control" name="free_shipping_min" 
                                               value="<?php echo $settings['free_shipping_min']; ?>" min="0" step="1000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pajak (%)</label>
                                        <input type="number" class="form-control" name="tax_rate" 
                                               value="<?php echo $settings['tax_rate']; ?>" min="0" max="100" step="0.1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Sistem</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>MySQL Version:</strong></td>
                                    <td><?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Server Software:</strong></td>
                                    <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Upload Max Size:</strong></td>
                                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Post Max Size:</strong></td>
                                    <td><?php echo ini_get('post_max_size'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Memory Limit:</strong></td>
                                    <td><?php echo ini_get('memory_limit'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearCache() {
    if (confirm('Apakah Anda yakin ingin membersihkan cache sistem?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="clear_cache">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
