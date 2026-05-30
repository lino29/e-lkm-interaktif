---
type: development
status: draft
tags:
  - livewire
  - components
  - ui
---

# Livewire Components

Dokumen ini mendefinisikan rancangan komponen Livewire untuk E-LKM Interaktif.

## Prinsip Livewire

- Component menangani interaksi UI.
- Logic bisnis berat diletakkan di service class.
- Validasi dilakukan di action Livewire atau Form Object.
- Query harus scoped berdasarkan role/user.
- Hindari component terlalu besar.

## Struktur Komponen yang Disarankan

```text
app/Livewire/
├── Admin/
│   ├── Dashboard.php
│   ├── Users/
│   └── ClassRooms/
├── Guru/
│   ├── Dashboard.php
│   ├── Modules/
│   ├── LearningUnits/
│   ├── Activities/
│   ├── Assessments/
│   ├── Rubrics/
│   ├── Projects/
│   └── Reports/
└── Murid/
    ├── Dashboard.php
    ├── Modules/
    ├── Learning/
    ├── Assessments/
    ├── Remedial/
    ├── Projects/
    └── Scores/
```

## Admin Components

### Admin Dashboard

File:

```text
app/Livewire/Admin/Dashboard.php
resources/views/livewire/admin/dashboard.blade.php
```

Isi ringkasan:

- Total guru.
- Total murid.
- Total kelas.
- Total modul.
- Aktivitas terbaru.

### Users Management

Folder:

```text
app/Livewire/Admin/Users/
```

Komponen:

- `Index.php`
- `Create.php`
- `Edit.php`

Fitur:

- Search user.
- Filter role.
- Create user.
- Assign role.
- Assign class.

### ClassRooms Management

Komponen:

- `Index.php`
- `Create.php`
- `Edit.php`

Fitur:

- CRUD kelas.
- Lihat jumlah murid per kelas.

## Guru Components

### Guru Dashboard

Ringkasan:

- Modul yang dibuat.
- Jumlah kelas.
- Asesmen aktif.
- Murid remedial.
- Proyek menunggu review.

### Module Management

Folder:

```text
app/Livewire/Guru/Modules/
```

Komponen:

- `Index.php`
- `Create.php`
- `Edit.php`
- `Show.php`

Fitur:

- CRUD module.
- Publish/unpublish.
- Duplicate module tahap lanjutan.

### Learning Unit Management

Folder:

```text
app/Livewire/Guru/LearningUnits/
```

Komponen:

- `Index.php`
- `Create.php`
- `Edit.php`
- `Sort.php`

Fitur:

- CRUD kegiatan belajar.
- Sorting order.
- Input tujuan pembelajaran.

### Activity Management

Folder:

```text
app/Livewire/Guru/Activities/
```

Komponen:

- `Index.php`
- `Create.php`
- `Edit.php`
- `Preview.php`

Jenis activity:

- `mengamati`
- `bertanya`
- `mencoba`
- `menalar`
- `menyimpulkan`

Catatan:

- `answer_schema` digunakan untuk aktivitas tabel.
- Preview penting sebelum publish.

### Assessment Management

Folder:

```text
app/Livewire/Guru/Assessments/
```

Komponen:

- `Index.php`
- `Create.php`
- `Edit.php`
- `Questions.php`
- `Preview.php`
- `Results.php`

Fitur:

- CRUD assessment.
- CRUD question.
- Set KKTP.
- Set max attempt.
- Publish/unpublish.
- Lihat hasil.

### Rubric Management

Folder:

```text
app/Livewire/Guru/Rubrics/
```

Fitur:

- Set keyword.
- Set rubrik.
- Set bobot scoring.
- Preview scoring.

### Project Review

Folder:

```text
app/Livewire/Guru/Projects/
```

Fitur:

- List submitted projects.
- Detail project.
- Score project.
- Feedback project.

## Murid Components

### Murid Dashboard

Menampilkan:

- Modul aktif.
- Progress terakhir.
- Asesmen belum selesai.
- Status remedial.
- Proyek.

### My Modules

Folder:

```text
app/Livewire/Murid/Modules/
```

Fitur:

- List modul aktif.
- Status progress.
- Tombol lanjut belajar.

### Learning Page

Folder:

```text
app/Livewire/Murid/Learning/
```

Komponen:

- `ShowUnit.php`
- `ActivityPage.php`
- `ForumPage.php`

Fitur:

- Tampilkan materi.
- Tampilkan aktivitas.
- Submit jawaban.
- Update progress.

### Assessment Page

Folder:

```text
app/Livewire/Murid/Assessments/
```

Komponen:

- `Start.php`
- `Attempt.php`
- `Result.php`

Fitur:

- Mulai attempt.
- Save answer.
- Submit.
- Lihat hasil.
- Status tuntas/remedial.

### Project Page

Folder:

```text
app/Livewire/Murid/Projects/
```

Fitur:

- Buat draft proyek.
- Submit proyek.
- Upload file.
- Lihat feedback.

## Pola Naming

Gunakan nama component yang jelas:

```text
Guru/Modules/Index
Guru/Modules/Create
Guru/Modules/Edit
Murid/Learning/ShowUnit
Murid/Assessments/Attempt
```

Hindari nama generik:

```text
Page
Form
Data
Index2
```

## Pembagian Logic

Jangan letakkan logic besar di component:

```text
Bad:
Livewire component langsung menghitung seluruh scoring.

Good:
Livewire component memanggil AssessmentScoringService.
```

Contoh service:

```php
$score = app(AssessmentScoringService::class)->scoreAttempt($attempt);
```

## Validasi

Setiap form wajib memiliki:

- Required field.
- Tipe data.
- Max length.
- Authorization.
- Error message yang jelas.

## Test Minimum

- Component render.
- Create data.
- Update data.
- Authorization.
- Validation error.
- Submit action.
