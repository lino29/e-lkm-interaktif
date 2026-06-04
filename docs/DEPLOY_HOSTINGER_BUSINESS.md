# Deploy E-LKM Interaktif ke Hostinger Business

Dokumen ini menjelaskan deployment Laravel E-LKM Interaktif ke Hostinger Business atau shared hosting tanpa SSH. Semua dependency Composer dan NPM harus disiapkan di lokal atau GitHub Actions, lalu hasil build diupload ke hosting.

## Kebutuhan Server Minimal

- PHP 8.3 atau lebih tinggi, sesuai `composer.json`.
- MySQL atau MariaDB.
- Domain aktif dan mengarah ke akun Hostinger.
- Akses FTP/FTPS.
- phpMyAdmin untuk import dan patch database.
- Panel file manager untuk mengedit `.env` production dan permission folder.

## Blocker Shared Hosting Tanpa SSH

- Tidak bisa menjalankan `composer install` di server.
- Tidak bisa menjalankan `npm install` atau `npm run build` di server.
- Tidak bisa menjalankan `php artisan migrate`, `storage:link`, `config:cache`, atau command maintenance lain dari server.
- Dependency `vendor/` dan aset `public/build/` wajib sudah ada sebelum upload.
- Database production harus dibuat/import lewat phpMyAdmin.
- Symlink `public/storage` tidak bisa diandalkan, sehingga upload production sebaiknya diarahkan ke disk `public_uploads`.

## Struktur Folder Hosting

Struktur yang direkomendasikan:

```text
domains/domain.com/
|-- elkm-app/
`-- public_html/
```

Source Laravel utama berada di `elkm-app`. Isi folder `public` Laravel berada di `public_html`.

## Edit public_html/index.php

File `public_html/index.php` harus mengarah ke aplikasi Laravel di `../elkm-app`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../elkm-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../elkm-app/vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../elkm-app/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

Script release di repo ini sudah membuat `index.php` dengan path tersebut.

## File dan Folder yang Wajib Diupload

Upload ke `elkm-app`:

- `app`
- `bootstrap`
- `config`
- `database`
- `resources`
- `routes`
- `storage`
- `vendor`
- `artisan`
- `composer.json`
- `composer.lock`

Upload isi folder `public` Laravel ke `public_html`, terutama:

- `index.php` hasil penyesuaian path.
- `build`
- asset publik lain seperti favicon, CSS, JS, dan file publik bawaan.
- folder `uploads` kosong atau existing production yang writable.

## File dan Folder yang Tidak Boleh Diupload

- `.git`
- `node_modules`
- `tests`
- `.env` lokal
- `storage/logs/*.log`
- `build` sementara di root repo
- credential database, FTP, APP_KEY production, atau secret lain

## Environment Production

Gunakan `.env.production.example` sebagai contoh aman. Buat file `.env` langsung di `elkm-app/.env` melalui File Manager Hostinger.

Nilai penting:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public_uploads
```

Jangan commit `.env` production. Jangan menulis APP_KEY, password database, atau credential FTP di repository.

## Disk Upload Tanpa storage:link

Repo ini menambahkan disk `public_uploads` di `config/filesystems.php`:

```php
'public_uploads' => [
    'driver' => 'local',
    'root' => public_path('uploads'),
    'url' => env('APP_URL').'/uploads',
    'visibility' => 'public',
    'throw' => false,
],
```

Gunakan `FILESYSTEM_DISK=public_uploads` untuk upload yang memakai disk default.

Masih ada beberapa titik kode yang hardcode disk `public`, sehingga belum diubah massal agar tidak merusak fitur:

- `app/Http/Controllers/Guru/EditorImageUploadController.php`
- `app/Livewire/Guru/ManageActivities.php`
- `app/Livewire/Guru/ManageLearningUnitOutline.php`
- `app/Livewire/Guru/ManageMaterials.php`
- `app/Livewire/Guru/ManageModules.php`
- `app/Livewire/Guru/ManageProjects.php`
- `app/Livewire/Guru/ModuleDetail.php`
- `app/Livewire/Murid/MyProject.php`
- `app/Services/Learning/ActivityAnswerService.php`
- `resources/views/components/learning/media-renderer.blade.php`

Jika upload production dari titik tersebut harus tampil tanpa `storage:link`, pindahkan bertahap ke disk default atau `public_uploads`, lalu test upload dan render media.

## Deploy Manual ZIP Tanpa SSH

Jalankan di lokal, Git Bash, WSL, Linux, atau macOS:

```bash
bash scripts/prepare-hostinger-release.sh
```

Script akan:

1. Menghapus `build/hostinger-release` lama.
2. Menjalankan `composer install --no-dev --optimize-autoloader`.
3. Menjalankan `npm ci` jika `package-lock.json` ada, jika tidak `npm install`.
4. Menjalankan `npm run build`.
5. Membuat:
   - `build/hostinger-release/elkm-app`
   - `build/hostinger-release/public_html`
6. Menyalin file aplikasi dan isi folder `public`.
7. Membuat `public_html/index.php` yang mengarah ke `../elkm-app`.

Setelah selesai, zip isi folder `build/hostinger-release`, bukan folder parent-nya. Upload zip ke root domain Hostinger yang berisi `public_html`, lalu extract.

Jika memakai Windows tanpa Bash, jalankan script melalui Git Bash atau WSL. Alternatifnya, gunakan GitHub Actions workflow di bawah.

## Deploy Otomatis GitHub Actions + FTPS

Workflow tersedia di:

```text
.github/workflows/deploy-hostinger.yml
```

Trigger:

- push ke branch `main`
- manual `workflow_dispatch`

Workflow akan:

1. Setup PHP 8.3.
2. Install Composer production dependency.
3. Setup Node.js.
4. Install dependency NPM.
5. Build Vite.
6. Menyiapkan struktur `elkm-app` dan `public_html`.
7. Upload ke Hostinger via FTPS.

Workflow tidak mengupload `.env`, `node_modules`, `.git`, atau `tests`. Folder `public_html/uploads` juga dikecualikan agar file upload user di production tidak terhapus.

## GitHub Secrets

Buat repository secrets berikut:

1. `FTP_SERVER`
2. `FTP_USERNAME`
3. `FTP_PASSWORD`
4. `FTP_SERVER_DIR`

Cara umum:

1. Buka GitHub repository.
2. Buka `Settings`.
3. Pilih `Secrets and variables`.
4. Pilih `Actions`.
5. Klik `New repository secret`.
6. Isi nama secret dan value dari Hostinger.

`FTP_SERVER_DIR` harus menunjuk ke folder root domain, yaitu folder yang berisi `public_html`. Contoh bentuk path: `/domains/domain-anda.com/`.

Jangan menulis password asli di repository atau dokumentasi.

## Checklist Database Production

Jangan membuat route seperti `/migrate` atau `/admin/system/migrate`. Endpoint migrasi production berbahaya dan tidak diperlukan.

Alur database tanpa SSH:

1. Jalankan migration di lokal.
2. Pastikan data lokal sudah sesuai untuk production awal.
3. Export database via phpMyAdmin lokal atau `mysqldump`.
4. Import SQL lewat phpMyAdmin Hostinger.
5. Jika update berikutnya menambah kolom atau tabel, buat SQL patch manual dari migration.
6. Backup database production sebelum import patch.
7. Verifikasi tabel `sessions`, `cache`, role, user admin, modul, asesmen, dan data utama tersedia.

## Checklist Setelah Deploy

- `APP_ENV=production`.
- `APP_DEBUG=false`.
- `APP_URL` sesuai domain.
- `APP_KEY` production sudah diisi dan tidak dibagikan.
- Credential database benar.
- `vendor` tersedia di `elkm-app/vendor`.
- `public/build` tersedia di `public_html/build`.
- `public_html/uploads` writable.
- `elkm-app/storage` writable, terutama `logs`, `framework/cache`, `framework/sessions`, dan `framework/views`.
- Login admin berhasil.
- Dashboard admin, guru, dan murid bisa dibuka.
- Upload file ringan berhasil.
- Materi/media tampil di sisi murid.
- Asesmen bisa dikerjakan sampai halaman hasil.
- Export PDF/Excel diuji terbatas.

## Validasi Lokal Sebelum Upload

Jalankan:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan test
```

Jika ingin mengikuti lockfile secara ketat, gunakan `npm ci` sebagai pengganti `npm install`.

## Catatan Risiko Teknis

- Shared hosting tanpa SSH membuat migration, cache warming, dan `storage:link` harus ditangani manual.
- Upload yang masih memakai disk `public` masih bergantung pada `/storage` kecuali kode dipindahkan bertahap ke `public_uploads`.
- `QUEUE_CONNECTION=sync` cocok untuk shared hosting, tetapi job berat akan berjalan di request user.
- `MAIL_MAILER=log` aman untuk awal deploy, tetapi email sungguhan memerlukan SMTP production.
- File upload production harus dijaga saat redeploy; jangan overwrite `public_html/uploads`.
