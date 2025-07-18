# Instalasi

Panduan ini menjelaskan langkah-langkah untuk menginstal dan mengkonfigurasi ShoeBrand Store pada server lokal Anda.

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com

Proses instalasi untuk Bus Samarinda Lestari Management System:

## Persyaratan Sistem

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi
- **Apache**: 2.4+
- **XAMPP**: Disarankan untuk kemudahan setup

## Langkah-Langkah Instalasi

1. **Kloning Repository**

   Clone atau download proyek ini ke direktori server lokal Anda.

   ```bash
   git clone https://github.com/username/UAS_PWEB.git
   ```

2. **Instalasi XAMPP**
   
   Unduh dan instal XAMPP dari situs resmi. Jalankan Apache dan MySQL dari XAMPP Control Panel.

3. **Setup Database**

   - Buka `phpMyAdmin` melalui `http://localhost/phpmyadmin`
   - Buat database baru dengan nama `bus_management`
   - Import file SQL yang terletak di `database/bus_management.sql`
     
4. **Konfigurasi Koneksi Database**

   Buka `config/database.php` dan pastikan detail koneksi sesuai dengan setup Anda:

   ```php
   define('DB_HOST', 'localhost');  // Host database Anda
   define('DB_USER', 'root');       // Username database
   define('DB_PASS', '');           // Password database
   define('DB_NAME', 'bus_management'); // Nama database
   ```

5. **Set Permission** (Opsional)

   Pastikan direktori proyek dapat diakses oleh web server (Apache) dengan cukup permission.

6. **Menjalankan Aplikasi**

   Akses aplikasi melalui browser:

   ```
   http://localhost/UAS_PWEB
   ```

## Troubleshooting

- **Fatal error: Class 'mysqli' not found**: Pastikan ext-mysqli diaktifkan di `php.ini`.
- **403 Forbidden Error**: Periksa permission folder atau konfigurasi Apache.
- **500 Internal Server Error**: Aktifkan `display_errors` di `php.ini` untuk debugging.

---

Proyek siap digunakan! Jika ada pertanyaan silakan cek dokumentasi penggunaan atau hubungi pengembang.

---
