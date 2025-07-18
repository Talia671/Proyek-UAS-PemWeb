# Instalasi

Panduan ini menjelaskan langkah-langkah untuk menginstal dan mengkonfigurasi ShoeBrand Store pada server lokal Anda.

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com

Proses instalasi untuk ShoeBrand Store E-commerce System:

## 🔧 Persyaratan Sistem

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi
- **Apache**: 2.4+
- **XAMPP**: Disarankan untuk kemudahan setup
- **Browser**: Chrome, Firefox, Safari, Edge (modern browsers)

## 💾 Langkah-Langkah Instalasi

### 1. **Persiapan Environment**

#### Download dan Install XAMPP:
1. Unduh XAMPP dari [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP di komputer Anda
3. Jalankan XAMPP Control Panel
4. Start Apache dan MySQL services

### 2. **Setup Proyek**

#### Clone/Download Repository:
```bash
git clone https://github.com/Talia671/Proyek-UAS-PemWeb.git
```

Atau download ZIP file dan extract ke folder `htdocs/backup.taliah`

### 3. **Konfigurasi Database**

#### Buat Database:
1. Buka browser dan akses `http://localhost/phpmyadmin`
2. Klik "New" untuk membuat database baru
3. Beri nama database: `shoe_store`
4. Pilih collation: `utf8mb4_general_ci`
5. Klik "Create"

#### Import Database Structure:
1. Pilih database `shoe_store` yang telah dibuat
2. Klik tab "Import"
3. Upload file SQL (jika ada) atau buat tabel manual
4. Klik "Go" untuk mengeksekusi

### 4. **Konfigurasi Aplikasi**

#### Edit File Konfigurasi:
Buka file `config.php` dan sesuaikan pengaturan database:

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shoe_store');

// Site configuration
define('SITE_URL', 'http://localhost/backup.taliah');
define('SITE_NAME', 'ShoeBrand Store');
```

### 5. **Setup Folder Permissions**

#### Untuk Windows (XAMPP):
```bash
# Pastikan folder assets/img/products/ dapat ditulis
# Biasanya tidak perlu setting khusus di Windows
```

#### Untuk Linux/Mac:
```bash
chmod 755 /path/to/backup.taliah
chmod 777 /path/to/backup.taliah/assets/img/products/
```

### 6. **Menjalankan Aplikasi**

1. Pastikan Apache dan MySQL berjalan di XAMPP
2. Buka browser dan akses:
   ```
   http://localhost/backup.taliah
   ```
3. Aplikasi akan menampilkan halaman utama ShoeBrand Store

## 🔄 Setup Data Awal

### Buat User Admin:
1. Akses phpMyAdmin
2. Pilih database `shoe_store`
3. Jalankan query berikut:

```sql
-- Buat tabel users jika belum ada
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT DEFAULT 2,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user
INSERT INTO users (username, email, password, role_id) VALUES 
('admin', 'admin@shoebrand.com', MD5('admin123'), 1);
```

### Buat Kategori Default:
```sql
-- Buat tabel categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert kategori default
INSERT INTO categories (name, description) VALUES 
('Sepatu Sneakers', 'Sepatu kasual untuk aktivitas sehari-hari'),
('Sepatu Formal', 'Sepatu untuk acara formal dan bisnis'),
('Sandal Casual', 'Sandal santai untuk kegiatan santai'),
('Sandal Sport', 'Sandal untuk aktivitas olahraga'),
('Sepatu Olahraga', 'Sepatu khusus untuk berbagai jenis olahraga');
```

## 🔍 Troubleshooting

### Error Common:

#### 1. **Database Connection Error**
```
Connection failed: SQLSTATE[HY000] [1049] Unknown database 'shoe_store'
```
**Solusi**: Pastikan database `shoe_store` sudah dibuat di phpMyAdmin

#### 2. **Permission Denied**
```
Warning: move_uploaded_file(): failed to open stream
```
**Solusi**: Set permission folder upload:
```bash
chmod 777 assets/img/products/
```

#### 3. **Session Error**
```
Warning: session_start(): Cannot send session cookie
```
**Solusi**: Pastikan tidak ada output sebelum session_start()

#### 4. **404 Not Found**
**Solusi**: 
- Periksa nama folder di htdocs (harus `backup.taliah`)
- Pastikan Apache berjalan
- Cek file `.htaccess` jika ada

#### 5. **PHP Extension Missing**
```
Fatal error: Class 'PDO' not found
```
**Solusi**: Aktifkan ekstensi PHP:
1. Buka `php.ini` di folder XAMPP
2. Uncomment baris: `extension=pdo_mysql`
3. Restart Apache

## ✅ Verifikasi Instalasi

### Checklist Instalasi:
- [ ] XAMPP Apache dan MySQL berjalan
- [ ] Database `shoe_store` sudah dibuat
- [ ] File konfigurasi sudah disesuaikan
- [ ] Folder upload memiliki permission yang tepat
- [ ] Halaman utama dapat diakses
- [ ] Login admin berhasil

### Test Fungsionalitas:
1. **Frontend Test**: Akses `http://localhost/backup.taliah`
2. **Admin Test**: Login dengan admin/admin123
3. **Database Test**: Coba tambah kategori atau produk
4. **Upload Test**: Coba upload gambar produk

## 🚀 Langkah Selanjutnya

Setelah instalasi selesai:
1. Baca [Panduan Penggunaan](USAGE.md)
2. Lihat [Dokumentasi Database](DATABASE.md)
3. Untuk deployment: [Panduan Deployment](DEPLOYMENT.md)

---

🎉 **Selamat! ShoeBrand Store siap digunakan!**

Jika ada pertanyaan atau masalah, silakan hubungi pengembang atau buat issue di repository GitHub.

---
