# Plan.md — Sistem Reservasi Rumah Makan dengan DP (Laravel + Midtrans)

## 1. Ringkasan Project

**Nama Kerja:** ResTable (bisa diganti sesuai selera)

**Deskripsi Singkat:**
Platform reservasi meja rumah makan berbasis web yang mewajibkan pelanggan membayar DP (down payment) saat melakukan reservasi, untuk mengurangi masalah no-show. Pelunasan tagihan dilakukan dengan skema **hybrid**: bisa via sistem (Midtrans Snap) atau cash on-site yang diinput manual oleh staf, dengan tetap tercatat dalam satu sistem pelaporan keuangan terpusat. Sistem mendukung multi-rumah makan (multi-tenant), manajemen meja dinamis, dan integrasi pembayaran via Midtrans Snap (sandbox).

**Masalah yang Diselesaikan:**

- Restoran kehilangan pendapatan akibat pelanggan yang reservasi tapi tidak datang (no-show).
- Tidak ada sistem terpusat untuk memantau reservasi, kapasitas meja, dan riwayat transaksi.
- Proses reservasi manual (telepon/WA) rentan human error dan tidak scalable.
- Pencatatan pembayaran cash yang terpisah dari sistem digital, menyulitkan rekonsiliasi laporan keuangan harian.

**Target Pengguna:**

- **Owner/Admin Restoran** — mengelola meja, melihat reservasi masuk, memantau transaksi, dan rekonsiliasi kas harian.
- **Staff/Kasir** — mencatat pelunasan (cash atau sistem) saat pelanggan check-in/selesai makan, akses terbatas.
- **Pelanggan** — melakukan reservasi meja, membayar DP, menerima konfirmasi.
- **Super Admin (opsional, jika multi-tenant)** — mengelola pendaftaran restoran baru ke platform.

---

## 2. Tech Stack

| Komponen          | Teknologi                                                            |
| ----------------- | -------------------------------------------------------------------- |
| Backend Framework | Laravel 11                                                           |
| Database          | MySQL / PostgreSQL                                                   |
| Payment Gateway   | Midtrans Snap (Sandbox)                                              |
| Queue & Scheduler | Laravel Queue (database/redis driver) + Task Scheduler               |
| Frontend          | Blade + Livewire / Alpine.js (atau Vue jika ingin SPA-like)          |
| Autentikasi       | Laravel Breeze/Fortify + Role Permission (Spatie Laravel-Permission) |
| Notifikasi        | Laravel Notification (Email) + opsional WhatsApp API (Fonnte/Twilio) |
| PDF/QR (opsional) | barryvdh/laravel-dompdf, simplesoftwareio/simple-qrcode              |
| Testing           | PHPUnit / Pest                                                       |
| Deployment        | VPS (misal: Railway, Hostinger VPS, atau DigitalOcean)               |

---

## 3. Fitur MVP (Minimum Viable Product)

### 3.1 Modul Pelanggan (Customer)

- [ ] Registrasi & login pelanggan
- [ ] Melihat daftar restoran (jika multi-tenant) atau langsung ke halaman restoran
- [ ] Melihat ketersediaan meja berdasarkan tanggal, jam, dan jumlah orang
- [ ] Membuat reservasi (pilih tanggal, jam, jumlah tamu, catatan khusus)
- [ ] Pembayaran DP via Midtrans Snap
- [ ] Melihat status reservasi (pending, menunggu pembayaran, terkonfirmasi, dibatalkan, selesai)
- [ ] Riwayat reservasi pribadi
- [ ] Notifikasi email saat reservasi berhasil/dibatalkan

### 3.2 Modul Admin Restoran

- [ ] Login admin (role-based)
- [ ] CRUD data meja (kapasitas, nomor meja, status aktif/nonaktif)
- [ ] Dashboard reservasi harian/mingguan
- [ ] Konfirmasi manual reservasi (opsional, jika tidak full otomatis)
- [ ] Riwayat transaksi & laporan pendapatan — breakdown per metode (DP via Midtrans, Pelunasan via Midtrans, Pelunasan Cash)
- [ ] Pengaturan jam operasional restoran & durasi slot reservasi
- [ ] Pengaturan nominal/persentase DP
- [ ] Manajemen akun Staff/Kasir (buat, nonaktifkan akses)
- [ ] Fitur rekonsiliasi kas harian ("closing kasir") — mencocokkan total cash tercatat sistem vs uang fisik

### 3.3 Modul Staff/Kasir (Role Baru)

- [ ] Login staff dengan akses terbatas (permission granular, bukan akses penuh admin)
- [ ] Melihat daftar reservasi aktif hari itu (check-in)
- [ ] Input nominal pelunasan (nominal aktual pesanan pelanggan)
- [ ] Pilih metode pelunasan: **Via Sistem** (generate Snap Token baru, pelanggan bayar sendiri) atau **Cash** (staf catat manual)
- [ ] Konfirmasi ganda untuk pembayaran cash (mencegah salah input/asal klik)
- [ ] Tidak bisa mengakses laporan keuangan penuh atau ubah data restoran

### 3.4 Modul Sistem (Background Process)

- [ ] Auto-matching meja sesuai kapasitas & jumlah tamu (termasuk gabung meja jika perlu)
- [ ] Auto-cancel reservasi jika DP tidak dibayar dalam batas waktu (misal 15 menit) — via Laravel Queue + Scheduler
- [ ] Webhook handler Midtrans (validasi signature key, update status transaksi) — berlaku untuk transaksi DP maupun pelunasan via sistem
- [ ] Idempotency check pada webhook (mencegah proses ganda jika Midtrans kirim notifikasi berkali-kali)
- [ ] Audit log setiap perubahan status transaksi/reservasi (siapa yang input, kapan, metode apa) — penting khusus untuk transaksi cash karena rawan human error

### 3.6 Fitur Tambahan (Nice-to-Have, jika waktu memungkinkan)

- [ ] Multi-channel pembayaran Midtrans (VA, e-wallet, QRIS) — otomatis didapat dari Snap
- [ ] Refund otomatis jika restoran membatalkan reservasi
- [ ] QR code check-in saat pelanggan datang
- [ ] Rating & review restoran setelah reservasi selesai
- [ ] Multi-tenant penuh (super admin approve pendaftaran restoran baru)

---

## 4. Rancangan Database (ERD Sederhana)

**Tabel Utama:**

- `users` — id, name, email, password, role (customer/staff/admin/superadmin)
- `restaurants` — id, owner_id, name, address, phone, open_time, close_time, slot_duration, dp_type (fixed/percentage), dp_value
- `tables` — id, restaurant_id, table_number, capacity, is_active
- `reservations` — id, user_id, restaurant_id, table_id (nullable jika auto-match), reservation_date, reservation_time, guest_count, status (pending/waiting_payment/confirmed/cancelled/completed), notes
- `transactions` — id, reservation_id, **type** (dp/pelunasan), **payment_method** (midtrans/cash), midtrans_order_id (nullable jika cash), amount, status (pending/settlement/expire/cancel — untuk cash langsung `settlement`), snap_token (nullable), **recorded_by** (nullable, foreign key ke users — diisi jika payment_method = cash), raw_response (json, nullable)
- `reservation_logs` — id, reservation_id, status_from, status_to, changed_by (nullable), changed_at, note
- `cash_reconciliations` — id, restaurant_id, staff_id, date, total_cash_system, total_cash_physical, selisih, note, confirmed_by (admin)

**Relasi Kunci:**

- 1 restaurant → banyak tables
- 1 restaurant → banyak reservations
- 1 reservation → **banyak transactions** (1:banyak — satu untuk DP, satu atau lebih untuk pelunasan)
- 1 transaction → 1 user pencatat (recorded_by, khusus cash)
- 1 user → banyak reservations
- 1 restaurant → banyak cash_reconciliations (per hari)

> **Catatan penting:** Perubahan dari rencana awal — relasi reservation ke transaction sekarang **1:banyak**, bukan 1:1, karena satu reservasi bisa punya transaksi DP dan transaksi pelunasan terpisah (dan pelunasan sendiri bisa via sistem atau cash).

---

## 5. Alur Sistem (Flow) Utama

### 5.1 Alur Reservasi & Pembayaran

1. Pelanggan pilih restoran → pilih tanggal, jam, jumlah tamu
2. Sistem cek ketersediaan meja (query slot yang belum penuh di jam tsb)
3. Sistem buat record `reservations` dengan status `waiting_payment`
4. Sistem hitung nominal DP → generate Snap Token dari Midtrans
5. Pelanggan diarahkan ke Snap popup untuk bayar
6. Midtrans kirim webhook notifikasi ke endpoint Laravel
7. Laravel validasi signature key → update status `transactions` & `reservations`
8. Jika sukses → status jadi `confirmed`, kirim email konfirmasi
9. Jika dalam 15 menit belum bayar → job scheduler ubah status jadi `cancelled` otomatis

### 5.2 Alur Pelunasan Hybrid (Saat Check-in / Selesai Makan)

1. Staf membuka daftar reservasi aktif hari itu di sistem, pilih reservasi yang akan dilunasi
2. Staf input nominal pelunasan (nominal aktual pesanan pelanggan, dikurangi DP yang sudah dibayar)
3. Staf memilih metode pelunasan:
   - **Via Sistem:** sistem generate Snap Token baru (transaksi baru dengan `type = pelunasan`, `payment_method = midtrans`) → pelanggan scan/bayar dari HP sendiri → menunggu webhook seperti alur DP
   - **Cash:** sistem membuat record transaksi baru (`type = pelunasan`, `payment_method = cash`, `status = settlement` langsung, `recorded_by = staff_id`) → **wajib ada konfirmasi ganda** ("Konfirmasi uang cash telah diterima") sebelum status reservasi diubah jadi `completed`
4. Reservasi berubah status menjadi `completed` setelah pelunasan (metode apa pun) berhasil tercatat
5. Setiap aksi (baik dari staf maupun sistem) dicatat di `reservation_logs` untuk audit trail

### 5.3 Alur Rekonsiliasi Kas Harian (Closing Kasir)

1. Di akhir hari/shift, staf atau admin membuka menu "Closing Kasir"
2. Sistem menghitung otomatis `total_cash_system` = total dari semua transaksi `payment_method = cash` yang tercatat hari itu
3. Staf/admin input `total_cash_physical` (uang fisik yang benar-benar ada di kasir)
4. Sistem hitung `selisih` = total_cash_physical - total_cash_system
5. Jika ada selisih, wajib diisi catatan (`note`) penjelasan
6. Admin melakukan konfirmasi akhir (`confirmed_by`) untuk menutup rekonsiliasi hari itu

### 5.4 Alur Webhook Midtrans (Detail Teknis)

1. Endpoint `POST /midtrans/callback` menerima notifikasi
2. Validasi `signature_key` (SHA512 dari order_id + status_code + gross_amount + server_key)
3. Cek idempotency: apakah `midtrans_order_id` sudah diproses sebelumnya?
4. Update status transaksi sesuai `transaction_status` dari Midtrans (settlement, pending, expire, cancel, deny)
5. Cek kolom `type` pada transaksi terkait — jika `dp` maka update status reservasi jadi `confirmed`; jika `pelunasan` maka update status reservasi jadi `completed`
6. Dispatch job tambahan (kirim notifikasi, update status reservasi)

---

## 6. Timeline Pengerjaan (Estimasi)

Lebih Cepat Lebih Baik
| Minggu | Fokus |
|---|---|
| 1 | Setup project, autentikasi, role & permission, desain database |
| 2 | CRUD restoran, meja, dan reservasi (tanpa payment dulu) |
| 3 | Integrasi Midtrans Snap (sandbox) + webhook handler (untuk transaksi DP) |
| 4 | Auto-cancel job, queue, notifikasi email |
| 5 | Modul pelunasan hybrid (role Staff, input cash/sistem, konfirmasi ganda) + rekonsiliasi kas harian |
| 6 | Dashboard admin (laporan breakdown per metode pembayaran, statistik reservasi) |
| 7 | Testing (unit + manual, termasuk skenario cash & split metode), perbaikan bug, polish UI |
| 8 | Deployment ke VPS/hosting, custom domain, dokumentasi |
| 9 | (Opsional) Fitur tambahan: QR check-in, rating, refund |

---

## 7. Hal yang Perlu Diperhatikan Saat Implementasi

- **Race Condition:** gunakan database transaction + locking (`lockForUpdate()`) saat proses assign meja, supaya 2 pelanggan tidak bisa reservasi meja & jam yang sama secara bersamaan.
- **Keamanan Webhook:** jangan pernah percaya data dari request Midtrans tanpa validasi signature key.
- **Testing Sandbox:** gunakan kartu simulasi Midtrans sandbox untuk testing berbagai skenario (sukses, gagal, pending, expire).
- **Environment Variables:** simpan `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` di `.env`, jangan hardcode.
- **Logging:** simpan raw response dari Midtrans di kolom `raw_response` (json) untuk keperluan debugging/audit.
- **Trust Boundary Cash vs Sistem:** transaksi cash tidak punya verifikasi pihak ketiga seperti Midtrans, jadi rawan human error/fraud. Wajib ada konfirmasi ganda + audit log siapa yang menginput (`recorded_by`).
- **Permission Granular untuk Staff:** gunakan Spatie Laravel-Permission dengan permission spesifik (misal `input-pelunasan-cash`, `lihat-reservasi-aktif`) — jangan cuma role sederhana, supaya staf tidak bisa mengakses data sensitif seperti laporan keuangan penuh.
- **Batasi Partial Payment di MVP:** untuk versi awal, satu transaksi pelunasan = satu metode saja (tidak split cash + sistem sekaligus), supaya tidak overbuild. Fitur split bisa ditambahkan di iterasi berikutnya.

---

## 8. Deliverable Akhir untuk Portofolio

- Source code di GitHub (dengan README lengkap: cara install, screenshot, demo flow)
- Live demo (deploy di VPS/hosting dengan domain sendiri)
- Video demo singkat (opsional, 2-3 menit) menunjukkan alur reservasi sampai pembayaran
- Dokumentasi teknis singkat: ERD, flow diagram webhook, dan penjelasan keputusan desain (misal kenapa pakai DP, kenapa pakai queue untuk auto-cancel)

---

## 9. Catatan Pengembangan Lanjutan (Setelah MVP)

Jika ingin dijual ke restoran nyata sebagai klien pertama:

- Tambahkan white-label branding per restoran (logo, warna tema)
- Integrasi WhatsApp notification (lebih familiar untuk owner restoran lokal)
- Model bisnis SaaS: biaya langganan bulanan per restoran, atau fee per transaksi
