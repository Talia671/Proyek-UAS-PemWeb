</main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-shoe-prints me-2"></i>
                        <?php echo SITE_NAME; ?>
                    </h5>
                    <p class="text-muted">Toko sepatu dan sandal branded terpercaya dengan koleksi terlengkap dan kualitas terbaik.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6 class="fw-bold mb-3">Kategori</h6>
                    <ul class="list-unstyled">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name LIMIT 5");
                        while ($category = $stmt->fetch()):
                        ?>
                        <li><a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>" class="text-muted text-decoration-none"><?php echo $category['name']; ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3">Layanan</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Bantuan</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Cara Belanja</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Kebijakan Return</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3">Kontak</h6>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Jl. Fashion Street No. 123, Jakarta</li>
                        <li><i class="fas fa-phone me-2"></i> +62 812-3456-7890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@shoebrand.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <img src="<?php echo SITE_URL; ?>/assets/img/payment-methods.png" alt="Payment Methods" class="img-fluid" style="max-height: 30px;">
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($extra_js)): ?>
        <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html>