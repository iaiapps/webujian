# WebExam - Platform Tes dan Tryout Digital

Platform manajemen tes, tryout, dan ujian online untuk guru dan institusi pendidikan. Sistem berbasis kredit dengan integrasi pembayaran Mayar (QRIS, Virtual Account, E-wallet, Credit Card).

## Deskripsi

WebExam adalah aplikasi web yang memungkinkan guru untuk:

- Membuat dan mengelola tes/tryout/ujian online
- Mengelola bank soal dengan kategorisasi
- Mendaftarkan dan mengelola siswa
- Monitoring progress siswa secara real-time
- Melihat hasil tes dengan scoring otomatis
- Export hasil ke Excel

Siswa dapat:

- Mengerjakan tes dengan antarmuka yang user-friendly
- Melihat hasil dan pembahasan soal
- Tracking progress belajar pribadi

## Fitur Utama

### Untuk Guru

- Manajemen bank soal dengan kategori dan sub-kategori
- Pembuatan paket tes dengan sistem kredit
- Manajemen siswa dan kelas
- Monitoring hasil tes real-time
- Export hasil ke Excel
- Analisis performa siswa

### Untuk Siswa

- Dashboard pribadi
- Daftar tes yang tersedia
- Pengerjaan tes dengan timer
- Review hasil dan pembahasan
- Riwayat tes

### Sistem Kredit

- 10 kredit gratis saat registrasi
- Pembelian kredit via Mayar (QRIS, VA, E-wallet, CC)
- Paket kredit: 1, 5 (+1 bonus), 10 (+2 bonus), 25 (+5 bonus), 50 (+10 bonus)
- Harga mulai Rp 5.000 per kredit
- 1 kredit = 1 paket tes untuk unlimited siswa

### Integrasi Pembayaran Mayar

- Pembayaran otomatis via Mayar Payment Gateway
- Support QRIS (Gopay, OVO, DANA, ShopeePay)
- Virtual Account (BCA, BNI, BRI, Mandiri)
- Credit Card (Visa, Mastercard, JCB)
- E-wallet (OVO, DANA, LinkAja)
- Webhook untuk notifikasi real-time
- Polling fallback jika webhook gagal

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3+)
- **Frontend**: Blade, Bootstrap 5, Bootstrap Icons
- **Database**: MySQL 8.0+
- **Payment Gateway**: Mayar API
- **Authentication**: Laravel Auth
- **Export**: Laravel Excel
- **Hosting**: Shared Hosting (cPanel compatible)

## Instalasi

### Persyaratan

- PHP 8.3 atau lebih tinggi
- Composer
- MySQL 8.0+

### Langkah Instalasi

1. Clone repository

```bash
git clone https://github.com/username/webujian.git
cd webujian
```

2. Install dependencies

```bash
composer install
```

3. Copy file environment

```bash
cp .env.example .env
```

4. Generate application key

```bash
php artisan key:generate
```

5. Konfigurasi database di `.env`

```env
DB_DATABASE=examweb
DB_USERNAME=root
DB_PASSWORD=your_password
```

6. Konfigurasi Mayar API di `.env`

```env
# Production
MAYAR_API_KEY=your-production-api-key
MAYAR_BASE_URL=https://api.mayar.id/hl/v1

# Sandbox (untuk testing)
# MAYAR_API_KEY=your-sandbox-api-key
# MAYAR_BASE_URL=https://api.mayar.club/hl/v1
```

7. Jalankan migration dan data awal

```bash
php artisan migrate:fresh --seed
```

8. Jalankan aplikasi

```bash
php artisan serve
```

## Setup Mayar Payment Gateway

### 1. Daftar Akun Mayar

- Production: https://web.mayar.id
- Sandbox (Testing): https://web.mayar.club

### 2. Verifikasi Akun (Wajib)

Login ke dashboard Mayar:

- Menu: Settings / Pengaturan -> Verification / Verifikasi
- Isi data lengkap: KTP, nomor HP, email
- Upload foto KTP dan selfie
- Tunggu approval (biasanya instan untuk sandbox)

Catatan: Tanpa verifikasi, payment page tidak akan bisa diakses.

### 3. Generate API Key

- Dashboard -> Integration -> API Keys
- Klik "Generate API Key"
- Copy API Key (format: mayar_live_xxx atau mayar_test_xxx)
- Paste ke file `.env`

### 4. Setup Webhook (Opsional tapi Direkomendasikan)

- Dashboard -> Integration -> Webhook
- Tambahkan URL: `https://yourdomain.com/webhook/mayar`
- Method: POST
- Webhook akan mengirim notifikasi saat pembayaran berhasil

## Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Controller untuk admin
│   │   ├── Guru/           # Controller untuk guru
│   │   ├── Student/        # Controller untuk siswa
│   │   └── WebhookController.php
│   └── Middleware/
├── Models/
│   ├── CreditPackage.php
│   ├── CreditPurchase.php
│   ├── CreditTransaction.php
│   └── User.php
└── Services/
    └── MayarService.php    # Service untuk integrasi Mayar

database/
├── migrations/
└── seeders/
    └── CreditPackageSeeder.php

resources/
├── views/
│   ├── admin/              # View untuk admin
│   ├── guru/               # View untuk guru
│   ├── student/            # View untuk siswa
│   └── welcome.blade.php   # Landing page
└── css/
    └── landing.css
```

## Penggunaan

### Sebagai Guru

1. Registrasi akun di `/register`
2. Login di `/login`
3. Dashboard akan menampilkan 10 kredit gratis
4. Tambah siswa di menu "Siswa"
5. Buat soal di menu "Soal"
6. Buat paket tes di menu "Paket Tes" (menggunakan 1 kredit)
7. Jika kredit habis, beli di menu "Kredit" -> "Beli Kredit"
8. Pilih paket -> Lanjutkan ke Pembayaran -> Bayar di Mayar
9. Kredit otomatis masuk setelah pembayaran berhasil

### Sebagai Siswa

1. Login dengan kredensial dari guru
2. Lihat daftar tes yang tersedia
3. Kerjakan tes sesuai jadwal
4. Lihat hasil dan pembahasan setelah tes selesai

### Sebagai Admin

1. Login dengan akun admin
2. Monitoring semua user dan aktivitas
3. Verifikasi akun guru
4. Kelola paket kredit
5. Setting global aplikasi

## Troubleshooting

### Error: "rate must be a number"

Pastikan harga dikirim sebagai integer:

```php
'rate' => (int) $package->price
```

### Error: "extraData Validation Error"

Semua value di extraData harus string:

```php
'extraData' => [
    'user_id' => (string) $user->id,
    'credits' => (string) $package->credit_amount,
]
```

### Error: "Unverified Mayar Account"

Lakukan verifikasi data di dashboard Mayar.

### Invoice tidak redirect ke Mayar

Cek log:

```bash
tail -f storage/logs/laravel.log
```

## API Endpoints

### Mayar Webhook

```
POST /webhook/mayar
Content-Type: application/json

Body:
{
  "event": "payment.received",
  "data": {
    "id": "invoice-id",
    "status": "paid",
    "amount": 50000,
    "paymentMethod": "QRIS",
    "extraData": {...}
  }
}
```

### Check Status (AJAX)

```
GET /guru/credits/check-status?purchase_id={id}
Authorization: Bearer {token}
```

## Lisensi

MIT License

## Support

Untuk pertanyaan dan bantuan, silakan hubungi tim developer atau buat issue di repository.

---

Dikembangkan oleh iaiapps with ♥️.
