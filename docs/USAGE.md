# Panduan Penggunaan

Panduan ini menjelaskan cara menggunakan fitur-fitur dari aplikasi ShoeBrand Store, termasuk manajemen produk, keranjang belanja, dan proses checkout.

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com

Panduan lengkap penggunaan ShoeBrand Store E-commerce System untuk admin dan customer.

## 🎯 Akses Aplikasi

Buka browser dan akses: `http://localhost/backup.taliah`

---

## 🛍️ Untuk Customer (Pengguna)

### 1. **Registrasi & Login**

#### Cara Registrasi:
1. Klik tombol **"Register"** di navigation bar
2. Isi form registrasi:
   - Username (unik)
   - Email
   - Password
   - Konfirmasi password
3. Klik **"Register"**
4. Login dengan akun yang baru dibuat

#### Cara Login:
1. Klik tombol **"Login"** di navigation bar
2. Masukkan username dan password
3. Klik **"Login"**

#### Akun Demo:
- **Username**: `user`
- **Password**: `user123`

### 2. **Browsing Produk**

#### Halaman Utama:
- **Hero Section**: Banner utama dengan koleksi terbaru
- **Kategori Produk**: Pilih kategori sepatu/sandal
- **Produk Terbaru**: 8 produk terbaru ditampilkan
- **Search Bar**: Cari produk dengan kata kunci

#### Halaman Produk:
1. Klik **"Lihat Semua Produk"** atau menu **"Products"**
2. Browse semua produk yang tersedia
3. Filter berdasarkan:
   - Kategori (Sepatu Sneakers, Formal, Sandal, dll)
   - Pencarian nama produk
   - Range harga

#### Detail Produk:
- Klik produk untuk melihat detail lengkap
- Informasi yang ditampilkan:
  - Gambar produk (multiple images)
  - Nama dan deskripsi
  - Harga
  - Kategori
  - Stok tersedia
  - Spesifikasi produk

### 3. **Keranjang Belanja**

#### Menambah ke Keranjang:
1. Pada halaman produk, klik **"Add to Cart"**
2. Pilih quantity yang diinginkan
3. Produk akan masuk ke keranjang
4. Notifikasi konfirmasi akan muncul

#### Mengelola Keranjang:
1. Klik icon **"Cart"** di navigation bar
2. Lihat semua item dalam keranjang
3. Update quantity atau hapus item
4. Cek total harga keseluruhan

#### Fitur Keranjang:
- **Update Quantity**: Ubah jumlah item
- **Remove Item**: Hapus item dari keranjang
- **Continue Shopping**: Kembali ke katalog produk
- **Checkout**: Lanjut ke pembayaran

### 4. **Proses Checkout**

#### Langkah Checkout:
1. Dari halaman cart, klik **"Checkout"**
2. **Informasi Pengiriman**:
   - Nama lengkap
   - Alamat pengiriman
   - Nomor telepon
   - Catatan khusus (opsional)
3. **Pilih Metode Pembayaran**:
   - Transfer Bank
   - Credit Card
   - E-wallet
   - Cash on Delivery
4. **Review Pesanan**:
   - Cek item yang dipesan
   - Verifikasi alamat pengiriman
   - Konfirmasi total pembayaran
5. **Place Order**: Klik untuk menyelesaikan pesanan

#### Konfirmasi Pesanan:
- Setelah berhasil checkout, akan muncul halaman konfirmasi
- Order ID akan diberikan untuk tracking
- Email konfirmasi akan dikirim (jika ada)

### 5. **Riwayat Pembelian**

#### Akses Order History:
1. Login terlebih dahulu
2. Klik **"My Orders"** di dropdown user
3. Lihat semua pesanan yang pernah dibuat

#### Informasi yang Ditampilkan:
- Order ID dan tanggal pemesanan
- Daftar produk yang dibeli
- Status pesanan (Pending, Processing, Shipped, Delivered)
- Total pembayaran
- Informasi pengiriman
- Metode pembayaran

---

## 🛠️ Untuk Administrator

### 1. **Login Admin**

#### Akun Admin Default:
- **Username**: `admin`
- **Password**: `admin123`

#### Cara Login:
1. Akses halaman login
2. Login dengan akun admin
3. Akan diarahkan ke dashboard admin di `/admin/`

### 2. **Dashboard Admin**

#### URL Admin: `http://localhost/backup.taliah/admin/`

#### Fitur Dashboard:
- **Overview**: Statistik penjualan dan sistem
- **Quick Stats**: Total produk, kategori, user, transaksi
- **Recent Activity**: Log aktivitas terbaru
- **Sales Chart**: Grafik penjualan (jika ada)

#### Menu Sidebar (Berdasarkan File yang Ada):
- 🏠 **Dashboard**: `admin/index.php`
- 👥 **Manage Users**: `admin/user.php`
- 🏷️ **Manage Categories**: `admin/categories.php`
- 🛍️ **Manage Products**: `admin/products.php`
- 💳 **Manage Transactions**: `admin/transactions.php`
- 📊 **Reports**: `admin/reports.php`
- ⚙️ **Settings**: `admin/settings.php`

### 3. **Manajemen Data**

#### 👥 Manage Users
- **Lihat Semua User**: Tabel dengan data lengkap pengguna
- **Tambah User**: Klik **"Add New User"**
- **Edit User**: Klik tombol **"Edit"** pada user yang ingin diubah
- **Hapus User**: Klik tombol **"Delete"** (konfirmasi required)
- **Role Management**: Atur role admin/customer

#### 🏷️ Manage Categories
- **Lihat Kategori**: Daftar semua kategori produk
- **Tambah Kategori Baru**:
  1. Klik **"Add New Category"**
  2. Isi nama kategori
  3. Tambahkan deskripsi
  4. Submit
- **Edit Kategori**: Update informasi kategori
- **Hapus Kategori**: Remove kategori (pastikan tidak ada produk terkait)

#### 🛍️ Manage Products
- **Lihat Produk**: Daftar semua produk dengan gambar
- **Tambah Produk Baru**:
  1. Klik **"Add New Product"**
  2. Isi form produk:
     - Nama produk
     - Deskripsi
     - Kategori
     - Harga
     - Stok
     - Upload gambar (multiple)
  3. Submit
- **Edit Produk**: Update informasi produk
- **Hapus Produk**: Remove produk dari sistem
- **Manage Images**: Upload/hapus gambar produk

#### 💳 Manage Transactions
- **Monitor Pesanan**: Status semua transaksi
- **Update Status**: Ubah status pesanan
- **Detail Transaksi**: Lihat detail lengkap pesanan
- **Payment Verification**: Konfirmasi pembayaran manual

#### 📊 Reports
- **Sales Report**: Laporan penjualan per periode
- **Product Report**: Produk terlaris
- **Customer Report**: Data customer aktif
- **Export**: Download laporan dalam format Excel/PDF

### 4. **Tips Admin**

#### Best Practices:
1. **Regular Backup**: Backup database secara rutin
2. **Monitor Stock**: Cek stok produk secara berkala
3. **Update Status**: Pastikan status pesanan akurat
4. **Image Optimization**: Kompres gambar untuk performance
5. **Security**: Ganti password default admin

#### Common Tasks:
- **Product Management**: Update produk dan harga
- **Order Processing**: Proses pesanan customer
- **Customer Support**: Bantu customer dengan masalah
- **Content Management**: Update kategori dan deskripsi

---

## 🔐 Sistem Keamanan

### Role-Based Access:
- **Admin**: Full access ke semua fitur
- **Customer**: Hanya fitur shopping dan profil

### Session Management:
- Auto-logout setelah periode inactive
- Session hijacking protection
- Secure cookie handling

### Data Protection:
- Password hashing (MD5, recommended: bcrypt)
- SQL injection protection
- XSS protection
- Input validation

---

## 🐛 Troubleshooting User

### Masalah Login:
- **Wrong credentials**: Periksa username/password
- **Account not found**: Pastikan sudah registrasi
- **Session expired**: Login ulang

### Masalah Shopping:
- **Add to cart failed**: Cek stok produk
- **Checkout error**: Pastikan form terisi lengkap
- **Payment issues**: Hubungi admin untuk konfirmasi

### Masalah Teknis:
- **Page not loading**: Refresh browser atau cek koneksi
- **Image not showing**: Cek folder assets/img/products/
- **Error messages**: Screenshot error dan laporkan ke admin

---

## 📞 Support

### Untuk Bantuan:
1. **Technical Issues**: Hubungi administrator sistem
2. **Shopping Problems**: Gunakan fitur contact yang tersedia
3. **Payment Issues**: Konfirmasi dengan admin

### Kontak:
- **Email**: nurtaliyah164@gmail.com
- **Developer**: Nur Taliyah (NIM: 202312030)

---

**🎉 Selamat menggunakan ShoeBrand Store!**

Jika ada pertanyaan atau masalah, jangan ragu untuk menghubungi tim support kami.
