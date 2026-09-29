# DESIGN.md — ResTable (Sistem Reservasi Rumah Makan)

## 1. Identitas & Mood

**Arah:** Warm & Confident — bukan SaaS generik.

Produk ini adalah sistem reservasi rumah makan, bukan dashboard fintech/B2B. Tampilan harus terasa hangat dan personal, mencerminkan pengalaman kuliner — bukan steril dan korporat seperti kebanyakan aplikasi berbasis "template AI generik" (card shadow tipis, gradient biru-ungu, all-sans-serif).

**Kata kunci mood:** hangat, percaya diri, editorial, dewasa. Bukan playful/kartunis, bukan juga kaku/dingin seperti aplikasi perbankan.

**Referensi rasa:** kombinasi antara nuansa kedai kopi specialty modern dan resto casual-dining kelas menengah-atas — serius tapi tidak berjarak.

---

## 2. Palet Warna

Hindari kombinasi biru-ungu gradient (ciri khas tampilan AI generik) dan putih polos steril.

| Peran | Warna | Hex | Catatan |
|---|---|---|---|
| Primary / Aksen & CTA | Terracotta gelap | `#C1502E` | Terinspirasi warna tanah liat/rempah |
| Teks utama | Charcoal | `#1F1B18` | Bukan hitam pekat `#000000` |
| Latar belakang | Krem hangat | `#F5EFE6` | Kesan "kertas menu", bukan layar polos |
| Aksen sukses/konfirmasi | Hijau zaitun gelap | `#4A5240` | Pengganti hijau neon generik |
| Aksen peringatan | Ochre/mustard gelap | `#B8862E` | Untuk status pending/waiting |
| Aksen bahaya/batal | Merah bata | `#8C3B2E` | Bukan merah terang standar |
| Border/pemisah | Charcoal transparan | `#1F1B18` @ 12% opacity | Untuk garis tipis, bukan shadow |

**Prinsip:** warna-warna ini cenderung "muted"/tidak jenuh — hindari saturasi tinggi ala UI kit default.

---

## 3. Tipografi

- **Heading:** Serif berkarakter kuat — gunakan **Fraunces** atau **Libre Caslon Text** (hindari Playfair Display, sudah terlalu umum dipakai output AI). Memberi kesan "menu restoran cetak", personal.
- **Body / UI (form, tabel, label):** Sans-serif netral dengan keterbacaan tinggi — **Inter** boleh dipakai khusus untuk body/UI text, **jangan** dipakai untuk heading.
- **Kombinasi wajib:** serif-heading + sans-body. Hindari pola all-sans-serif seragam yang jadi ciri khas tampilan AI-generik.

**Skala ukuran (contoh, boleh disesuaikan):**
- H1: 2.5rem, Fraunces, bold
- H2: 1.75rem, Fraunces, semibold
- Body: 1rem, Inter, regular
- Label kecil/caption: 0.875rem, Inter, medium

---

## 4. Karakter Layout & Komponen

- **Border-radius:** kecil, 4–6px. Hindari sudut full-rounded besar ala iOS card generik — kesan yang dicari adalah tegas/dewasa, bukan playful.
- **Shadow:** hindari shadow tebal/glow/elevated-card style. Gunakan border tipis (`1px solid`, warna charcoal transparan) sebagai pemisah antar elemen — kesannya lebih "cetak/editorial" daripada "floating card".
- **Foto (menu, meja, dsb):** tampilkan tanpa rounded-corner besar berlebihan. Kesan yang dicari seperti foto di majalah kuliner, bukan thumbnail aplikasi generik.
- **Spacing:** cukup lega (bukan padat ala dashboard admin generik), tapi tidak berlebihan sampai terasa kosong.
- **Ikon:** hindari ikon outline generik dari library default tanpa kurasi — pilih set ikon yang konsisten ketebalan strokenya dengan gaya editorial (bukan flat-generic).

---

## 5. Hal yang Dihindari (Anti-Pattern / "AI Slop")

- Gradient biru-ungu sebagai warna utama
- Card dengan shadow tipis mengambang (elevated card default)
- Font Poppins/Playfair Display sebagai default tanpa alasan
- Border-radius besar seragam di semua elemen (ciri "template SaaS")
- Ikon generik tanpa kurasi gaya
- Palet warna dengan saturasi tinggi/neon
- Layout all-sans-serif tanpa hierarki tipografi yang jelas

---

## 6. Catatan Implementasi

Dokumen ini adalah arahan desain awal, bukan spesifikasi final yang kaku. Saat implementasi UI (baik manual maupun lewat AI agent), gunakan dokumen ini sebagai referensi utama sebelum membuat komponen baru — terutama untuk halaman customer-facing (reservasi, pembayaran) yang paling menentukan kesan pertama produk.
