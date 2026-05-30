---
type: design
status: draft
tags:
  - database
  - mysql
  - erd
---

# Database Design

Dokumen ini adalah rancangan database konseptual untuk E-LKM Interaktif.

## Prinsip Desain

- Gunakan struktur relasional MySQL.
- Gunakan foreign key untuk relasi utama.
- Gunakan enum/string terbatas untuk status dan tipe.
- Hindari menyimpan jawaban kompleks hanya dalam string jika struktur perlu dianalisis.
- Gunakan JSON untuk jawaban tabel/matching/complex options bila diperlukan.
- Simpan attempt agar riwayat asesmen tidak hilang.

## Tabel Utama

### users

Menyimpan data pengguna.

Kolom utama:

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| name | string | nama pengguna |
| email | string | unique |
| password | string | hashed |
| class_id | nullable fk | khusus murid |
| created_at | timestamp | default Laravel |
| updated_at | timestamp | default Laravel |

Catatan:

- Role sebaiknya dikelola dengan package permission atau tabel role terpisah.
- Jangan simpan role string manual jika project sudah memakai permission package.

### class_rooms

Menyimpan data kelas.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| name | string | contoh: X TKJ 1 |
| school_year | string | contoh: 2025/2026 |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### modules

Menyimpan modul pembelajaran.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| title | string | nama modul |
| subject | string | Projek IPAS |
| grade | string | X |
| semester | string | Genap |
| description | text | deskripsi modul |
| learning_model | string | STEM/PjBL/Pembelajaran Mendalam |
| status | string | draft/published/archived |
| created_by | fk users | guru pembuat |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### learning_units

Menyimpan kegiatan belajar.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| module_id | fk modules | parent module |
| title | string | judul kegiatan belajar |
| description | text | deskripsi |
| order_number | integer | urutan |
| learning_objective | text | tujuan kegiatan |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### materials

Menyimpan materi teks.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| learning_unit_id | fk learning_units | parent |
| title | string | judul materi |
| content | longText | HTML/Markdown sanitizable |
| order_number | integer | urutan |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### media

Menyimpan media pembelajaran.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| learning_unit_id | fk learning_units | parent |
| media_type | string | image/video/simulation/link/file |
| title | string | judul media |
| file_path | nullable string | file upload |
| url | nullable string | link eksternal |
| caption | nullable text | keterangan |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### activities

Menyimpan aktivitas E-LKM.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| learning_unit_id | fk learning_units | parent |
| activity_type | string | mengamati/bertanya/mencoba/menalar/menyimpulkan |
| title | string | judul |
| instruction | text | instruksi |
| answer_schema | nullable json | struktur tabel/field |
| order_number | integer | urutan |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### activity_answers

Menyimpan jawaban aktivitas murid.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| activity_id | fk activities | parent |
| student_id | fk users | murid |
| answer_text | nullable longText | jawaban teks |
| answer_json | nullable json | jawaban tabel/struktur |
| file_path | nullable string | upload opsional |
| score | nullable decimal | skor opsional |
| feedback | nullable text | feedback guru/sistem |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### assessments

Menyimpan asesmen.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| module_id | fk modules | nullable untuk asesmen akhir |
| learning_unit_id | nullable fk | untuk formatif |
| title | string | nama asesmen |
| assessment_type | string | formative/final |
| kktp | integer | nilai minimal |
| max_attempt | integer | jumlah percobaan |
| is_published | boolean | tampil atau tidak |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### questions

Menyimpan soal.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| assessment_id | fk assessments | parent |
| question_type | string | mcq/complex/true_false/matching/short/essay |
| question_text | longText | teks soal |
| options_json | nullable json | opsi jawaban |
| correct_answer_json | nullable json | kunci |
| cognitive_level | nullable string | LOTS/HOTS/C1-C6 |
| literacy_aspect | nullable string | sains/teknologi |
| score_weight | decimal | bobot |
| order_number | integer | urutan |
| created_at | timestamp |  |
| updated_at | timestamp |  |

Catatan:

- Gunakan `options_json` daripada `option_a` sampai `option_e` agar fleksibel untuk matching dan complex answer.
- Jika UI awal lebih sederhana, opsi A-E boleh dipakai dulu, tetapi JSON lebih scalable.

### question_keywords

Menyimpan keyword untuk isian/uraian.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| question_id | fk questions | parent |
| keyword | string | kata kunci |
| weight | decimal | bobot keyword |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### rubrics

Menyimpan rubrik penilaian uraian/proyek.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| question_id | nullable fk questions | untuk soal uraian |
| project_type | nullable string | untuk proyek |
| criterion | string | kriteria |
| max_score | decimal | skor maksimum |
| description | text | deskripsi |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### assessment_attempts

Menyimpan percobaan asesmen.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| assessment_id | fk assessments | parent |
| student_id | fk users | murid |
| attempt_number | integer | percobaan ke |
| total_score | decimal | nilai akhir |
| status | string | in_progress/submitted/passed/failed |
| started_at | timestamp | mulai |
| submitted_at | nullable timestamp | selesai |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### student_answers

Menyimpan jawaban asesmen.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| attempt_id | fk assessment_attempts | parent |
| question_id | fk questions | parent |
| answer_json | nullable json | jawaban struktur |
| answer_text | nullable longText | jawaban teks |
| score | decimal | skor akhir |
| feedback | nullable text | feedback |
| similarity_score | nullable decimal | skor similarity |
| keyword_score | nullable decimal | skor keyword |
| rubric_score | nullable decimal | skor rubrik |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### progress

Menyimpan progres belajar.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| student_id | fk users | murid |
| module_id | fk modules | parent |
| learning_unit_id | nullable fk | detail |
| status | string | not_started/in_progress/completed/remedial |
| progress_percentage | integer | 0-100 |
| completed_at | nullable timestamp | selesai |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### discussions

Menyimpan diskusi.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| learning_unit_id | fk learning_units | parent |
| student_id | fk users | penulis |
| content | text | isi |
| parent_id | nullable fk discussions | reply |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### projects

Menyimpan proyek murid.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| student_id | fk users | murid |
| module_id | fk modules | parent |
| project_title | string | judul |
| problem | text | masalah |
| objective | text | tujuan |
| tools_materials | text | alat bahan |
| procedure | text | langkah kerja |
| collected_data | text/json | data |
| expected_result | text | hasil harapan |
| conclusion | text | kesimpulan |
| file_path | nullable string | upload |
| score | nullable decimal | nilai |
| feedback | nullable text | feedback |
| status | string | draft/submitted/reviewed |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### glossaries

Menyimpan glosarium.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| module_id | fk modules | parent |
| term | string | istilah |
| definition | text | arti |
| created_at | timestamp |  |
| updated_at | timestamp |  |

### references

Menyimpan daftar pustaka.

| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigint | primary key |
| module_id | fk modules | parent |
| reference_text | text | referensi |
| created_at | timestamp |  |
| updated_at | timestamp |  |

## Relasi Utama

```text
class_rooms hasMany users
users hasMany modules through created_by
users hasMany activity_answers
users hasMany assessment_attempts
users hasMany projects

modules hasMany learning_units
modules hasMany glossaries
modules hasMany references
modules hasMany assessments

learning_units hasMany materials
learning_units hasMany media
learning_units hasMany activities
learning_units hasMany discussions
learning_units hasMany assessments

activities hasMany activity_answers

assessments hasMany questions
assessments hasMany assessment_attempts

questions hasMany question_keywords
questions hasMany rubrics
questions hasMany student_answers

assessment_attempts hasMany student_answers
```

## Catatan Implementasi Laravel

- Buat migration dengan `php artisan make:model NamaModel -m`.
- Buat factory dan seeder untuk model penting.
- Gunakan foreign key constraint.
- Gunakan cascade delete hanya jika aman.
- Untuk data belajar, lebih aman gunakan restrict atau soft delete.
- Gunakan enum class atau constant untuk status dan tipe.
