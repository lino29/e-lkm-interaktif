# Panduan Pengembangan Sistem E-LKM Interaktif Energi Terbarukan Berbasis Web

## 1. Arah Umum Pengembangan

Sistem yang akan dikembangkan adalah **E-LKM Interaktif Energi Terbarukan Berbasis Web**. Sistem ini dikembangkan untuk membantu murid SMK kelas X mempelajari materi Projek IPAS melalui modul digital, aktivitas interaktif, asesmen otomatis, remedial, forum diskusi, portofolio proyek, dan laporan hasil belajar.

Tech stack utama yang digunakan:

* Backend: Laravel
* Database: MySQL
* Frontend utama: Blade + Livewire + Tailwind CSS
* Frontend tambahan: Vue.js jika dibutuhkan untuk komponen yang sangat interaktif
* Role & permission: Spatie Laravel Permission
* Penilaian uraian: Custom NLP Service berbasis rubrik, keyword matching, dan similarity score
* Penyimpanan file: Laravel Storage
* Export laporan: PDF dan Excel pada tahap lanjutan

Catatan teknis: meskipun dokumen konsep menyebut Blade/Vue, untuk tahap awal lebih rapi jika Livewire + Blade dijadikan fondasi utama. Vue dapat digunakan nanti untuk fitur tertentu seperti drag-and-drop menjodohkan, grafik interaktif, simulasi ringan, atau komponen dashboard yang lebih kompleks.

---

## 2. Struktur Tahapan Pengembangan Berdasarkan Waterfall

Pengembangan mengikuti alur Waterfall:

1. Analisis kebutuhan
2. Desain sistem
3. Implementasi/pengkodean
4. Pengujian sistem
5. Penerapan dan pemeliharaan

Pada posisi sekarang, dokumen analisis dan konsep pemodelan sudah tersedia. Maka tahap berikutnya adalah masuk ke **implementasi awal sistem**.

---

## 3. Target Fitur Utama Sistem

Sistem perlu dikembangkan dalam tiga sisi pengguna.

### A. Admin

Admin bertugas mengelola sistem secara umum. Fitur admin meliputi:

1. Login dan logout
2. Mengelola data pengguna
3. Mengelola data guru
4. Mengelola data murid
5. Mengelola kelas
6. Mengelola mata pelajaran
7. Mengatur role dan permission
8. Melihat aktivitas sistem
9. Mengelola pengaturan aplikasi

### B. Guru

Guru bertugas mengelola pembelajaran digital. Fitur guru meliputi:

1. Login dan logout
2. Mengelola modul pembelajaran
3. Mengelola kegiatan belajar
4. Mengelola materi pembelajaran
5. Mengunggah gambar, video, atau simulasi
6. Mengelola aktivitas Ayo Mengamati
7. Mengelola aktivitas Ayo Bertanya
8. Mengelola aktivitas Ayo Mencoba
9. Mengelola aktivitas Ayo Menalar
10. Mengelola aktivitas Ayo Menyimpulkan
11. Mengelola forum diskusi
12. Mengelola asesmen formatif dan asesmen akhir
13. Mengatur kunci jawaban
14. Mengatur rubrik penilaian
15. Mengatur keyword jawaban uraian
16. Mengatur KKTP
17. Mengatur jumlah percobaan asesmen
18. Melihat progres belajar murid
19. Melihat nilai murid
20. Memberikan umpan balik
21. Menilai proyek murid
22. Mengunduh laporan hasil belajar

### C. Murid

Murid menggunakan sistem untuk mengikuti pembelajaran. Fitur murid meliputi:

1. Login dan logout
2. Melihat dashboard belajar
3. Membuka modul E-LKM
4. Membaca tujuan pembelajaran
5. Membaca materi
6. Mengamati gambar, video, atau simulasi
7. Menjawab pertanyaan pemantik
8. Mengisi lembar kerja digital
9. Menjawab soal penalaran
10. Menulis kesimpulan
11. Mengikuti forum diskusi/refleksi
12. Mengerjakan asesmen formatif
13. Menerima nilai dan umpan balik otomatis
14. Mengulang materi jika belum tuntas
15. Mengikuti remedial
16. Mengunggah hasil proyek
17. Melihat portofolio belajar

---

## 4. Langkah Instalasi Awal Project

### 4.1 Siapkan Tools

Pastikan perangkat sudah memiliki:

* PHP
* Composer
* Node.js dan NPM
* MySQL/MariaDB
* Git
* Laragon/XAMPP/Herd/Laravel Sail

Untuk Windows, Laragon sangat disarankan karena mudah digunakan untuk Laravel dan MySQL.

---

### 4.2 Buat Project Laravel Livewire

Jalankan:

```bash
composer global require laravel/installer
```

Lalu buat project:

```bash
laravel new e-lkm-interaktif
```

Saat muncul pilihan starter kit, pilih:

```text
Starter kit: Livewire
Database: MySQL
Testing framework: Pest
```

Masuk ke folder project:

```bash
cd e-lkm-interaktif
```

Install dependency frontend:

```bash
npm install
```

Jalankan aplikasi:

```bash
composer run dev
```

Jika ingin menjalankan manual, gunakan dua terminal:

```bash
php artisan serve
```

dan:

```bash
npm run dev
```

---

## 5. Konfigurasi Database

Buat database MySQL:

```sql
CREATE DATABASE e_lkm_interaktif;
```

Atur file `.env`:

```env
APP_NAME="E-LKM Interaktif"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_lkm_interaktif
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi awal:

```bash
php artisan migrate
```

---

## 6. Instalasi Role Admin, Guru, dan Murid

Install package permission:

```bash
composer require spatie/laravel-permission
```

Publish konfigurasi:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Bersihkan cache:

```bash
php artisan optimize:clear
```

Jalankan migrasi:

```bash
php artisan migrate
```

Tambahkan trait pada model `User`.

File:

```text
app/Models/User.php
```

Tambahkan:

```php
use Spatie\Permission\Traits\HasRoles;
```

Di dalam class `User`:

```php
use HasRoles;
```

Contoh sederhana:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
```

---

## 7. Seeder Role dan Admin Awal

Buat seeder role:

```bash
php artisan make:seeder RoleSeeder
```

Isi file:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'murid']);
    }
}
```

Buat seeder admin:

```bash
php artisan make:seeder AdminSeeder
```

Isi file:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@elkm.test'],
            [
                'name' => 'Administrator E-LKM',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('admin');
    }
}
```

Panggil di `DatabaseSeeder.php`:

```php
$this->call([
    RoleSeeder::class,
    AdminSeeder::class,
]);
```

Jalankan:

```bash
php artisan db:seed
```

Akun awal:

```text
Email: admin@elkm.test
Password: password
```

---

## 8. Rancangan Struktur Folder Aplikasi

Gunakan struktur berikut agar sistem mudah dikembangkan.

```text
app/
├── Livewire/
│   ├── Admin/
│   ├── Guru/
│   └── Murid/
├── Models/
├── Services/
│   ├── Assessment/
│   ├── Learning/
│   ├── Nlp/
│   └── Report/
├── Policies/
└── Helpers/

resources/
├── views/
│   ├── dashboard/
│   ├── livewire/
│   ├── components/
│   └── layouts/
├── css/
└── js/

database/
├── migrations/
├── seeders/
└── factories/

routes/
├── web.php
└── auth.php
```

---

## 9. Rancangan Komponen Livewire

### Admin

```text
app/Livewire/Admin/
├── Dashboard.php
├── ManageUsers.php
├── ManageTeachers.php
├── ManageStudents.php
├── ManageClasses.php
├── ManageSubjects.php
└── Reports.php
```

### Guru

```text
app/Livewire/Guru/
├── Dashboard.php
├── ManageModules.php
├── ManageLearningUnits.php
├── ManageMaterials.php
├── ManageActivities.php
├── ManageAssessments.php
├── ManageQuestions.php
├── ManageRubrics.php
├── ManageDiscussions.php
├── ManageProjects.php
└── Reports.php
```

### Murid

```text
app/Livewire/Murid/
├── Dashboard.php
├── MyModules.php
├── LearningUnitPage.php
├── ActivityPage.php
├── AssessmentPage.php
├── RemedialPage.php
├── MyProject.php
├── MyScores.php
└── Portfolio.php
```

Contoh membuat komponen:

```bash
php artisan make:livewire Guru/ManageModules
php artisan make:livewire Murid/MyModules
php artisan make:livewire Murid/AssessmentPage
```

---

## 10. Route Dashboard Berdasarkan Role

Contoh route awal:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('guru')) {
            return redirect()->route('guru.dashboard');
        }

        if ($user->hasRole('murid')) {
            return redirect()->route('murid.dashboard');
        }

        abort(403);
    })->name('dashboard');

    Route::view('/admin/dashboard', 'dashboard.admin')
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::view('/guru/dashboard', 'dashboard.guru')
        ->middleware('role:guru')
        ->name('guru.dashboard');

    Route::view('/murid/dashboard', 'dashboard.murid')
        ->middleware('role:murid')
        ->name('murid.dashboard');
});
```

---

## 11. Database Inti yang Perlu Dibuat

Buat model dan migration berikut.

```bash
php artisan make:model ClassRoom -m
php artisan make:model Subject -m
php artisan make:model Module -m
php artisan make:model LearningUnit -m
php artisan make:model Material -m
php artisan make:model Media -m
php artisan make:model Activity -m
php artisan make:model ActivityAnswer -m
php artisan make:model Assessment -m
php artisan make:model Question -m
php artisan make:model QuestionKeyword -m
php artisan make:model Rubric -m
php artisan make:model AssessmentAttempt -m
php artisan make:model StudentAnswer -m
php artisan make:model Progress -m
php artisan make:model Discussion -m
php artisan make:model Project -m
php artisan make:model Glossary -m
php artisan make:model Reference -m
```

Tabel utama:

1. `users`
2. `class_rooms`
3. `subjects`
4. `modules`
5. `learning_units`
6. `materials`
7. `media`
8. `activities`
9. `activity_answers`
10. `assessments`
11. `questions`
12. `question_keywords`
13. `rubrics`
14. `assessment_attempts`
15. `student_answers`
16. `progress`
17. `discussions`
18. `projects`
19. `glossaries`
20. `references`

---

## 12. Relasi Database Utama

Relasi yang disarankan:

```text
User
├── belongsTo ClassRoom sebagai murid
├── hasMany Modules sebagai guru/pembuat modul
├── hasMany ActivityAnswers
├── hasMany AssessmentAttempts
├── hasMany StudentAnswers
├── hasMany Projects
└── hasMany Discussions

ClassRoom
└── hasMany Users

Subject
└── hasMany Modules

Module
├── belongsTo Subject
├── belongsTo User sebagai created_by
├── hasMany LearningUnits
├── hasMany Assessments
├── hasMany Projects
├── hasMany Glossaries
└── hasMany References

LearningUnit
├── belongsTo Module
├── hasMany Materials
├── hasMany Media
├── hasMany Activities
├── hasMany Assessments
└── hasMany Discussions

Activity
├── belongsTo LearningUnit
└── hasMany ActivityAnswers

Assessment
├── belongsTo Module
├── belongsTo LearningUnit
├── hasMany Questions
└── hasMany AssessmentAttempts

Question
├── belongsTo Assessment
├── hasMany QuestionKeywords
├── hasMany Rubrics
└── hasMany StudentAnswers

AssessmentAttempt
├── belongsTo Assessment
├── belongsTo User sebagai student
└── hasMany StudentAnswers
```

---

## 13. Urutan Implementasi yang Disarankan

### Tahap 1 — Fondasi Sistem

Target:

* Laravel Livewire berjalan
* Database MySQL terhubung
* Login/register berjalan
* Role admin, guru, murid tersedia
* Dashboard role berjalan

Checklist:

```text
✓ Install Laravel Livewire Starter Kit
✓ Setup database
✓ Install Spatie Permission
✓ Buat RoleSeeder
✓ Buat AdminSeeder
✓ Buat route dashboard sesuai role
✓ Buat tampilan dashboard admin/guru/murid
```

---

### Tahap 2 — Manajemen Pengguna dan Kelas

Target:

* Admin bisa mengelola guru, murid, dan kelas
* Murid terhubung ke kelas
* Guru dapat mengelola kelas atau modul tertentu

Fitur:

```text
Admin:
- Tambah user
- Edit user
- Hapus user
- Assign role
- Assign murid ke kelas
- Assign guru ke kelas/modul
```

---

### Tahap 3 — Manajemen Modul Pembelajaran

Target:

Guru dapat membuat modul E-LKM.

Struktur modul:

```text
Modul E-LKM
├── Pendahuluan
├── Kegiatan Belajar 1
├── Kegiatan Belajar 2
├── Kegiatan Belajar 3
├── Kegiatan Belajar 4
├── Kegiatan Belajar 5
├── Asesmen Akhir
├── Glosarium
└── Daftar Pustaka
```

Fitur guru:

```text
- Tambah modul
- Edit modul
- Upload cover modul
- Atur status draft/publish
- Atur KKTP modul
- Atur jumlah percobaan asesmen
```

---

### Tahap 4 — Kegiatan Belajar dan Materi

Setiap modul memiliki beberapa kegiatan belajar.

Contoh sesuai bahan ajar:

```text
1. Konsep Energi dan Sumber Energi
2. Masalah Energi Fosil
3. Pengertian dan Jenis Energi Terbarukan
4. Teknologi Energi Terbarukan Berbasis STEM
5. Merancang Aksi Sederhana Energi Terbarukan
```

Fitur:

```text
- Tambah kegiatan belajar
- Atur tujuan pembelajaran
- Tambah materi
- Tambah gambar/video/simulasi
- Atur urutan materi
```

---

### Tahap 5 — Aktivitas Interaktif E-LKM

Setiap kegiatan belajar memiliki aktivitas:

```text
Ayo Mengamati
Ayo Bertanya
Ayo Mencoba
Ayo Menalar
Ayo Menyimpulkan
Forum Diskusi/Refleksi
```

Jenis input yang perlu disiapkan:

```text
- Jawaban teks singkat
- Jawaban uraian
- Tabel isian
- Upload file
- Komentar diskusi
```

Gunakan kolom `answer_text` untuk jawaban biasa dan `answer_json` untuk tabel isian.

Contoh `answer_json` untuk tabel energi:

```json
[
  {
    "alat": "Lampu",
    "energi_masuk": "Energi listrik",
    "energi_keluar": "Energi cahaya dan panas",
    "sumber_energi": "PLN"
  }
]
```

---

### Tahap 6 — Modul Asesmen

Jenis soal yang harus didukung:

1. Pilihan ganda biasa
2. Pilihan ganda kompleks
3. Benar/salah
4. Menjodohkan
5. Isian singkat
6. Uraian singkat

Struktur penyimpanan jawaban:

* Pilihan ganda biasa: simpan string, misalnya `A`
* Pilihan ganda kompleks: simpan array JSON, misalnya `["A","C","D"]`
* Benar/salah: simpan `true` atau `false`
* Menjodohkan: simpan JSON pasangan
* Isian: simpan teks
* Uraian: simpan teks panjang

---

## 14. Logika Penilaian Otomatis

### A. Pilihan Ganda Biasa

```text
Jika jawaban murid sama dengan kunci:
    skor = bobot soal
Jika salah:
    skor = 0
```

### B. Pilihan Ganda Kompleks

Gunakan skor parsial.

```text
Skor = jumlah jawaban benar yang dipilih / jumlah kunci benar × bobot soal
```

Namun jika murid memilih opsi salah, skor dapat dikurangi agar tidak asal memilih semua jawaban.

Contoh:

```text
Kunci: A, C, D
Jawaban murid: A, C
Skor: 2/3 × bobot
```

### C. Benar/Salah

```text
Jika sesuai kunci:
    skor = bobot
Jika tidak:
    skor = 0
```

### D. Menjodohkan

```text
Skor = jumlah pasangan benar / total pasangan × bobot
```

### E. Isian Singkat

Gunakan keyword matching sederhana.

```text
Jika jawaban mengandung kata kunci utama:
    skor penuh atau parsial
Jika tidak:
    skor rendah atau 0
```

### F. Uraian Singkat

Gunakan kombinasi:

```text
Skor akhir = 40% skor rubrik + 30% skor keyword + 30% similarity score
```

Contoh:

```text
Skor Rubrik = 80
Skor Keyword = 70
Similarity Score = 75

Skor Akhir = (0,40 × 80) + (0,30 × 70) + (0,30 × 75)
            = 75,5
```

Hasil dapat dibulatkan menjadi 76.

---

## 15. Rancangan Custom NLP Service

Buat folder:

```text
app/Services/Nlp/
```

Buat service:

```text
TextPreprocessor.php
KeywordMatcher.php
SimilarityService.php
EssayScoringService.php
```

### TextPreprocessor

Fungsi:

```text
- Mengubah teks menjadi lowercase
- Menghapus tanda baca
- Menghapus spasi ganda
- Normalisasi kata sederhana
```

### KeywordMatcher

Fungsi:

```text
- Mengambil keyword dari database
- Memeriksa keyword yang muncul dalam jawaban murid
- Menghitung skor keyword berdasarkan bobot
```

### SimilarityService

Fungsi:

```text
- Membandingkan jawaban murid dengan jawaban acuan
- Menghasilkan similarity score 0–100
```

Untuk tahap awal, similarity dapat memakai pendekatan sederhana seperti:

```text
- similar_text PHP
- cosine similarity sederhana
- Jaccard similarity
```

### EssayScoringService

Fungsi:

```text
- Menggabungkan skor rubrik
- Menggabungkan skor keyword
- Menggabungkan similarity score
- Menghasilkan nilai akhir
- Menghasilkan umpan balik otomatis
```

---

## 16. Modul Remedial

Aturan remedial:

```text
Jika nilai asesmen >= KKTP:
    status = tuntas
    murid boleh lanjut kegiatan belajar berikutnya

Jika nilai asesmen < KKTP:
    status = remedial
    sistem menampilkan materi yang perlu dipelajari ulang
    murid boleh mengulang sesuai batas percobaan
```

Data yang perlu disimpan:

```text
- assessment_id
- student_id
- attempt_number
- total_score
- status
- started_at
- submitted_at
```

Status yang disarankan:

```text
belum_mulai
sedang_dikerjakan
tuntas
remedial
selesai
```

---

## 17. Modul Proyek Murid

Kegiatan Belajar 5 diarahkan menjadi portofolio proyek.

Pilihan proyek:

```text
- Kompor surya mini
- Briket biomassa
- Audit energi kelas
- Kampanye hemat energi
- Lampu taman surya mini
```

Field proyek:

```text
project_title
problem
objective
tools_materials
procedure
collected_data
expected_result
conclusion
file_path
score
feedback
status
```

Status proyek:

```text
draft
submitted
reviewed
revision
approved
```

---

## 18. Modul Laporan

Laporan yang perlu disiapkan:

```text
- Laporan progres belajar murid
- Laporan nilai per kegiatan belajar
- Laporan nilai asesmen
- Laporan hasil remedial
- Laporan aktivitas diskusi
- Laporan proyek murid
- Rekap nilai kelas
- Export PDF
- Export Excel
```

Tahap awal cukup tampilkan laporan di dashboard. Export PDF/Excel dapat dibuat setelah fitur inti stabil.

---

## 19. Package Tambahan yang Disarankan

Gunakan package secara bertahap.

```text
Role dan permission:
spatie/laravel-permission

Export Excel:
maatwebsite/excel

Export PDF:
barryvdh/laravel-dompdf

Editor materi:
CKEditor, TinyMCE, atau TipTap

Grafik dashboard:
Chart.js atau ApexCharts

Backup database:
spatie/laravel-backup
```

Untuk tahap awal, cukup install:

```bash
composer require spatie/laravel-permission
```

Package lain dipasang setelah fitur dasar stabil.

---

## 20. Prioritas Pengembangan MVP

MVP atau versi awal sistem sebaiknya tidak langsung memuat semua fitur. Urutan terbaik:

### MVP 1 — Fondasi

```text
- Login
- Role admin/guru/murid
- Dashboard masing-masing role
- Database user dan kelas
```

### MVP 2 — Modul Pembelajaran

```text
- Guru membuat modul
- Guru membuat kegiatan belajar
- Guru mengisi materi
- Murid melihat modul dan kegiatan belajar
```

### MVP 3 — Aktivitas Interaktif

```text
- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba
- Ayo Menalar
- Ayo Menyimpulkan
- Jawaban murid tersimpan
```

### MVP 4 — Asesmen Otomatis

```text
- Pilihan ganda
- Pilihan ganda kompleks
- Benar/salah
- Menjodohkan
- Isian singkat
- Nilai otomatis
```

### MVP 5 — Uraian dan NLP Sederhana

```text
- Rubrik
- Keyword
- Similarity score
- Umpan balik otomatis
```

### MVP 6 — Remedial dan Progress

```text
- Status tuntas/remedial
- Percobaan asesmen
- Rekomendasi belajar ulang
- Progress belajar murid
```

### MVP 7 — Proyek dan Laporan

```text
- Upload proyek
- Penilaian proyek oleh guru
- Rekap nilai
- Laporan hasil belajar
```

---

## 21. Workflow Harian Pengembangan

Saat mulai coding:

```bash
composer run dev
```

Setelah membuat migration:

```bash
php artisan migrate
```

Jika mengubah seeder:

```bash
php artisan db:seed
```

Jika ingin reset database:

```bash
php artisan migrate:fresh --seed
```

Jika tampilan tidak berubah:

```bash
npm run dev
```

Jika konfigurasi bermasalah:

```bash
php artisan optimize:clear
```

Jika upload file tidak terbaca:

```bash
php artisan storage:link
```

---

## 22. Standar Pengujian

Gunakan pengujian bertahap.

### A. Black Box Testing

Contoh:

```text
Login admin berhasil
Login guru berhasil
Login murid berhasil
Guru dapat membuat modul
Murid dapat membuka modul
Murid dapat mengerjakan asesmen
Sistem dapat menghitung nilai
Sistem dapat menentukan remedial
Guru dapat melihat laporan
```

### B. Testing Role

Pastikan:

```text
Admin tidak masuk halaman murid
Murid tidak masuk halaman guru
Guru tidak masuk halaman admin
User tanpa login tidak bisa membuka dashboard
```

### C. Testing Penilaian

Pastikan:

```text
Jawaban pilihan ganda benar mendapat skor
Jawaban pilihan ganda salah mendapat 0
Pilihan ganda kompleks mendapat skor parsial
Menjodohkan dihitung berdasarkan pasangan benar
Isian singkat membaca keyword
Uraian menghasilkan skor rubrik + keyword + similarity
```

---

## 23. Target Hasil Pengembangan Awal

Setelah tahap awal selesai, sistem minimal harus memiliki:

```text
✓ Aplikasi Laravel Livewire berjalan
✓ Database MySQL terhubung
✓ Login dan register aktif
✓ Role admin, guru, murid tersedia
✓ Dashboard sesuai role tersedia
✓ Admin dapat mengelola pengguna
✓ Guru dapat membuat modul
✓ Guru dapat membuat kegiatan belajar
✓ Murid dapat membuka modul
✓ Murid dapat mengisi aktivitas
✓ Murid dapat mengerjakan asesmen
✓ Sistem dapat menghitung nilai dasar
```

---

## 24. Kesimpulan Panduan

Pengembangan sistem E-LKM Interaktif sebaiknya dimulai dari fondasi yang stabil, bukan langsung membuat semua fitur sekaligus. Fondasi terbaik adalah Laravel Official Livewire Starter Kit, MySQL, Tailwind CSS, dan Spatie Permission. Setelah autentikasi dan role berjalan, pengembangan dilanjutkan ke modul pembelajaran, kegiatan belajar, aktivitas interaktif, asesmen otomatis, NLP sederhana, remedial, proyek, dan laporan.

Dengan urutan ini, sistem akan berkembang secara rapi, terukur, dan sesuai dengan konsep pemodelan Waterfall yang sudah disusun.
