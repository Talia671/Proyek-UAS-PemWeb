<?php
require_once __DIR__ . '/../../config.php';

requireAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin ' . SITE_NAME : 'Admin ' . SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            z-index: 1000;
            transition: left 0.3s ease;
        }
        .admin-sidebar.show {
            left: 0;
        }
        .admin-content {
            margin-left: 0;
            min-height: 100vh;
        }
        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .admin-header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        .hamburger-menu {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .hamburger-menu:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.5);
            background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        }
        .hamburger-lines {
            width: 20px;
            height: 14px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .hamburger-lines span {
            width: 100%;
            height: 2px;
            background: white;
            border-radius: 1px;
            transition: all 0.3s ease;
        }
        .hamburger-menu.active .hamburger-lines span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        .hamburger-menu.active .hamburger-lines span:nth-child(2) {
            opacity: 0;
        }
        .hamburger-menu.active .hamburger-lines span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .admin-brand {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .pulse-effect {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            }
            50% {
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.6);
            }
            100% {
                box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu Button -->
    <button class="hamburger-menu" id="hamburger-menu">
        <div class="hamburger-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- Sidebar -->
    <nav class="admin-sidebar" id="admin-sidebar">
        <div class="p-3">
            <div class="admin-brand">
                <a href="<?php echo SITE_URL; ?>/admin/" class="navbar-brand text-white fw-bold d-block text-center">
                    <i class="fas fa-shoe-prints me-2"></i>
                    Admin Panel
                </a>
            </div>
            
            <ul class="nav flex-column sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/products.php">
                        <i class="fas fa-box me-2"></i>Manajemen Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/categories.php">
                        <i class="fas fa-tags me-2"></i>Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/transactions.php">
                        <i class="fas fa-receipt me-2"></i>Transaksi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/user.php">
                        <i class="fas fa-users me-2"></i>Pengguna
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Laporan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/settings.php">
                        <i class="fas fa-cog me-2"></i>Pengaturan
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="admin-content">
        <!-- Header -->
        <header class="admin-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="mb-0" style="margin-left: 80px;"><?php echo $page_title ?? 'Admin Panel'; ?></h4>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i><?php echo $_SESSION['user_name']; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="p-0">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>