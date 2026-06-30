# SEAPEDIA - Marketplace E-Commerce Platform

SEAPEDIA adalah platform *e-commerce* *multi-role* terintegrasi yang menghubungkan Pembeli (Buyer), Penjual (Seller), dan Pengemudi (Driver) dalam satu ekosistem digital. Proyek ini dikembangkan untuk memenuhi tugas seleksi Software Engineering Academy COMPFEST 18.

## 🚀 Fitur Utama & Roles
Sistem ini mendukung otentikasi *multi-role* (satu akun dapat memiliki beberapa peran non-admin secara bersamaan) dengan batasan akses berbasis sesi aktif:
- **Admin**: Memantau aktivitas *marketplace*, mengelola *Voucher/Promo*, dan memicu simulasi waktu untuk sistem *overdue*.
- **Seller**: Membuat toko, mengelola produk (CRUD), dan memproses pesanan masuk.
- **Buyer**: Mengelola saldo dompet (dummy top-up), keranjang, alamat, dan melakukan *checkout* dengan berbagai metode pengiriman.
- **Driver**: Mencari pekerjaan pengiriman, mengambil pekerjaan (*take job*), dan mengonfirmasi penyelesaian pengiriman.

---

## 🛠️ Persyaratan Sistem (Prerequisites)
Pastikan mesin Anda memiliki perangkat lunak berikut sebelum menjalankan aplikasi:
- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.x & npm
- MySQL

---

## ⚙️ Cara Instalasi & Menjalankan Aplikasi

Aplikasi ini menggunakan Laravel untuk *Backend* (API) dan Vite (Vue/React) untuk *Frontend*.

1. Setup Backend (API)
Buka terminal dan jalankan perintah berikut di folder backend:
```bash
git clone <repository_url> seapedia
cd seapedia/seapedia-backend
composer install
cp .env.example .env
php artisan key:generate

-- (Konfigurasi .env Backend)
Karena Anda menggunakan MySQL, pastikan baris koneksi database di file .env Anda diatur seperti ini:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seapedia
DB_USERNAME=root
DB_PASSWORD=

-- Migrasi & Seeding Database
Aplikasi sudah dilengkapi dengan data awal (seed data) termasuk akun demo yang lengkap.

php artisan migrate:fresh --seed
php artisan serve

Backend API akan berjalan di http://127.0.0.1:8000

2. Setup Frontend (Client)
Buka terminal baru dan jalankan perintah berikut di folder frontend:

cd seapedia/seapedia-frontend
npm install
cp .env.example .env

Konfigurasi .env Frontend:Pastikan URL API mengarah ke backend lokal Anda:

Cuplikan kodeVITE_API_BASE_URL=[http://127.0.0.1:8000/api](http://127.0.0.1:8000/api)

Jalankan Frontend:
npm run dev

Frontend akan berjalan di URL localhost yang disediakan oleh Vite (biasanya http://localhost:5173).

🔐 Akun Demo (Credentials)
Gunakan kredensial berikut untuk menguji end-to-end flow. Semua akun demo menggunakan password yang sama.

Password untuk semua akun: (password)

Username        Role Aktif      Keterangan
admin           Admin           Memiliki akses penuh ke Admin Dashboard.
seller_demo     Seller          Memiliki "Toko Elektronik Jaya" + 3 produk awal.
buyer_demo      Buyer           Memiliki saldo Rp 1.000.000 & alamat rumah default.
driver_demo     Driver          Saldo awal Rp 0, siap mengambil pekerjaan (job).
multi_demo      Seller + Buyer  Saldo Rp 500.000 (Untuk demo role-selection saat login).

Demo Diskon (Voucher/Promo) yang tersedia:
-SEAPEDIA10: Diskon 10% (Maks Rp 50.000)
-HEMAT20K: Diskon flat Rp 20.000

📜 Dokumentasi Aturan Bisnis (Business Rules)
Sistem ini dibangun dengan mematuhi aturan bisnis marketplace berikut:
1. Aturan Single-Store Checkout:Satu keranjang (cart) hanya dapat berisi produk dari satu toko yang sama. Jika Buyer mencoba memasukkan produk dari Store ID yang berbeda ke dalam keranjang, sistem akan menolak aksi tersebut dan meminta pengguna untuk mengosongkan keranjang terlebih dahulu.

2. Aturan Diskon & Perhitungan PPN 12%:
    - Buyer hanya dapat menggunakan salah satu antara Voucher ATAU Promo dalam satu kali transaksi (tidak dapat digabung).
    - Rumus Checkout:
    Subtotal = Total Harga ProdukDiskon = Nilai potongan dari Voucher/PromoPPN (12%) = 12% x (Subtotal - Diskon)Total Akhir = (Subtotal - Diskon) + PPN (12%) + Biaya Pengiriman

3. Aturan Pendapatan Driver (Driver Earning):Saat pesanan selesai (Pesanan Selesai), 100% dari Biaya Pengiriman (Delivery Fee) akan langsung ditambahkan ke dompet (wallet) Driver sebagai pendapatan.

4. SLA Pengiriman & Penanganan Overdue:Batas waktu pengiriman diatur berdasarkan metode:
    - Instant: Maks 1 Hari
    - Next Day: Maks 2 Hari
    - Regular: Maks 5 Hari
    Jika pesanan melewati batas waktu (SLA) dan belum diselesaikan, sistem akan otomatis mengubah status menjadi Dikembalikan, melakukan refund ke wallet Buyer, dan mengembalikan stok produk ke Seller.
    
Cara Simulasi Waktu (Time Travel):
Untuk keperluan evaluasi, Admin dapat menggunakan fitur simulasi hari. Fitur ini akan memajukan tanggal sistem (+1 hari) dan memicu pengecekan status overdue pada seluruh pesanan.
    
🛡️ Catatan Keamanan (Security Notes)
Aplikasi ini menerapkan perlindungan keamanan standar:
    - SQL Injection: Interaksi database menggunakan Eloquent ORM yang menetralisir input berbahaya melalui parameter binding.
    - Cross-Site Scripting (XSS): Semua teks dari pengguna (seperti Review) disanitasi oleh template engine di frontend.
    - Input Validation: Backend menggunakan Laravel Form Requests untuk memvalidasi tipe data dan batasan input.
    - Role-Based Access Control (RBAC): Akses endpoint dikelola secara ketat melalui Middleware dan Gate untuk mencegah manipulasi data lintas akun.