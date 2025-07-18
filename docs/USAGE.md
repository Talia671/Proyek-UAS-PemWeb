# Panduan Penggunaan

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com

Panduan lengkap penggunaan Bus Samarinda Lestari Management System untuk admin dan pengguna.

## 🎯 Akses Aplikasi

Buka browser dan akses: `http://localhost/UAS_PWEB`

---

## 👤 Untuk Pengguna (User)

### 1. **Registrasi & Login**

#### Cara Login:
1. Klik tombol **"Login"** di navigation bar
2. Masukkan username dan password
3. Klik **"Login"**

#### Akun Demo:
- **Username**: `user`
- **Password**: `user123`

### 2. **Melihat Jadwal**

#### Dari Halaman Utama:
- **Jadwal Terbaru**: 3 jadwal terbaru ditampilkan di homepage
- **Lihat Semua**: Klik **"Lihat Semua Jadwal"** untuk melihat seluruh jadwal

#### Dari Menu Schedules:
1. Klik **"Schedules"** di navigation bar
2. Browse semua jadwal yang tersedia
3. Informasi yang ditampilkan:
   - Rute perjalanan (Origin → Destination)
   - Waktu keberangkatan dan tiba
   - Informasi bus (merek, plat nomor)
   - Harga tiket
   - Tombol booking

### 3. **Pemesanan Tiket**

#### Step-by-Step Booking:
1. **Pilih Jadwal**: Klik **"Book Now"** pada jadwal yang diinginkan
2. **Isi Data Penumpang**:
   - Nama lengkap
   - Email
   - Nomor telepon
3. **Pilih Kursi**: Pilih nomor kursi yang tersedia (kursi yang sudah dipesan akan disabled)
4. **Pilih Metode Pembayaran**:
   - Bank Transfer
   - Credit Card
   - Cash
5. **Review Booking**: Cek ringkasan pemesanan di sidebar
6. **Submit**: Klik **"Book Ticket"**

#### Konfirmasi Booking:
- Setelah berhasil booking, Anda akan diarahkan ke halaman konfirmasi
- Simpan ID tiket untuk referensi

### 4. **Melihat Riwayat Pemesanan**

#### Akses My Bookings:
1. Login terlebih dahulu
2. Klik **"My Bookings"** di navigation bar
3. Lihat semua tiket yang pernah Anda pesan

#### Informasi yang Ditampilkan:
- Rute perjalanan
- Waktu keberangkatan dan tiba
- Informasi bus
- Nomor kursi dan nama penumpang
- Status pembayaran
- Metode pembayaran
- Total harga

#### Fitur Tiket:
- **Print Ticket**: Klik untuk download/print tiket

### 5. **Mengelola Profil**

#### Akses Profil:
1. Klik dropdown nama pengguna di navigation bar
2. Pilih **"Profile"**

#### Update Profil:
1. **Username**: Ubah nama pengguna
2. **Password**: Ganti password (opsional)
3. **Konfirmasi**: Masukkan password lama untuk konfirmasi
4. Klik **"Update Profile"**

---

## 🛠️ Untuk Administrator

### 1. **Login Admin**

#### Akun Admin Default:
- **Username**: `admin`
- **Password**: `admin123`

#### Cara Login:
1. Akses halaman login
2. Login dengan akun admin
3. Akan diarahkan ke dashboard admin

### 2. **Dashboard Admin**

#### Fitur Dashboard:
- **Overview**: Statistik sistem
- **Quick Navigation**: Akses cepat ke semua modul
- **Recent Activity**: Log aktivitas terbaru

#### Menu Sidebar:
- 🏠 **Dashboard**: Halaman utama admin
- 👥 **Manage Users**: Kelola pengguna
- 🚌 **Manage Buses**: Kelola armada bus
- 🛤️ **Manage Routes**: Kelola rute perjalanan
- 📅 **Manage Schedules**: Kelola jadwal
- 💳 **Manage Transactions**: Monitor transaksi
- 📊 **User Activity**: Log aktivitas

### 3. **Manajemen Data**

#### 👥 Manage Users
- **Lihat Semua User**: Tabel dengan data lengkap pengguna
- **Tambah User**: Klik **"Add New User"**
- **Edit User**: Klik tombol **"Edit"** pada user yang ingin diubah
- **Hapus User**: Klik tombol **"Delete"** (konfirmasi required)

#### 🚌 Manage Buses
- **Lihat Armada**: Daftar bus dengan status
- **Tambah Bus Baru**:
  1. Klik **"Add New Bus"**
  2. Isi form: Plate Number, Brand, Seat Count, Status
  3. Submit
- **Edit Bus**: Update informasi bus
- **Hapus Bus**: Remove bus dari sistem

#### 🛤️ Manage Routes
- **Lihat Rute**: Daftar semua rute perjalanan
- **Tambah Rute Baru**:
  1. Origin (kota asal)
  2. Destination (kota tujuan)
  3. Distance (jarak dalam km)
  4. Estimated Time (estimasi waktu)
- **Edit/Hapus**: Modifikasi atau hapus rute

#### 📅 Manage Schedules
- **Lihat Jadwal**: Semua jadwal dengan detail lengkap
- **Tambah Jadwal Baru**:
  1. **Pilih Rute**: Dropdown dengan info rute
  2. **Pilih Bus**: Dropdown bus yang available
  3. **Departure Time**: Waktu keberangkatan
  4. **Arrival Time**: Otomatis dihitung
  5. **Price**: Harga tiket
- **Edit/Hapus**: Modifikasi jadwal existing

#### 💳 Manage Transactions
- **Monitor Pembayaran**: Status semua transaksi
- **Update Status**: Ubah status pembayaran
- **Detail Transaksi**: Lihat detail lengkap

#### 📊 User Activity
- **Log Aktivitas**: Semua aktivitas pengguna
- **Filter**: Berdasarkan user atau jenis aktivitas
- **Export**: Download laporan aktivitas

### 4. **Tips Admin**

#### Best Practices:
1. **Regular Backup**: Backup database secara rutin
2. **Monitor Activity**: Cek log aktivitas pengguna
3. **Update Status**: Pastikan status bus dan pembayaran akurat
4. **Validasi Data**: Cek konsistensi data antar tabel

#### Common Tasks:
- **Bus Maintenance**: Update status bus ke 'maintenance'
- **Schedule Management**: Buat jadwal rutin
- **Payment Verification**: Konfirmasi pembayaran manual
- **User Support**: Bantu user dengan masalah booking

---

## 🔐 Sistem Keamanan

### Role-Based Access:
- **Admin**: Full access ke semua fitur
- **Operator**: Access terbatas (tanpa user management)
- **User**: Hanya fitur booking dan profil

### Session Management:
- Auto-logout setelah periode inactive
- Session hijacking protection
- Secure cookie handling

---

## 🐛 Troubleshooting User

### Masalah Login:
- **Wrong credentials**: Periksa username/password
- **Account not found**: Hubungi admin untuk registrasi

### Masalah Booking:
- **Seat unavailable**: Pilih kursi lain
- **Schedule not found**: Refresh halaman atau pilih jadwal lain
- **Payment issues**: Hubungi admin untuk konfirmasi

### Masalah Teknis:
- **Page not loading**: Refresh browser atau cek koneksi
- **Error messages**: Screenshot error dan laporkan ke admin
- **Missing data**: Clear browser cache

---

## 📞 Support

### Untuk Bantuan:
1. **Technical Issues**: Hubungi administrator sistem
2. **Booking Problems**: Gunakan fitur contact yang tersedia
3. **Payment Issues**: Konfirmasi dengan admin

### Jam Operasional:
- **Senin - Minggu**: 06:00 - 22:00
- **Customer Service**: 24/7

---

**🎉 Selamat menggunakan Bus Samarinda Lestari Management System!**

Jika ada pertanyaan atau masalah, jangan ragu untuk menghubungi tim support kami.
