# BukuHub - Sistem Perpustakaan QR Code

[![Continuous Integration](https://github.com/ikhsan3adi/sistem-perpustakaan-qr-code/actions/workflows/ci.yml/badge.svg)](https://github.com/ikhsan3adi/sistem-perpustakaan-qr-code/actions/workflows/ci.yml)

![Preview](https://github.com/ikhsan3adi/sistem-perpustakaan-qr-code/raw/main/screenshots/home.png)

> [!NOTE]
>
> ## Fitur
>
> - Login, Register & Magic login link (via Email)
> - Dashboard admin
> - QR Code anggota
> - QR Code peminjaman
> - Sistem denda
> - Dan lainnya.
>
> ## Framework dan Library Yang Digunakan
>
> - [CodeIgniter 4](https://codeigniter.com/)
> - [CodeIgniter Shield](https://codeigniter4.github.io/shield/)
> - [Bootstrap 5](https://getbootstrap.com/)
> - [Modernize Free Bootstrap 5 Admin Template](https://adminmart.com/product/modernize-free-bootstrap-5-admin-template/)
> - [Tabler Icons](https://tabler-icons.io/)
> - [Apex Charts](https://apexcharts.com/)
> - [Endroid QR Code Generator](https://github.com/endroid/qr-code)
> - [Mebjas Html5-QRCode Scanner](https://github.com/mebjas/html5-qrcode)

## Cara Penggunaan (Development)

### Persyaratan

- [Composer](https://getcomposer.org/).
- PHP 8.1+ dan MySQL atau [XAMPP](https://www.apachefriends.org/download.html) versi 8.1+ dengan mengaktifkan extension `-intl` dan `-gd`.
- (Opsional) Kamera/webcam untuk menjalankan qr scanner. Bisa juga menggunakan kamera HP dengan bantuan software DroidCam.

### Instalasi Local

- Unduh dan impor kode proyek ini ke dalam direktori proyek anda (htdocs).
- Penting ⚠️. Jika belum memiliki file `.env`, salin/rename file `.env.example` menjadi `.env`
- (Opsional) Konfigurasi file `.env` untuk mengatur parameter seperti koneksi database dan pengaturan lainnya sesuai dengan lingkungan pengembangan Anda.
- Penting ⚠️. Install dependencies yang diperlukan dengan cara menjalankan perintah berikut di terminal:

```shell
composer install
```

- Buat database `db_book_library` di phpMyAdmin / mysql
- Penting ⚠️. Jalankan migrasi database untuk membuat struktur tabel yang diperlukan. Ketikkan perintah berikut di terminal:

```shell
php spark migrate --all
```

- Penting ⚠️. Karena belum memiliki akun admin, untuk mengakses halaman admin, anda memerlukan user/akun dengan level `superadmin`. Jalankan perintah berikut untuk membuat akun `superadmin`:

```shell
php spark db:seed SuperAdminSeeder
```

> [!TIP]
>
> - (Opsional) Isi database dengan data dummy / seeder.
>
> ```shell
> php spark db:seed Seeder # semua seeder
> php spark db:seed BookSeeder # buku
> php spark db:seed MemberSeeder # anggota
> php spark db:seed LoanSeeder # peminjaman, pengembalian & denda
> ```

- Jalankan website

```shell
php spark serve
```

- Buka [http://localhost:8080](http://localhost:8080)
- Login dengan kredensial `superadmin` berikut:

```txt
username : superadmin
email    : superadmin@admin.com
password : superadmin
```

---

## Deploy Production (maktabah.syathiby.id)

### Persiapan Server

1. **Web Server**: Apache/Nginx dengan PHP 8.1+
2. **Database**: MySQL/MariaDB
3. **SSL**: Let's Encrypt atau sertifikat SSL valid untuk `maktabah.syathiby.id`
4. **Document Root**: Arahkan ke folder `public/`

### Konfigurasi Apache (contoh)

```apache
<VirtualHost *:443>
    ServerName maktabah.syathiby.id
    DocumentRoot /path/to/maktabah.syathiby.id/public
    
    <Directory /path/to/maktabah.syathiby.id/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/privkey.pem
    SSLCertificateChainFile /path/to/chain.pem
</VirtualHost>

<VirtualHost *:80>
    ServerName maktabah.syathiby.id
    Redirect permanent / https://maktabah.syathiby.id/
</VirtualHost>
```

### Langkah Deploy

1. **Clone repository** ke server:
   ```bash
   git clone git@github.com:syathiby/maktabah.syathiby.id.git
   cd maktabah.syathiby.id
   ```

2. **Checkout branch dev** (atau main untuk production):
   ```bash
   git checkout dev
   ```

3. **Install dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

4. **Setup .env production**:
   ```bash
   cp .env.example .env
   # Edit .env dengan konfigurasi production:
   # - app.baseURL = 'https://maktabah.syathiby.id/'
   # - database credentials
   # - encryption.key (generate dengan: php spark key:generate --force)
   # - cookie.secure = true
   ```

5. **Generate encryption key**:
   ```bash
   php spark key:generate --force
   ```

6. **Run migrations**:
   ```bash
   php spark migrate --all
   ```

7. **Create superadmin user**:
   ```bash
   php spark db:seed SuperAdminSeeder
   ```

8. **Set permissions** (Linux):
   ```bash
   chmod -R 755 writable/
   chown -R www-data:www-data writable/
   ```

9. **Clear cache**:
   ```bash
   php spark cache:clear
   php spark optimize
   ```

### Git Workflow

Sesuai standar project SyathibyEcosystem:

- **Branch `dev`** → development branch, push changes di sini
- **Branch `main`** → production ready, merge dari dev setelah testing
- **Branch `staging`** (jika ada) → staging environment

```bash
# Development workflow
git checkout dev
# ... edit files ...
git add <files>
git commit -m "feat: description"
git push origin dev
# ⏸ TUNGGU INSTRUKSI USER untuk lanjut staging/prod

# Deploy ke production (setelah approval user)
git checkout main
git merge dev --no-ff -m "release: vX.Y.Z"
git push origin main
git checkout dev  # ← WAJIB switch back ke dev
```

### Struktur Database

Migrasi database terletak di `app/Database/Migrations/`:
- `2023-08-12-000001_CreateRacksTable.php`
- `2023-08-12-000002_CreateCategoriesTable.php`
- `2023-08-12-000003_CreateBooksTable.php`
- `2023-08-12-000004_CreateBookStockTable.php`
- `2023-08-12-000005_CreateMembersTable.php`
- `2023-08-12-000006_CreateLoansTable.php`
- `2023-08-12-000007_CreateFinesTable.php`
- `2024-07-08-045735_CreateFinesPerDayTable.php`

### Upstream Repository

Fork dari: [ikhsan3adi/sistem-perpustakaan-qr-code](https://github.com/ikhsan3adi/sistem-perpustakaan-qr-code)

Remote `upstream` sudah dikonfigurasi:
```bash
git fetch upstream
git merge upstream/main  # untuk sinkronisasi dengan upstream
```

### Contributing

Kami menerima kontribusi dari komunitas terbuka untuk meningkatkan aplikasi ini. Jika Anda menemukan masalah, bug, atau memiliki saran untuk peningkatan, silakan buat issue baru dalam repositori ini atau ajukan pull request.

### Donasi

[![Donate paypal](https://img.shields.io/badge/Donate-PayPal-green.svg?style=for-the-badge)](https://paypal.me/xannxett?country.x=ID&locale.x=en_US)
[![Donate saweria](https://img.shields.io/badge/Donate-Saweria-red?style=for-the-badge&link=https%3A%2F%2Fsaweria.co%2Fxiboxann)](https://saweria.co/xiboxann)

### Lisensi

[![GitHub license](https://img.shields.io/github/license/ikhsan3adi/sistem-perpustakaan-qr-code?style=for-the-badge)](https://github.com/ikhsan3adi/sistem-perpustakaan-qr-code/raw/main/LICENSE)

### Authors

- [@ikhsan3adi](https://www.github.com/ikhsan3adi) - Original author
- Syathiby Team - Fork & deployment untuk maktabah.syathiby.id