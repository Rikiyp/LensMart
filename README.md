# LensMart

Marketplace fotografi multi-vendor untuk kamera, lensa, dan aksesori. Dibangun dengan Laravel 12, Blade, Tailwind CSS, dan Alpine.js.

LensMart menghubungkan pelanggan dengan partner toko terverifikasi. Setiap pesanan dipecah per toko, pembayaran digabung dalam satu transaksi, dan partner dapat memproses pesanan serta mencairkan dana melalui dashboard masing-masing.

## Fitur Utama

### Publik
- Beranda dengan produk terbaru, kategori, dan pilihan rating tertinggi.
- Katalog dengan pencarian, filter kategori/merek/kondisi/harga/rating, dan pengurutan.
- Detail produk: galeri, spesifikasi, kompatibilitas, ulasan, dan produk terkait.
- Perbandingan hingga 3 produk.
- Pendaftaran partner dan autentikasi pelanggan.
- Dialog konfirmasi sebelum menghapus atau menonaktifkan data (produk, akun, alamat, ulasan, pesanan, dan pencairan).

### Pelanggan
- Dashboard ringkasan pesanan dan wishlist.
- Keranjang belanja multi-toko dengan checklist produk yang akan di-checkout.
- Checkout dengan pemilihan alamat dan metode pengiriman untuk produk yang dipilih saja.
- Simulasi pembayaran (transfer bank, e-wallet, QRIS, COD) dengan referensi gateway.
- Pelacakan status pesanan, konfirmasi penerimaan, dan pembatalan.
- Ulasan produk dengan foto, wishlist, alamat, notifikasi, dan profil.
- Hati wishlist pada kartu produk tetap merah untuk produk yang sudah disimpan.

### Partner
- Dashboard penjualan, pesanan baru, stok menipis, dan grafik 6 bulan.
- Pengelolaan profil toko dan rekening bank.
- CRUD produk, foto, spesifikasi, kompatibilitas, dan pengajuan verifikasi.
- Pemrosesan pesanan bertahap: konfirmasi → dikemas → dikirim → selesai.
- Laporan keuangan, komisi platform, dan status pencairan.
- Balasan ulasan serta pelaporan ulasan bermasalah.

### Admin
- Dashboard statistik marketplace dan antrean moderasi.
- Verifikasi toko partner dan produk.
- Pengelolaan pengguna, pesanan, kategori, merek, dan atribut spesifikasi.
- Moderasi ulasan dan tanggapan laporan.
- Laporan penjualan/komisi dengan ekspor PDF (DomPDF) dan Excel (Laravel Excel).
- Persetujuan pencairan dana partner.

### Aturan Bisnis
- Satu checkout menghasilkan satu `Payment` dan satu `Order` per toko.
- Komisi platform default 5% dari subtotal produk (dapat diubah di `config/lensmart.php`).
- Ongkir dihitung per toko berdasarkan metode pengiriman.
- Stok dikurangi saat checkout dan dikembalikan saat pesanan dibatalkan.
- Pesanan hanya dapat berpindah satu status ke depan; status final tidak dapat diubah.
- Pencairan dana dibuat otomatis ketika pelanggan mengonfirmasi penerimaan.
- Produk hanya tampil publik jika sudah diverifikasi admin, aktif, dan tokonya terverifikasi.

## Teknologi

- PHP 8.2+ / Laravel 12
- Blade + Tailwind CSS 3 + Alpine.js 3
- Vite 6
- SQLite (default) atau MySQL
- Laravel Breeze (session auth)
- DomPDF untuk ekspor PDF
- Laravel Excel untuk ekspor XLSX
- GD untuk pengolahan gambar demo

## Tampilan

Antarmuka memakai arah visual editorial: palet kertas hangat (`paper`), aksen amber, tipografi Fraunces untuk judul dan Plus Jakarta Sans untuk teks, garis tipis, serta sudut yang tegas. Komponen dasar (tombol, kartu, input, badge, tabel) berada di `resources/css/app.css`; token warna dan font di `tailwind.config.js`.

- Logo dan favicon original di `resources/views/components/brand-logo.blade.php` dan `public/images/lensmart-mark.svg`.
- Ikon kategori duotone original di `resources/views/components/category-icon.blade.php`.
- Rail merek di beranda berupa marquee wordmark resmi bernada monokrom (`public/images/brands/`) yang tertaut ke filter katalog. Animasi berhenti saat hover/fokus dan nonaktif pada `prefers-reduced-motion`.
- Halaman publik, akun pelanggan, portal partner, dan portal admin memakai bahasa visual yang sama.
- Layout responsif diuji dari lebar 360–390 px.

## Aset Visual & Atribusi

Foto produk demo memakai foto asli sebagai gambar representatif, bukan ilustrasi generated. Sebagian berasal dari Wikimedia Commons dengan lisensi CC0/CC BY/CC BY-SA, sebagian dari galeri produk publik Kenmore Camera sebagai placeholder demo.

- Atribusi lengkap: [`docs/IMAGE-CREDITS.md`](docs/IMAGE-CREDITS.md).
- Logo LensMart, ikon kategori, dan seluruh elemen antarmuka dibuat khusus untuk proyek ini.
- Wordmark merek pada rail beranda berasal dari Wikimedia Commons/Simple Icons; merek dagang tetap milik pemiliknya.
- Foto retailer hanya untuk demo portofolio. Untuk produksi, ganti dengan aset resmi dari pemilik merek atau fotografer berlisensi.

## Instalasi

```bash
composer install
npm install
cp .env.example .env       # Windows: copy .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`. Default proyek menggunakan SQLite:

```env
DB_CONNECTION=sqlite
# DB_DATABASE=...
```

Jika file database belum ada:

```bash
type nul > database/database.sqlite   # Windows
# touch database/database.sqlite      # macOS/Linux
```

Lalu siapkan aplikasi:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

Untuk pengembangan dengan hot reload:

```bash
npm run dev
php artisan serve
```

## Akun Demo

Semua akun menggunakan password: `password`

| Peran | Email |
| --- | --- |
| Admin | `admin@lensmart.test` |
| Partner 1 — Kamera Nusantara | `partner1@lensmart.test` |
| Partner 2 — Lensa Pro Studio | `partner2@lensmart.test` |
| Partner 3 — GearUp Fotografi | `partner3@lensmart.test` |
| Pelanggan | `customer@lensmart.test` |

Seeder juga membuat:
- 33 produk (30 terverifikasi, 2 menunggu verifikasi, 1 ditolak).
- 11 pesanan pada seluruh status.
- Pembayaran lunas, belum dibayar, dan kedaluwarsa.
- Pencairan dana, ulasan, laporan ulasan, wishlist, keranjang, dan notifikasi.
- Toko pending dan rejected untuk simulasi moderasi admin.

Pelanggan demo memiliki pembayaran belum lunas (`PAY-DEMO-0006`) sehingga alur pemilihan metode dan simulasi pembayaran dapat dicoba langsung.

## Alur Demo Singkat

1. Masuk sebagai `customer@lensmart.test`.
2. Tambahkan produk dari toko berbeda ke keranjang.
3. Checkout, pilih alamat dan metode pengiriman.
4. Pada halaman pembayaran, pilih metode lalu klik **Simulasikan Pembayaran Berhasil**.
5. Masuk sebagai partner terkait untuk memproses pesanan hingga dikirim.
6. Kembali sebagai pelanggan untuk mengonfirmasi penerimaan dan memberi ulasan.
7. Masuk sebagai admin untuk melihat laporan, moderasi, dan pencairan dana.

## Pengujian

```bash
php artisan test
```

Test menggunakan SQLite in-memory (lihat `phpunit.xml`) sehingga tidak menghapus database demo.

Cakupan utama:
- Checkout multi-toko, validasi keranjang, dan produk tidak publik.
- Simulasi pembayaran dan notifikasi ke pelanggan/partner.
- Transisi status pesanan, otorisasi lintas toko, pembatalan, dan pemulihan stok.
- Pembuatan pencairan setelah konfirmasi penerimaan.
- Autentikasi, profil, dan smoke test seluruh halaman utama per peran.

## Struktur Penting

```text
app/
  Enums/                  Status pesanan, pembayaran, produk, dll.
  Http/Controllers/       Controller publik, pelanggan, partner, admin
  Http/Middleware/        Role, status toko, dan status pengguna
  Http/Requests/          Validasi form
  Models/                 Model Eloquent
  Notifications/          Notifikasi database
  Policies/               Otorisasi alamat, pesanan, produk, ulasan, toko
  Services/               Checkout, keranjang, pembayaran, status pesanan, laporan
  Services/Payment/       Kontrak gateway dan implementasi simulasi
  Support/helpers.php     Helper format rupiah, tanggal, pengiriman, gambar
database/
  seeders/                Data demo dan generator gambar
resources/views/          Blade untuk publik, pelanggan, partner, admin
routes/web.php            Seluruh rute aplikasi
config/lensmart.php       Ongkir, metode pembayaran, komisi
```

## Catatan

- Pembayaran bersifat simulasi; tidak ada integrasi gateway nyata.
- Foto produk demo disimpan di `storage/app/public/demo` dan diolah ke rasio 4:3 berukuran 1200×900 menggunakan GD. Sumber dan lisensi dicatat di `docs/IMAGE-CREDITS.md`.
- Ekspor PDF memakai font DejaVu Sans agar karakter Rupiah dan teks Indonesia tampil benar.
- Untuk mereset seluruh data demo: `php artisan migrate:fresh --seed`.
