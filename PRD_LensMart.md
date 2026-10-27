# PRD — LensMart (Laravel E-Commerce Kamera & Aksesori Fotografi)

| | |
|---|---|
| Versi | 1.0 (turunan dari SRS LensMart v1.0) |
| Tujuan | Proyek portofolio, dijalankan lokal |
| Stack | Laravel + Blade + Tailwind + MySQL/SQLite |
| Pembaca utama | AI coding agent (opencode) dan developer |

---

## 1. Ringkasan Produk

LensMart adalah website marketplace kamera, lensa, dan aksesori fotografi. Toko kamera (partner) menjual produknya, pelanggan membeli, dan admin mengelola platform. Platform mendapat komisi 5% dari setiap transaksi.

**Tiga aktor:** Admin, Partner (Toko Kamera), Pelanggan.

## 2. Ruang Lingkup

### 2.1 Termasuk (In Scope)
Semua fitur e-commerce pada SRS: akun & role, verifikasi partner, manajemen produk + spesifikasi teknis, katalog + filter + pencarian, komparasi produk (maks 3), keranjang, checkout, pembayaran, manajemen pesanan, ulasan, wishlist, laporan + export PDF/Excel.

### 2.2 Dihapus / Disederhanakan dari SRS

| Item di SRS | Keputusan |
|---|---|
| AI Camera Advisor, AI Recommendation Engine, Python/OpenAI | **DIHAPUS total.** Jangan buat fitur kuesioner/rekomendasi AI. |
| Frontend React/Next.js | Diganti **Blade + Tailwind** (satu aplikasi Laravel, tanpa API terpisah). |
| JWT + OAuth 2.0 | Diganti **session auth bawaan Laravel (Breeze)**. |
| AWS S3 / Cloudinary | Diganti **Laravel local storage** (`storage:link`). |
| VPS / AWS EC2, HTTPS, jadwal maintenance Sabtu | Di luar lingkup. Jalan lokal (`php artisan serve`) atau shared hosting biasa. |
| Payment Gateway Tripay | Default **pembayaran simulasi**. Tripay sandbox hanya opsional di fase terakhir. |
| Biaya langganan partner Rp150.000/bulan | Di luar lingkup (hanya catatan bisnis). |
| Notifikasi real-time | Pakai **notifikasi database Laravel** + polling sederhana. Tanpa websocket. |
| Wishlist dari hasil AI | Dihapus. Wishlist tetap ada, ditambah manual dari halaman produk. |

### 2.3 Di Luar Lingkup
Chat, kupon/voucher, multi-bahasa, integrasi ongkir asli (RajaOngkir), refund otomatis, aplikasi mobile.

---

## 3. Aturan untuk AI Agent (WAJIB DIBACA)

1. Kerjakan **satu fase per sesi**. Jangan lanjut ke fase berikutnya sebelum semua *acceptance criteria* fase ini terpenuhi.
2. Gunakan hanya paket pada Bagian 4. Jangan menambah paket lain tanpa diminta.
3. Ikuti konvensi Laravel standar: Eloquent, Form Request untuk validasi, Policy/middleware untuk otorisasi, Resource Controller bila cocok, migration + seeder + factory.
4. Gunakan **bahasa Indonesia** untuk semua teks antarmuka. Nama tabel, kolom, class, dan route memakai bahasa Inggris.
5. Semua halaman harus **responsif** (mobile & desktop) dengan Tailwind.
6. Jangan menaruh logika bisnis di Blade/Controller yang gemuk. Taruh di **Service class** (`app/Services`) seperti `CheckoutService`, `OrderStatusService`.
7. Setiap fase harus bisa dijalankan dengan `php artisan migrate:fresh --seed` tanpa error.
8. Setelah selesai satu fase, tulis ringkasan singkat file apa yang dibuat/diubah, dan cara mengetesnya.
9. Jika ada keraguan, pilih solusi paling sederhana. Jangan membuat arsitektur berlebihan.

---

## 4. Tech Stack & Konvensi

| Layer | Pilihan |
|---|---|
| Framework | Laravel 11 atau lebih baru, PHP 8.2+ |
| Auth | Laravel Breeze (stack Blade) |
| UI | Blade + Tailwind CSS + Alpine.js (bawaan Breeze) |
| Database | MySQL (XAMPP/Laragon) atau SQLite untuk paling mudah |
| Upload file | `Storage::disk('public')`, jalankan `php artisan storage:link` |
| Export PDF | `barryvdh/laravel-dompdf` |
| Export Excel | `maatwebsite/excel` |
| Testing | PHPUnit/Pest Feature test untuk alur kritis (checkout, status order) |

**Struktur route:**
- `/` dan `/produk/*` → publik
- `/admin/*` → role `admin`
- `/partner/*` → role `partner`
- `/akun/*`, `/keranjang`, `/checkout`, `/pesanan/*` → role `customer`

**Otorisasi:** kolom `users.role` (`admin`, `partner`, `customer`) + middleware `role:admin` dan sejenisnya. Akun dengan `is_active = false` tidak bisa login.

---

## 5. Aturan Bisnis

### 5.1 Akun & Partner
- Pelanggan registrasi sendiri dan langsung aktif.
- Partner registrasi lewat form terpisah: membuat akun `partner` + data toko dengan status `pending`.
- Partner `pending` boleh login, melengkapi profil toko, dan upload dokumen legalitas, tetapi **tidak boleh menambah produk**.
- Admin memverifikasi toko menjadi `verified` atau `rejected` (dengan catatan penolakan).
- Admin bisa menonaktifkan/mengaktifkan akun pelanggan dan partner. Toko partner nonaktif: produknya tidak tampil di publik.
- Akun admin dibuat lewat seeder.

### 5.2 Produk
- Produk dibuat partner dengan `verification_status = pending`. **Produk hanya tampil di publik jika** `verification_status = approved` **dan** `status = active` **dan** toko `verified` dan aktif.
- Jika partner mengubah data penting (nama, harga, spesifikasi) produk yang sudah approved, status **tidak** kembali pending (disederhanakan). Pengecualian: jika admin menolak, partner harus edit lalu produk kembali `pending`.
- Admin dapat menghapus produk yang melanggar kebijakan (soft delete).
- `condition`: `new` (Baru), `like_new` (Bekas Mulus), `good` (Bekas Normal).
- `status` produk: `active`, `inactive`, `out_of_stock`. Jika stok = 0, status otomatis `out_of_stock`.
- Spesifikasi teknis memakai atribut dinamis yang dikelola admin (mis. Sensor, Resolusi, FPS, Mount).
- Kompatibilitas aksesori: relasi ke brand + nama model.

### 5.3 Pesanan
- Alur status: `waiting_confirmation` (Menunggu Konfirmasi) → `confirmed` (Dikonfirmasi) → `packed` (Dikemas) → `shipped` (Dikirim) → `completed` (Selesai). Status lain: `cancelled` (Dibatalkan).
- Status hanya boleh maju satu langkah berurutan. Yang mengubah: partner pemilik pesanan. Pelanggan yang menekan "Pesanan Diterima" pada status `shipped` → `completed`.
- **Pembatalan:** pelanggan hanya bisa membatalkan saat status `waiting_confirmation`. Partner juga bisa menolak/membatalkan pesanan di status itu.
- Keranjang berisi produk dari banyak toko → saat checkout dibuat **satu pesanan per toko**, digabung dalam **satu pembayaran**.
- **Stok** berkurang saat checkout dibuat. Jika pesanan dibatalkan atau pembayaran kedaluwarsa, stok dikembalikan.
- Pembayaran kedaluwarsa setelah 24 jam (cukup dicek lewat scheduled command atau saat halaman dibuka).
- Partner baru melihat pesanan **setelah pembayaran lunas**. Pesanan belum dibayar tidak tampil di partner.
- Setiap perubahan status dicatat di riwayat status.

### 5.4 Keuangan
- Komisi platform = **5% dari subtotal produk** per pesanan (disimpan di `orders.commission_amount`).
- Saat pesanan `completed`, otomatis dibuat catatan pencairan `payouts` untuk partner: `amount = subtotal − commission_amount`, status `pending`. Admin dapat menandainya `paid`.
- Ongkir tidak dikenai komisi.
- Ongkos kirim: metode pengiriman statis dari config (mis. JNE Reguler Rp20.000, J&T Rp18.000, SiCepat Rp17.000, Instant Rp40.000). Tanpa integrasi API ongkir.

### 5.5 Ulasan
- Hanya pelanggan yang pesanannya `completed` yang boleh mengulas produk tersebut, **satu ulasan per produk per pesanan**.
- Ulasan: rating 1–5, komentar, hingga 3 foto. Pelanggan boleh mengedit ulasannya.
- Partner dapat membalas ulasan (satu balasan) dan melaporkan ulasan ke admin.
- Admin dapat menyembunyikan/menghapus ulasan dan menanggapi laporan.

### 5.6 Komparasi
- Pelanggan memilih 2–3 produk (disimpan di session). Maksimal 3.
- Tabel side-by-side: gambar, nama, harga, kondisi, toko, dan semua atribut spesifikasi (gabungan semua atribut; yang kosong ditampilkan "-").
- Perbandingan harga antar toko: pada halaman produk, tampilkan daftar produk lain dengan `brand_id` dan `model_name` yang sama (tidak peka huruf besar/kecil) dari toko lain.

### 5.7 Wishlist
- Pelanggan menambah/menghapus produk dari wishlist.
- Saat partner menurunkan harga produk, kirim notifikasi database ke semua pelanggan yang memiliki produk itu di wishlist.

---

## 6. Model Data

Semua tabel memiliki `id`, `created_at`, `updated_at` kecuali dinyatakan lain.

**users**: name, email (unique), password, role (enum: admin|partner|customer), phone, avatar, is_active (bool, default true)

**stores**: user_id (FK, unique), name, slug (unique), description, address, city, phone, logo, legal_document, status (enum: pending|verified|rejected), rejection_note, bank_name, account_number, account_holder

**addresses**: user_id, label, recipient_name, phone, address_line, city, province, postal_code, is_default

**categories**: name, slug

**brands**: name, slug

**spec_attributes**: name, unit (nullable), category_id (nullable, kosong = berlaku semua)

**products** (SoftDeletes): store_id, category_id, brand_id, name, slug (unique), model_name, description, price (unsigned bigint), stock, condition (enum: new|like_new|good), status (enum: active|inactive|out_of_stock), verification_status (enum: pending|approved|rejected), rejection_note

**product_images**: product_id, path, is_primary, sort_order

**product_specs**: product_id, spec_attribute_id, value

**product_compatibilities**: product_id, brand_id, model_name

**cart_items**: user_id, product_id, quantity (unique user_id+product_id)

**wishlists**: user_id, product_id, price_when_added (unique user_id+product_id)

**payments**: code (unique), customer_id, amount, method (nullable), status (enum: unpaid|paid|expired|failed), gateway (string, default `simulated`), gateway_reference (nullable), paid_at, expires_at

**orders**: order_number (unique), payment_id, customer_id, store_id, status (enum sesuai 5.3), recipient_name, recipient_phone, shipping_address (text, snapshot), shipping_method, shipping_cost, subtotal, commission_amount, total, tracking_number (nullable), note (nullable)

**order_items**: order_id, product_id, product_name (snapshot), price (snapshot), quantity, subtotal

**order_status_histories**: order_id, status, note, changed_by (user_id)

**reviews**: product_id, customer_id, order_id, rating, comment, store_reply (nullable), replied_at (nullable), is_hidden (bool)

**review_images**: review_id, path

**review_reports**: review_id, store_id, reason, status (enum: open|resolved), admin_response (nullable)

**payouts**: store_id, order_id, amount, status (enum: pending|paid), paid_at

Tambahan: tabel `notifications` bawaan Laravel (`php artisan notifications:table`).

---

## 7. Halaman & Fitur per Aktor

### 7.1 Publik (tanpa login)
- Beranda: banner, kategori, produk terbaru
- Katalog `/produk`: grid produk, pencarian keyword, filter (merek, kategori, kondisi, rentang harga, rating minimal), urutan (terbaru, harga, rating), pagination
- Detail produk: galeri foto, harga, stok, kondisi, toko, spesifikasi lengkap, kompatibilitas, ulasan, harga di toko lain, tombol *Keranjang*, *Wishlist*, *Bandingkan*
- Halaman komparasi `/bandingkan`
- Login, registrasi pelanggan, registrasi partner

### 7.2 Pelanggan
- Profil (nama, telepon, foto), kelola alamat (CRUD + alamat utama)
- Keranjang (tambah, ubah qty, hapus; qty tidak boleh melebihi stok)
- Checkout: pilih alamat, metode pengiriman, ringkasan per toko, ongkir, total
- Halaman pembayaran: pilih metode (Transfer Bank / E-Wallet / QRIS), status pembayaran (polling tiap 5 detik)
- Daftar pesanan + detail + riwayat status, batalkan (jika boleh), konfirmasi diterima
- Ulasan: beri, edit
- Wishlist
- Notifikasi (ikon lonceng)

### 7.3 Partner
- Dashboard: ringkasan pesanan baru, total penjualan, produk
- Profil toko, rekening, upload dokumen legalitas, status verifikasi
- Produk: CRUD, multi foto, atur status, spesifikasi teknis, kompatibilitas
- Pesanan: daftar (filter status), detail, ubah status, input nomor resi saat `shipped`, riwayat status
- Keuangan: laporan penjualan per periode, riwayat pencairan, ubah rekening
- Ulasan: lihat, balas, laporkan ke admin

### 7.4 Admin
- Dashboard: total pelanggan, partner, produk, pesanan, pendapatan, komisi
- Akun: daftar pelanggan & partner, aktif/nonaktifkan, verifikasi partner (lihat dokumen)
- Partner: daftar & detail profil toko
- Produk: daftar semua produk, verifikasi (approve/reject), hapus
- Pesanan: master pesanan + filter status (baru, dikonfirmasi, dikemas, dikirim, selesai, dibatalkan)
- Laporan: transaksi platform, pendapatan per periode, export PDF & Excel
- Ulasan: daftar, sembunyikan/hapus, tanggapi laporan ulasan
- Master data: kategori, brand, atribut spesifikasi
- Pencairan: daftar payout, tandai `paid`

---

## 8. Pembayaran

Gunakan pola driver supaya mudah diganti:

```
app/Services/Payment/
  PaymentGateway.php        (interface: createPayment, checkStatus)
  SimulatedGateway.php      (default)
  TripayGateway.php         (opsional, fase 10)
```

Config: `.env` → `PAYMENT_DRIVER=simulated`.

**Alur simulasi:**
1. Checkout membuat `payments` (unpaid) + orders.
2. Pelanggan diarahkan ke halaman pembayaran, memilih metode.
3. Halaman menampilkan kode virtual account / QRIS dummy dan tombol **"Simulasikan Pembayaran Berhasil"**.
4. Tombol itu menandai `payments.status = paid`, `paid_at = now()`, dan mengirim notifikasi ke pelanggan dan partner.
5. Halaman pembayaran melakukan polling status; saat `paid`, tampilkan konfirmasi.

**Tripay (opsional):** pakai sandbox, butuh API key + merchant code + URL callback publik (mis. ngrok). Lewati jika tidak perlu.

---

## 9. Rencana Pengembangan (per Fase)

Setiap fase = satu sesi/prompt ke agent.

### Fase 0 — Setup
- Buat project Laravel, pasang Breeze (Blade), Tailwind jalan, koneksi DB, `storage:link`
- Pasang `dompdf` dan `maatwebsite/excel`
- Layout dasar (navbar, footer) dan helper format Rupiah

**Acceptance:** `php artisan serve` menampilkan beranda; migrate sukses.

### Fase 1 — Auth & Role
- Kolom `role`, `is_active`, middleware role, registrasi pelanggan, registrasi partner (membuat `stores` pending), login redirect sesuai role
- Seeder: 1 admin (`admin@lensmart.test` / `password`)
- Dashboard kosong per role

**Acceptance:** tiap role masuk dashboard sendiri; role salah → 403; akun nonaktif ditolak login.

### Fase 2 — Master Data (Admin)
- CRUD kategori, brand, atribut spesifikasi
- Seeder: kategori (Kamera DSLR, Mirrorless, Lensa, Aksesori), brand (Canon, Nikon, Sony, Fujifilm, Panasonic), atribut (Sensor, Resolusi, FPS, Mount, ISO, Video, Berat)

**Acceptance:** admin dapat CRUD ketiganya; data seed muncul.

### Fase 3 — Manajemen Akun & Verifikasi Partner (Admin) + Profil Toko (Partner)
- Admin: daftar pelanggan & partner, aktif/nonaktif, verifikasi/tolak partner, lihat dokumen
- Partner: edit profil toko, upload dokumen legalitas, rekening, lihat status verifikasi

**Acceptance:** partner pending tidak bisa mengakses menu produk; setelah diverifikasi bisa.

### Fase 4 — Produk (Partner) + Verifikasi (Admin)
- Partner: CRUD produk, multi foto (pilih foto utama), spesifikasi dinamis, kompatibilitas, status
- Admin: daftar produk, approve/reject (dengan catatan), hapus
- Seeder demo: ≥ 3 toko verified, ≥ 25 produk approved dengan foto placeholder

**Acceptance:** produk baru berstatus pending dan tidak muncul publik; setelah approve muncul.

### Fase 5 — Katalog Publik & Komparasi
- Beranda, katalog + pencarian + filter + sort + pagination, detail produk
- Harga di toko lain (5.6), komparasi 2–3 produk via session

**Acceptance:** semua filter bekerja bersamaan lewat query string; komparasi menolak produk ke-4.

### Fase 6 — Keranjang, Alamat, Checkout, Pembayaran
- Alamat CRUD, keranjang, `CheckoutService` (transaksi DB: validasi stok, pecah per toko, kurangi stok, hitung komisi, buat payment)
- Halaman pembayaran simulasi + polling

**Acceptance:** keranjang 2 toko → 2 pesanan + 1 pembayaran; stok berkurang; stok tidak bisa minus; tes Feature untuk checkout lulus.

### Fase 7 — Manajemen Pesanan
- `OrderStatusService` dengan validasi alur dan pencatatan riwayat
- Partner: daftar/detail/ubah status + resi. Pelanggan: daftar/detail/batalkan/konfirmasi diterima. Admin: master + filter status
- Pembatalan mengembalikan stok; `completed` membuat `payouts`

**Acceptance:** status tidak bisa melompat; batal hanya saat `waiting_confirmation`; tes Feature lulus.

### Fase 8 — Ulasan, Wishlist, Notifikasi
- Ulasan lengkap (foto, edit, balasan partner, laporan, moderasi admin)
- Wishlist + notifikasi turun harga (observer/service saat harga produk berubah)
- Notifikasi database: pembayaran berhasil, pesanan baru, perubahan status, harga turun

**Acceptance:** hanya pembeli pesanan selesai yang bisa mengulas; harga turun memicu notifikasi.

### Fase 9 — Laporan & Keuangan
- Admin: laporan transaksi & pendapatan per periode (filter tanggal), export PDF & Excel; kelola payout
- Partner: laporan penjualan per periode, riwayat pencairan
- Grafik sederhana di dashboard (boleh Chart.js via CDN)

**Acceptance:** angka laporan cocok dengan data pesanan `completed`; file PDF/Excel terunduh.

### Fase 10 — Polish & (Opsional) Tripay
- Rapikan UI mobile, empty state, pesan error/sukses, validasi form
- Seeder demo lengkap (pelanggan, pesanan dalam berbagai status, ulasan)
- `README.md`: cara instal, akun demo, daftar fitur
- Opsional: `TripayGateway` sandbox

**Acceptance:** `migrate:fresh --seed` menghasilkan demo yang bisa dijelajahi penuh tanpa input manual.

---

## 10. Persyaratan Non-Fungsional (versi sederhana)

- Password di-hash (bawaan Laravel), proteksi CSRF aktif, validasi di server untuk semua input
- Upload dibatasi: gambar jpg/png/webp maks 2 MB; dokumen legalitas pdf/jpg/png maks 5 MB
- Pengguna hanya dapat mengakses datanya sendiri (cek kepemilikan lewat Policy), termasuk partner hanya melihat produk dan pesanan tokonya
- Hindari N+1 query (gunakan `with()`), halaman daftar selalu dipaginasi
- Responsif untuk lebar 360 px ke atas

---

## 11. Akun Demo (Seeder)

| Role | Email | Password |
|---|---|---|
| Admin | admin@lensmart.test | password |
| Partner | partner1@lensmart.test (dan partner2, partner3) | password |
| Pelanggan | customer@lensmart.test | password |

---

## 12. Definition of Done (Seluruh Proyek)

- [ ] Ketiga role dapat menjalankan seluruh fitur pada Bagian 7
- [ ] Alur utama berjalan: registrasi → cari/bandingkan → keranjang → checkout → bayar (simulasi) → partner proses → selesai → ulasan
- [ ] Tidak ada referensi ke fitur AI di kode maupun UI
- [ ] `php artisan migrate:fresh --seed` berhasil
- [ ] Feature test untuk checkout dan alur status pesanan lulus
- [ ] README tersedia

---

## Lampiran A — Template Prompt untuk opencode

Simpan dokumen ini di root repo (mis. `docs/PRD_LensMart.md`) dan buat `AGENTS.md` berisi: *"Baca docs/PRD_LensMart.md. Patuhi Bagian 3 dan 4 selalu."* Lalu per sesi:

```
Baca docs/PRD_LensMart.md. Kerjakan HANYA Fase <N>.
Ikuti aturan Bagian 3 dan 4, aturan bisnis Bagian 5, dan model data Bagian 6.
Setelah selesai, verifikasi semua acceptance criteria fase tersebut,
jalankan `php artisan migrate:fresh --seed`, lalu ringkas file yang dibuat
dan cara mengetesnya. Jangan mengerjakan fase lain.
```

Bila ada error di tengah jalan, minta agent memperbaiki dulu sebelum lanjut fase berikutnya.
