# ShoeBrand Store - E-commerce System

Sebuah sistem e-commerce yang dirancang untuk menjual sepatu dan sandal dari berbagai merek ternama dengan fitur lengkap untuk pengguna dan administrator.

## 🛍️ Tentang Proyek

ShoeBrand Store adalah aplikasi web berbasis PHP yang dikembangkan untuk memudahkan penjualan sepatu dan sandal secara online. Sistem ini menyediakan platform lengkap untuk admin dan pengguna dalam mengelola produk, transaksi, dan operasional toko online.

## ✨ Fitur Utama

### 👤 Untuk Pengguna (Customer)

- **Registrasi & Login**: Sistem autentikasi pengguna yang aman
- **Katalog Produk**: Menampilkan koleksi sepatu dan sandal terlengkap
- **Pencarian & Filter**: Cari produk berdasarkan kategori, merek, atau harga
- **Keranjang Belanja**: Tambahkan produk ke cart dan kelola pesanan
- **Checkout**: Proses pembayaran yang mudah dan aman
- **Riwayat Pembelian**: Lacak status pesanan dan history transaksi

### 🛠️ Untuk Administrator

- **Dashboard Admin**: Overview penjualan dan statistik toko
- **Manajemen Produk**: CRUD produk dengan upload gambar
- **Manajemen Kategori**: Kelola kategori produk
- **Manajemen User**: Kelola akun pengguna dan admin
- **Manajemen Transaksi**: Monitor dan kelola pesanan
- **Laporan**: Generate laporan penjualan dan analitik
- **Activity Logs**: Tracking aktivitas sistem

## 🏗️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ (shoe_store)
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **JavaScript**: Vanilla JS untuk interaktivitas
- **Icons**: Font Awesome 5.15
- **Server**: Apache (XAMPP)

## 📁 Struktur Proyek

```
ShoeBrand-Store/
├── admin/                     # Panel administrasi
│   ├── ajax/                 # AJAX handlers
│   ├── includes/             # Admin templates
│   ├── categories.php        # Manajemen kategori
│   ├── index.php            # Dashboard admin
│   ├── products.php         # Manajemen produk
│   ├── reports.php          # Laporan penjualan
│   ├── settings.php         # Pengaturan sistem
│   ├── transactions.php     # Manajemen transaksi
│   └── user.php             # Manajemen pengguna
├── ajax/                     # AJAX untuk user
│   ├── add_to_cart.php      # Tambah ke keranjang
│   ├── remove_from_cart.php # Hapus dari keranjang
│   └── update_cart.php      # Update keranjang
├── api/                      # REST API endpoints
│   └── cart_operations.php  # Operasi keranjang
├── assets/                   # Asset statis
│   ├── css/                 # Stylesheet
│   ├── js/                  # JavaScript
│   └── img/                 # Images
├── docs/                     # Dokumentasi
│   ├── DATABASE.md          # Dokumentasi database
│   ├── DEPLOYMENT.md        # Panduan deployment
│   ├── INSTALLATION.md      # Panduan instalasi
│   └── USAGE.md            # Panduan penggunaan
├── includes/                 # Template files
│   ├── header.php           # Header template
│   └── footer.php           # Footer template
├── config.php               # Konfigurasi database
├── index.php               # Halaman utama
├── login.php               # Halaman login
├── register.php            # Halaman registrasi
├── products.php            # Katalog produk
├── cart.php                # Keranjang belanja
├── checkout.php            # Proses checkout
└── payment.php             # Proses pembayaran
```

## 🚀 Quick Start

1. **Clone/Download** proyek ini
2. **Setup** XAMPP dan jalankan Apache + MySQL
3. **Buat** database `shoe_store` di phpMyAdmin
4. **Konfigurasi** database di `config.php`
5. **Akses** aplikasi melalui `http://localhost/backup.taliah`

## 🎯 Fitur Navigasi

### 🏠 Halaman Utama
- **URL**: `http://localhost/backup.taliah/index.php`
- Hero section dengan koleksi terbaru
- Kategori produk
- Produk featured
- Search bar

### 🛍️ Halaman Produk
- **URL**: `http://localhost/backup.taliah/products.php`
- Katalog lengkap produk
- Filter berdasarkan kategori
- Pencarian produk

### 🛒 Keranjang Belanja
- **URL**: `http://localhost/backup.taliah/cart.php`
- Manage items dalam cart
- Update quantity
- Lanjut ke checkout

### 💳 Checkout
- **URL**: `http://localhost/backup.taliah/checkout.php`
- Form informasi pengiriman
- Pilih metode pembayaran
- Konfirmasi pesanan

### 👤 Halaman User
- **Login**: `http://localhost/backup.taliah/login.php`
- **Register**: `http://localhost/backup.taliah/register.php`
- **Profile**: `http://localhost/backup.taliah/profile.php` (jika ada)

### 🔧 Admin Panel
- **URL**: `http://localhost/backup.taliah/admin/`
- **Dashboard**: `admin/index.php`
- **Manage Products**: `admin/products.php`
- **Manage Categories**: `admin/categories.php`
- **Manage Users**: `admin/user.php`
- **Transactions**: `admin/transactions.php`
- **Reports**: `admin/reports.php`
- **Settings**: `admin/settings.php`

## 📚 Dokumentasi Lengkap

- 📖 [Panduan Instalasi](docs/INSTALLATION.md)
- 🗄️ [Dokumentasi Database](docs/DATABASE.md)
- 🚀 [Panduan Deployment](docs/DEPLOYMENT.md)
- 📝 [Panduan Penggunaan](docs/USAGE.md)

## 👥 Default Akun

### Administrator
- **Username**: `admin`
- **Password**: `admin123`

### User Demo
- **Username**: `user`
- **Password**: `user123`

## 🔧 Persyaratan Sistem

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi
- **Apache**: 2.4+
- **Browser**: Chrome, Firefox, Safari, Edge (modern browsers)
- **Memory**: Minimal 512MB RAM
- **Storage**: Minimal 100MB disk space

## 📄 Lisensi

Proyek ini dibuat untuk keperluan akademis (UAS Pemrograman Web).

## 👨‍💻 Pengembang

Dikembangkan sebagai proyek Ujian Akhir Semester mata kuliah Pemrograman Web.

---

**⚡ Quick Links:**

- [🚀 Instalasi](docs/INSTALLATION.md)
- [📊 Database](docs/DATABASE.md)
- [📖 Penggunaan](docs/USAGE.md)
- [🐛 Issues](https://github.com/username/repo/issues)

**© 2025 Nur Taliyah - STITEK Bontang 🌟**

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com
