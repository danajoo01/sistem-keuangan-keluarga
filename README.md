# Sistem Keuangan Keluarga

Sistem Keuangan Keluarga adalah aplikasi berbasis Laravel 12 untuk mengelola pemasukan, pengajuan dana, pengeluaran, approval admin, notifikasi, lampiran bukti, dan konfigurasi email opsional dalam satu alur kerja.

## Fitur Utama

### Autentikasi
- Login
- Logout
- Register
- Verifikasi email
- Update profile
- Update password

### Role dan Hak Akses Dinamis
- Role utama: `admin` dan `user`
- Hak akses menu disimpan pada tabel `menu_list` dan `role_menu_access`
- Admin memiliki full access
- User hanya bisa mengakses menu yang diberikan dari master role akses

### Dashboard
- Menampilkan ringkasan approved-only
- Pemasukan bulan ini
- Pengeluaran bulan ini
- Saldo saat ini
- Riwayat transaksi terbaru

### Master Data
- Manajemen user
- Role akses
- Config mail

### Modul Keuangan
- Data pemasukan oleh admin
- Pengajuan dana oleh user
- Approval pengajuan dana oleh admin
- Data pengeluaran oleh user
- Approval pengeluaran oleh admin

### Notifikasi
- Admin menerima notifikasi saat user membuat pengajuan dana
- Admin menerima notifikasi saat user membuat pengeluaran
- User menerima notifikasi saat admin mengubah status pengajuan atau pengeluaran
- Notifikasi unread tampil di header
- Saat notifikasi dibuka, data otomatis ditandai sebagai read

### Lampiran Bukti
- Bukti pengeluaran mendukung file `png`, `jpg`, `jpeg`, dan `pdf`
- Preview dan download file menggunakan signed route
- Hanya owner data atau admin yang dapat mengakses file

### Email Opsional
- Config mail diatur dari halaman `Master Data > Config Mail`
- Checkbox `Kirim notifikasi email` muncul jika konfigurasi mail sudah lengkap
- Jika config mail belum lengkap, sistem tetap berjalan dengan notifikasi database tanpa email

## Aturan Bisnis

- Pengajuan dana user masuk ke tabel `uang_masuk` dengan `source = pengajuan` dan status awal `pending`
- Input pemasukan langsung oleh admin otomatis berstatus `approved`
- Pengeluaran user masuk ke tabel `uang_keluar` dengan status awal `pending`
- User hanya bisa edit dan hapus data miliknya selama status masih `pending`
- Admin bisa menghapus data pemasukan dan pengeluaran di semua status
- Dashboard hanya menghitung transaksi dengan status `approved`

## Modul dan Komponen Penting

### Controller
- `UangMasukController` untuk pemasukan, pengajuan dana, dan approval pengajuan
- `UangKeluarController` untuk pengeluaran dan approval pengeluaran
- `DashboardController` untuk ringkasan dashboard
- `AttachmentController` untuk preview dan download lampiran
- `UserNotificationController` untuk mark as read dan redirect notifikasi
- `MailSettingController` untuk konfigurasi mail

### Support Class
- `FinanceNotifier` untuk mengirim notifikasi database dan email opsional
- `MailConfiguration` untuk menerapkan konfigurasi mail dari database
- `AttachmentUrl` untuk membuat signed URL attachment

### View Utama
- Shared form pemasukan: `resources/views/keuangan/uang-masuk/index.blade.php`
- Shared form pengeluaran: `resources/views/keuangan/uang-keluar/index.blade.php`
- Dashboard: `resources/views/dashboard/home.blade.php`
- Header notifikasi: `resources/views/layouts/header.blade.php`

## Daftar Route Utama

### Master Data
- `/master-data/users`
- `/master-data/role-access`
- `/master-data/config-mail`

### Keuangan
- `/keuangan/pemasukan`
- `/keuangan/pengajuan-dana`
- `/keuangan/approval-pengajuan`
- `/keuangan/pengeluaran`
- `/keuangan/approval-pengeluaran`

### Notifikasi dan Attachment
- `/notifications/{notification}/visit`
- `/attachments/preview`
- `/attachments/download`

## Kebutuhan Sistem

- PHP 8.2+
- Composer
- MySQL
- Node.js tidak diperlukan untuk menjalankan project ini pada setup yang sekarang

Catatan: asset hasil build sudah tersedia di `public/build`, sehingga setup lokal maupun deployment Docker yang ada saat ini tidak membutuhkan `npm install` atau `vite build`.

## Instalasi Lokal

1. Clone repository.
2. Install dependency Composer.
3. Salin file environment.
4. Generate app key.
5. Atur koneksi database MySQL.
6. Jalankan migration dan seeder.
7. Jalankan aplikasi.

Contoh perintah:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Konfigurasi Environment Database

Contoh `.env` untuk MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=keuangan-l12
DB_USERNAME=root
DB_PASSWORD=
```

## Akun Default dari Seeder

Seeder bawaan akan membuat akun berikut:

- Admin
	- Email: `admin@example.com`
	- Password: `password`
- User
	- Email: `test@example.com`
	- Password: `password`

Seeder juga mengisi:
- menu dinamis dan role akses
- data dummy pemasukan
- data dummy pengeluaran
- file dummy bukti pengeluaran

## DataTables dan UI

- Modul list data menggunakan server-side DataTables
- Create, edit, dan view menggunakan halaman blade gabungan agar alur lebih ringkas
- Header memiliki dropdown notifikasi unread

## Konfigurasi Mail

Config mail tersedia di menu `Master Data > Config Mail`.

Field yang disediakan:
- mailer
- host
- port
- username
- password
- encryption
- from address
- from name

Perilaku sistem:
- jika konfigurasi mail tidak lengkap, email tidak dikirim
- jika konfigurasi lengkap, opsi kirim email akan muncul pada form yang relevan

## Docker

Project ini sudah memiliki Dockerfile dengan karakteristik berikut:

- runtime menggunakan `Nginx + PHP-FPM`
- tidak menginstal `npm`
- tidak menjalankan `vite build`
- menggunakan asset yang sudah tersedia di `public/build`
- default koneksi database diarahkan ke MySQL melalui `host.docker.internal`
- saat container start akan menjalankan:
	- `php artisan key:generate` jika diperlukan
	- `php artisan optimize:clear`
	- `php artisan storage:link`
	- `php artisan migrate --force`
	- `php artisan db:seed --force`

Contoh build dan run:

```bash
docker build -t keuangan-l12 .
docker run -p 8000:80 --name keuangan-app keuangan-l12
```

Contoh override env MySQL:

```bash
docker run -p 8000:80 --name keuangan-app \
	-e DB_HOST=host.docker.internal \
	-e DB_PORT=3306 \
	-e DB_DATABASE=keuangan-l12 \
	-e DB_USERNAME=root \
	-e DB_PASSWORD= \
	keuangan-l12
```

## Testing

Untuk menjalankan test:

```bash
php artisan test
```

Test utama mencakup:
- autentikasi
- profile management
- pengajuan dana
- pengeluaran
- approval admin
- dashboard approved-only
- attachment preview dan download
- notifikasi dan config mail

## Catatan Implementasi

- Attachment tidak menggunakan direct URL `/storage/...`, tetapi memakai route aplikasi yang signed dan tervalidasi
- Hak akses menu disusun dari database sehingga bisa diubah dari master role akses
- Sistem notifikasi menggunakan database notifications Laravel
- Pengiriman email bersifat opsional dan tidak memblokir alur utama aplikasi

## Created By

Created by Danang Fathurrohman
