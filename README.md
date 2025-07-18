# Bus Sekian Jaya - Management System

Sistem Manajemen Bus yang dikembangkan untuk mengelola operasional Bus Sekian Jaya, termasuk pengelolaan jadwal, pemesanan tiket, dan administrasi bus.

## 🚌 Tentang Proyek

Bus Sekian Jaya Management System adalah aplikasi web berbasis PHP yang dirancang untuk mempermudah pengelolaan transportasi bus. Sistem ini menyediakan platform lengkap untuk admin dan pengguna dalam mengelola jadwal perjalanan, pemesanan tiket, dan operasional bus.

## ✨ Fitur Utama

### 👤 Untuk Pengguna (User)

- **Registrasi & Login**: Sistem autentikasi pengguna
- **Lihat Jadwal**: Melihat jadwal keberangkatan bus yang tersedia
- **Pemesanan Tiket**: Booking tiket dengan pemilihan kursi
- **Riwayat Pemesanan**: Melihat history booking dan status pembayaran
- **Cetak Tiket**: Download/print tiket elektronik
- **Profil**: Manage profil dan update informasi

### 🛠️ Untuk Administrator

- **Dashboard Admin**: Overview sistem dan statistik
- **Manajemen User**: Kelola akun pengguna
- **Manajemen Bus**: CRUD data bus dan armada
- **Manajemen Rute**: Kelola rute perjalanan
- **Manajemen Jadwal**: Buat dan kelola jadwal keberangkatan
- **Manajemen Transaksi**: Monitor pembayaran dan transaksi
- **Laporan**: Generate berbagai laporan operasional
- **Activity Logs**: Tracking aktivitas user

## 🏗️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 4.5
- **Icons**: Font Awesome 5.15
- **Server**: Apache (XAMPP)

## 📁 Struktur Proyek

```
UAS_PWEB/
├── admin/                  # Panel administrasi
│   ├── dashboard.php
│   ├── manage_*.php       # CRUD modules
│   └── add_*.php         # Form tambah data
├── config/               # Konfigurasi
│   └── database.php      # Koneksi database
├── includes/             # File include
│   ├── auth.php         # Sistem autentikasi
│   ├── header.php       # Header template
│   └── footer.php       # Footer template
├── docs/                # Dokumentasi
│   ├── INSTALLATION.md  # Panduan instalasi
│   ├── DATABASE.md      # Dokumentasi database
│   └── USAGE.md        # Panduan penggunaan
├── assets/             # Asset statis (jika ada)
├── index.php           # Halaman utama
├── login.php           # Halaman login
├── schedules.php       # Daftar jadwal
├── booking.php         # Pemesanan tiket
├── my_bookings.php     # Riwayat booking
└── profile.php         # Profil pengguna
```

## 🚀 Quick Start

1. **Clone/Download** proyek ini
2. **Setup** XAMPP dan jalankan Apache + MySQL
3. **Import** database dari `database/bus_management.sql`
4. **Konfigurasi** database di `config/database.php`
5. **Akses** aplikasi melalui `http://localhost/UAS_PWEB`

## 📚 Dokumentasi Lengkap

- 📖 [Panduan Instalasi](docs/INSTALLATION.md)
- 🗄️ [Dokumentasi Database](docs/DATABASE.md)
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
