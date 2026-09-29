# ResTable — Sistem Reservasi Rumah Makan dengan Midtrans

ResTable adalah platform reservasi meja rumah makan berbasis web yang mewajibkan pelanggan membayar DP (down payment) saat melakukan reservasi untuk mengurangi masalah no-show. Pelunasan tagihan mendukung skema **hybrid**: bisa via sistem (Midtrans Snap) atau cash on-site yang dicatat oleh staf, dengan seluruh transaksi tetap tercatat dalam satu sistem pelaporan keuangan terpusat.

Dibangun dengan **Laravel** dan terintegrasi dengan **Midtrans Payment Gateway (Sandbox)**.

---

## Daftar Isi

- [Latar Belakang](#latar-belakang)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Struktur Peran Pengguna](#struktur-peran-pengguna)
- [Alur Sistem](#alur-sistem)
- [ERD / Struktur Database](#erd--struktur-database)
- [Instalasi & Setup](#instalasi--setup)
- [Konfigurasi Midtrans (Sandbox)](#konfigurasi-midtrans-sandbox)
- [Menjalankan Queue & Webhook (Development)](#menjalankan-queue--webhook-development)
- [Akun Default (Seeder)](#akun-default-seeder)
- [Menjalankan Test](#menjalankan-test)
- [Status Pengembangan](#status-pengembangan)
- [Lisensi](#lisensi)

---

## Latar Belakang

Rumah makan sering mengalami kerugian akibat pelanggan yang melakukan reservasi namun tidak datang (*no-show*), sementara meja yang sudah dipesan tidak bisa dipakai pelanggan lain. ResTable menyelesaikan masalah ini dengan mewajibkan DP saat reservasi, sekaligus menyediakan pencatatan keuangan terpusat — baik untuk transaksi digital maupun cash — sehingga laporan pendapatan restoran tetap akurat dan mudah direkonsiliasi.

---

## Fitur Utama

### Pelanggan
- Registrasi & login
- Membuat reservasi (tanggal, jam, jumlah tamu, catatan)
- Auto-matching meja berdasarkan kapasitas & ketersediaan slot waktu
- Pembayaran DP via Midtrans Snap
- Melihat status reservasi & riwayat transaksi secara real-time
- Membayar pelunasan via sistem (jika staf memilih metode ini)

### Staff / Kasir
- Melihat daftar reservasi aktif pada hari berjalan
- Mencatat pelunasan dengan dua metode:
  - **Cash** — dengan konfirmasi ganda sebelum tersimpan
  - **Via Sistem** — generate Snap Token baru untuk pelanggan
- Audit log otomatis untuk setiap perubahan status reservasi

### Admin Restoran
- Dashboard (status reservasi & revenue harian, breakdown per metode pembayaran)
- CRUD data meja (nomor, kapasitas, status aktif)
- Pengaturan restoran (jam operasional, durasi slot, tipe & nilai DP)
- Rekonsiliasi kas harian (cash tercatat sistem vs fisik, dengan catatan wajib jika ada selisih)

### Sistem
- Auto-cancel reservasi jika DP tidak dibayar dalam batas waktu tertentu (default 15 menit) via Laravel Queue
- Webhook Midtrans dengan validasi signature key (`hash_equals`) dan idempotency check
- Locking database (`lockForUpdate`) untuk mencegah race condition saat dua pelanggan memesan meja yang sama secara bersamaan

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 11+ |
| Database | MySQL |
| Payment Gateway | Midtrans Snap (Sandbox) |
| Autentikasi | Laravel Breeze |
| Otorisasi Role & Permission | Spatie Laravel-Permission |
| Queue | Laravel Queue (database driver) |
| Frontend | Blade + Tailwind CSS |
| Testing | PHPUnit |

---

## Struktur Peran Pengguna

| Role | Dibuat Lewat | Akses |
|---|---|---|
| **Customer** | Registrasi publik (`/register`) | Reservasi, pembayaran, riwayat pribadi |
| **Staff** | Dibuat manual oleh admin (Tinker / fitur manajemen staf) | Input pelunasan, lihat reservasi aktif |
| **Admin** | Seeder / dibuat manual | Semua akses staf + kelola restoran, meja, laporan, rekonsiliasi kas |

---

## Alur Sistem

### 1. Reservasi & Pembayaran DP
1. Pelanggan memilih tanggal, jam, dan jumlah tamu.
2. Sistem mencari meja yang tersedia dengan mengunci baris data (`lockForUpdate`) untuk mencegah dua pelanggan mendapat meja yang sama secara bersamaan.
3. Reservasi dibuat dengan status `waiting_payment`, Snap Token untuk DP di-generate.
4. Auto-cancel job dijadwalkan berjalan 15 menit kemudian jika DP belum dibayar.
5. Setelah pembayaran berhasil, webhook Midtrans memvalidasi signature dan mengubah status menjadi `confirmed`.

### 2. Pelunasan Hybrid
1. Staf membuka daftar reservasi aktif pada hari itu.
2. Staf memilih metode pelunasan:
   - **Cash** — wajib centang konfirmasi ganda sebelum tersimpan sebagai `settlement`.
   - **Via Sistem** — Snap Token baru dibuat, pelanggan dapat membayar dari akunnya sendiri.
3. Setelah pelunasan berhasil, status reservasi berubah menjadi `completed`.

### 3. Rekonsiliasi Kas Harian
Admin membandingkan total transaksi cash yang tercatat sistem dengan uang fisik di kasir pada akhir hari, dengan catatan wajib jika terdapat selisih.

---

## ERD / Struktur Database

Tabel utama:

- `users` — akun pengguna dengan role (customer/staff/admin)
- `restaurants` — data restoran, jam operasional, konfigurasi DP
- `tables` — data meja per restoran
- `reservations` — data reservasi pelanggan
- `transactions` — transaksi DP & pelunasan (1 reservasi dapat memiliki banyak transaksi)
- `reservation_logs` — audit log perubahan status reservasi
- `cash_reconciliations` — pencatatan rekonsiliasi kas harian

---

## Instalasi & Setup

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL

### Langkah Instalasi

```bash
git clone https://github.com/USERNAME_KAMU/restable.git
cd restable

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di `.env`, lalu buat database:

```bash
mysql -u root -p -e "CREATE DATABASE restable"
```

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
```

Jalankan server lokal:

```bash
php artisan serve
```

Aplikasi dapat diakses di `http://127.0.0.1:8000`.

---

## Konfigurasi Midtrans (Sandbox)

1. Daftar akun di [dashboard.midtrans.com](https://dashboard.midtrans.com/register), pastikan mode **Sandbox** aktif.
2. Ambil **Client Key** dan **Server Key** dari menu **Settings → Access Keys**.
3. Isi ke dalam `.env`:

```env
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

4. Untuk menerima notifikasi webhook saat development lokal, gunakan [ngrok](https://ngrok.com) untuk membuat tunnel publik:

```bash
ngrok http 8000
```

5. Salin URL yang diberikan ngrok, lalu daftarkan di dashboard Midtrans pada **Settings → Payment → Notification URL**:

```
https://xxxxx.ngrok-free.dev/midtrans/callback
```

> Catatan: URL ngrok gratis berubah setiap kali di-restart, sehingga perlu diperbarui ulang di dashboard Midtrans setiap sesi development baru.

---

## Menjalankan Queue & Webhook (Development)

Auto-cancel reservasi bergantung pada Laravel Queue. Jalankan worker di terminal terpisah selama development:

```bash
php artisan queue:work
```

Pastikan juga `php artisan serve` dan `ngrok http 8000` tetap berjalan agar webhook Midtrans dapat diterima oleh aplikasi.

---

## Akun Default (Seeder)

Setelah menjalankan `php artisan migrate --seed`, akun berikut otomatis tersedia:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@restable.test` | `password` |

Akun staff belum dibuat otomatis oleh seeder. Untuk membuat akun staff, jalankan via Tinker:

```bash
php artisan tinker
```
```php
$staff = App\Models\User::create([
    'name' => 'Staff Kasir',
    'email' => 'staff@restable.test',
    'password' => bcrypt('password'),
]);
$staff->assignRole('staff');
```

---

## Menjalankan Test

```bash
php artisan test
```

---

## Status Pengembangan

Fitur inti (reservasi, pembayaran DP, webhook, pelunasan hybrid, rekonsiliasi kas) sudah berfungsi dan teruji. Beberapa fitur lanjutan yang direncanakan namun belum diimplementasikan:

- Wiring notifikasi email otomatis
- Halaman riwayat/laporan transaksi (range tanggal)
- Manajemen akun staff melalui UI (saat ini via Tinker)
- Gabung meja otomatis untuk rombongan besar
- Multi-tenant penuh (multi-restoran dengan super admin)
- Refund otomatis, QR check-in, rating & review

---

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan portofolio, dibangun di atas framework [Laravel](https://laravel.com) yang bersifat open-source dengan lisensi MIT.
