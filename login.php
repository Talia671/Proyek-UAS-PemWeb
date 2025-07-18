<?php
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$page_title = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            
            logActivity($user['id'], 'User login');
            
            $_SESSION['success'] = 'Selamat datang, ' . $user['name'] . '!';
            
            // Redirect based on role
            if ($user['role_id'] == 1) {
                redirect('admin/index.php');
            } else {
                redirect('index.php');
            }
        } else {
            $error = 'Email atau password salah';
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                        <h2 class="fw-bold">Login</h2>
                        <p class="text-muted">Masuk ke akun Anda</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-2">
                            <a href="forgot-password.php" class="text-decoration-none">Lupa password?</a>
                        </p>
                        <p class="mb-0">
                            Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar sekarang</a>
                        </p>
                    </div>
                    
                    <!-- Demo accounts info -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Demo Accounts:</strong><br>
                            Admin: admin@shoebrand.com / admin123<br>
                            Customer: customer@example.com / customer123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>