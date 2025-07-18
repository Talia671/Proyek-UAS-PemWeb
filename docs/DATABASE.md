# Dokumentasi Database

Database `shoe_store` menyimpan semua informasi yang diperlukan untuk manajemen toko sepatu online, termasuk data pengguna, produk, kategori, pesanan, dan transaksi.

## 👤 Informasi Pribadi

- **Nama**: Nur Taliyah
- **NIM**: 202312030
- **Email**: nurtaliyah164@gmail.com

ShoeBrand Store menggunakan database MySQL dengan struktur relasional yang terorganisir dengan baik untuk mendukung operasi e-commerce.

## 🗄️ Struktur Database

Database `shoe_store` terdiri dari beberapa tabel utama yang saling berelasi:

### 📋 Daftar Tabel

1. **roles** - Role pengguna (Admin, Customer)
2. **users** - Data pengguna sistem (admin dan customer)
3. **categories** - Kategori produk (sepatu, sandal, dll)
4. **products** - Data produk sepatu dan sandal
5. **product_images** - Gambar produk (multiple images per product)
6. **carts** - Item dalam keranjang belanja
7. **orders** - Pesanan pelanggan
8. **order_items** - Item dalam pesanan
9. **activity_logs** - Log aktivitas sistem
10. **settings** - Pengaturan sistem

### 📊 Tabel Berdasarkan Fungsi

#### Core Tables:
- `roles` - Role management (Admin/Customer)
- `users` - Sistem autentikasi dan user management
- `categories` - Kategori produk sepatu dan sandal
- `products` - Master produk dengan informasi lengkap
- `product_images` - Gambar produk (multiple images per product)
- `carts` - Keranjang belanja untuk proses pembelian
- `orders` - Master pesanan pelanggan
- `order_items` - Detail item dalam pesanan
- `activity_logs` - Log aktivitas sistem untuk audit
- `settings` - Pengaturan sistem toko
- `activity_logs` - Log aktivitas sistem
- `settings` - Pengaturan sistem toko

---

## 🔍 Detail Struktur Tabel

### 1. Table: `users`

**Deskripsi**: Menyimpan data pengguna sistem (admin dan user)

| Field      | Type                            | Null | Key | Extra             | Description       |
| ---------- | ------------------------------- | ---- | --- | ----------------- | ----------------- |
| id         | int(11)                         | NO   | PRI | auto_increment    | ID unik pengguna  |
| username   | varchar(50)                     | NO   | UNI |                   | Nama pengguna     |
| password   | varchar(255)                    | NO   |     |                   | Password (hashed) |
| role       | enum('admin','operator','user') | NO   |     | 'user'            | Role pengguna     |
| created_at | timestamp                       | NO   |     | CURRENT_TIMESTAMP | Waktu dibuat      |

**Indexes:**

- PRIMARY KEY (`id`)
- UNIQUE KEY (`username`)

---

### 2. Table: `routes`

**Deskripsi**: Menyimpan data rute perjalanan bus

| Field          | Type         | Null | Key | Extra          | Description               |
| -------------- | ------------ | ---- | --- | -------------- | ------------------------- |
| id             | int(11)      | NO   | PRI | auto_increment | ID unik rute              |
| origin         | varchar(100) | NO   |     |                | Kota asal                 |
| destination    | varchar(100) | NO   |     |                | Kota tujuan               |
| distance_km    | int(11)      | NO   |     |                | Jarak tempuh (km)         |
| estimated_time | varchar(50)  | NO   |     |                | Estimasi waktu perjalanan |

**Indexes:**

- PRIMARY KEY (`id`)

---

### 3. Table: `buses`

**Deskripsi**: Menyimpan data armada bus

| Field        | Type                            | Null | Key | Extra          | Description          |
| ------------ | ------------------------------- | ---- | --- | -------------- | -------------------- |
| id           | int(11)                         | NO   | PRI | auto_increment | ID unik bus          |
| plate_number | varchar(20)                     | NO   | UNI |                | Nomor plat kendaraan |
| brand        | varchar(50)                     | NO   |     |                | Merek bus            |
| seat_count   | int(11)                         | NO   |     |                | Jumlah kursi         |
| status       | enum('available','maintenance') | NO   |     | 'available'    | Status bus           |

**Indexes:**

- PRIMARY KEY (`id`)
- UNIQUE KEY (`plate_number`)

---

### 4. Table: `schedules`

**Deskripsi**: Menyimpan jadwal keberangkatan bus

| Field          | Type          | Null | Key | Extra          | Description         |
| -------------- | ------------- | ---- | --- | -------------- | ------------------- |
| id             | int(11)       | NO   | PRI | auto_increment | ID unik jadwal      |
| route_id       | int(11)       | NO   | MUL |                | ID rute (FK)        |
| bus_id         | int(11)       | NO   | MUL |                | ID bus (FK)         |
| departure_time | datetime      | NO   |     |                | Waktu keberangkatan |
| arrival_time   | datetime      | NO   |     |                | Waktu tiba          |
| price          | decimal(10,2) | NO   |     |                | Harga tiket         |

**Indexes:**

- PRIMARY KEY (`id`)
- FOREIGN KEY (`route_id`) REFERENCES `routes(id)`
- FOREIGN KEY (`bus_id`) REFERENCES `buses(id)`

---

### 5. Table: `passengers`

**Deskripsi**: Menyimpan data penumpang

| Field | Type         | Null | Key | Extra          | Description       |
| ----- | ------------ | ---- | --- | -------------- | ----------------- |
| id    | int(11)      | NO   | PRI | auto_increment | ID unik penumpang |
| name  | varchar(100) | NO   |     |                | Nama lengkap      |
| email | varchar(100) | NO   |     |                | Email penumpang   |
| phone | varchar(20)  | NO   |     |                | Nomor telepon     |

**Indexes:**

- PRIMARY KEY (`id`)

---

### 6. Table: `tickets`

**Deskripsi**: Menyimpan data tiket pemesanan

| Field        | Type                       | Null | Key | Extra          | Description       |
| ------------ | -------------------------- | ---- | --- | -------------- | ----------------- |
| id           | int(11)                    | NO   | PRI | auto_increment | ID unik tiket     |
| schedule_id  | int(11)                    | NO   | MUL |                | ID jadwal (FK)    |
| passenger_id | int(11)                    | NO   | MUL |                | ID penumpang (FK) |
| seat_number  | int(11)                    | NO   |     |                | Nomor kursi       |
| status       | enum('booked','cancelled') | NO   |     | 'booked'       | Status tiket      |

**Indexes:**

- PRIMARY KEY (`id`)
- FOREIGN KEY (`schedule_id`) REFERENCES `schedules(id)`
- FOREIGN KEY (`passenger_id`) REFERENCES `passengers(id)`

---

### 7. Table: `transactions`

**Deskripsi**: Menyimpan data transaksi pembayaran

| Field          | Type                            | Null | Key | Extra          | Description       |
| -------------- | ------------------------------- | ---- | --- | -------------- | ----------------- |
| id             | int(11)                         | NO   | PRI | auto_increment | ID unik transaksi |
| ticket_id      | int(11)                         | NO   | MUL |                | ID tiket (FK)     |
| payment_method | varchar(50)                     | NO   |     |                | Metode pembayaran |
| payment_status | enum('pending','paid','failed') | NO   |     | 'pending'      | Status pembayaran |
| payment_date   | timestamp                       | YES  |     | NULL           | Waktu pembayaran  |

**Indexes:**

- PRIMARY KEY (`id`)
- FOREIGN KEY (`ticket_id`) REFERENCES `tickets(id)`

---

### 8. Table: `activity_logs`

**Deskripsi**: Menyimpan log aktivitas sistem

| Field       | Type         | Null | Key | Extra             | Description         |
| ----------- | ------------ | ---- | --- | ----------------- | ------------------- |
| id          | int(11)      | NO   | PRI | auto_increment    | ID unik log         |
| user_id     | int(11)      | YES  | MUL |                   | ID pengguna (FK)    |
| action      | varchar(100) | NO   |     |                   | Jenis aksi          |
| description | text         | YES  |     |                   | Deskripsi aktivitas |
| timestamp   | timestamp    | NO   |     | CURRENT_TIMESTAMP | Waktu aktivitas     |

**Indexes:**

- PRIMARY KEY (`id`)
- FOREIGN KEY (`user_id`) REFERENCES `users(id)`

---

## 🔗 Entity Relationship Diagram (ERD)

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│    users    │       │   routes     │       │    buses    │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id (PK)     │       │ id (PK)      │       │ id (PK)     │
│ username    │       │ origin       │       │ plate_number│
│ password    │       │ destination  │       │ brand       │
│ role        │       │ distance_km  │       │ seat_count  │
│ created_at  │       │ estimated_time│       │ status      │
└─────────────┘       └──────────────┘       └─────────────┘
       │                      │                      │
       │                      └──────┐       ┌───────┘
       │                             │       │
       │               ┌─────────────▼───────▼─────────────┐
       │               │           schedules              │
       │               ├─────────────────────────────────┤
       │               │ id (PK)                         │
       │               │ route_id (FK → routes.id)       │
       │               │ bus_id (FK → buses.id)          │
       │               │ departure_time                  │
       │               │ arrival_time                    │
       │               │ price                           │
       │               └─────────────────────────────────┘
       │                              │
       │                              │
       │               ┌──────────────▼──────────────┐
       │               │         passengers          │
       │               ├─────────────────────────────┤
       │               │ id (PK)                     │
       │               │ name                        │
       │               │ email                       │
       │               │ phone                       │
       │               └─────────────────────────────┘
       │                              │
       │                              │
       │               ┌──────────────▼──────────────┐
       │               │           tickets           │
       │               ├─────────────────────────────┤
       │               │ id (PK)                     │
       │               │ schedule_id (FK)            │
       │               │ passenger_id (FK)           │
       │               │ seat_number                 │
       │               │ status                      │
       │               └─────────────────────────────┘
       │                              │
       │                              │
       │               ┌──────────────▼──────────────┐
       │               │        transactions         │
       │               ├─────────────────────────────┤
       │               │ id (PK)                     │
       │               │ ticket_id (FK)              │
       │               │ payment_method              │
       │               │ payment_status              │
       │               │ payment_date                │
       │               └─────────────────────────────┘
       │
       │
       └───────────────┐
                       │
       ┌───────────────▼───────────────┐
       │         activity_logs         │
       ├───────────────────────────────┤
       │ id (PK)                       │
       │ user_id (FK → users.id)       │
       │ action                        │
       │ description                   │
       │ timestamp                     │
       └───────────────────────────────┘
```

## 🗂️ Relasi Antar Tabel

### Primary Relations:

1. **schedules** ↔ **routes** (Many-to-One)
2. **schedules** ↔ **buses** (Many-to-One)
3. **tickets** ↔ **schedules** (Many-to-One)
4. **tickets** ↔ **passengers** (Many-to-One)
5. **transactions** ↔ **tickets** (One-to-One)
6. **activity_logs** ↔ **users** (Many-to-One)

### Constraint Rules:

- **ON DELETE CASCADE**: Jika schedule dihapus, tiket terkait ikut terhapus
- **ON UPDATE CASCADE**: Update ID otomatis terpropagasi
- **FOREIGN KEY CONSTRAINTS**: Menjaga integritas referensial

## 📊 Sample Data

### Default Users:

```sql
INSERT INTO users (username, password, role) VALUES
('admin', MD5('admin123'), 'admin'),
('operator', MD5('operator123'), 'operator'),
('user', MD5('user123'), 'user');
```

### Sample Routes:

```sql
INSERT INTO routes (origin, destination, distance_km, estimated_time) VALUES
('Samarinda', 'Balikpapan', 120, '2 jam'),
('Samarinda', 'Tenggarong', 40, '1 jam'),
('Balikpapan', 'Sangatta', 150, '2.5 jam');
```

## 🔧 Maintenance

### Backup Database:

```bash
mysqldump -u root -p bus_management > backup_$(date +%Y%m%d).sql
```

### Restore Database:

```bash
mysql -u root -p bus_management < backup_file.sql
```

### Optimize Tables:

```sql
OPTIMIZE TABLE users, routes, buses, schedules, passengers, tickets, transactions, activity_logs;
```

---
