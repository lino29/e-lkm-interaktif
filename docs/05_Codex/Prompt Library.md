---
type: codex
status: active
tags:
  - codex
  - prompt
  - ai-agent
---

# Prompt Library

Gunakan prompt berikut saat bekerja dengan Codex. Prinsip utama: **task kecil, scope jelas, output bisa dites**.

## Prompt Dasar untuk Semua Task

```text
Baca terlebih dahulu:
1. AGENTS.md
2. docs/00_Index.md
3. docs/01_Roadmap/Roadmap Pengembangan.md
4. docs/02_Analisis/Kebutuhan Sistem.md
5. docs/03_Desain/Database Design.md

Gunakan code-review-graph MCP tools terlebih dahulu untuk memahami struktur project sebelum membaca file manual.

Tugas:
[ISI TUGAS SPESIFIK]

Batasan:
- Jangan ubah file di luar scope task kecuali benar-benar diperlukan.
- Jangan mengubah .env.
- Jangan menghapus migration lama.
- Jangan menambah package tanpa persetujuan.
- Ikuti convention Laravel, Livewire, Pest, dan Pint.
- Gunakan service class untuk logic bisnis besar.
- Jangan taruh logic scoring besar di Blade atau Livewire component.

Output akhir:
- Ringkasan perubahan.
- File yang diubah.
- Risiko teknis.
- Command test yang dijalankan.
- Saran commit message.
```

## Prompt 1 - Analisis Struktur Project Saat Ini

```text
Baca AGENTS.md dan folder docs.

Gunakan code-review-graph untuk memahami struktur project.
Tolong analisis kondisi project saat ini:

1. Stack Laravel/Livewire yang terdeteksi.
2. Struktur route.
3. Struktur model.
4. Struktur Livewire component.
5. Fitur yang sudah ada.
6. Fitur yang belum ada dibanding docs/01_Roadmap/Roadmap Pengembangan.md.
7. Risiko teknis.
8. Rekomendasi task pertama yang paling aman.

Jangan ubah kode.
```

## Prompt 2 - Implementasi Auth dan Role

```text
Baca AGENTS.md dan docs terkait aktor sistem.

Tugas:
Implementasikan fondasi role admin, guru, dan murid.

Scope:
1. Cek apakah role/permission sudah tersedia.
2. Jika belum, buat implementasi sesuai convention project.
3. Buat redirect dashboard berdasarkan role.
4. Proteksi route dashboard.
5. Tambahkan test minimal untuk redirect role.

Batasan:
- Jangan mengubah .env.
- Jangan menambah package baru tanpa izin.
- Jika package role belum ada, jelaskan opsi terlebih dahulu sebelum implementasi.
```

## Prompt 3 - Dashboard Role

```text
Baca AGENTS.md, docs/02_Analisis/Aktor Sistem.md, dan docs/04_Development/Livewire Components.md.

Tugas:
Buat dashboard awal untuk admin, guru, dan murid.

Scope:
- Admin dashboard menampilkan placeholder data pengguna, kelas, modul.
- Guru dashboard menampilkan placeholder modul, asesmen, remedial, proyek.
- Murid dashboard menampilkan placeholder modul aktif, progress, asesmen, proyek.
- Gunakan Livewire jika sesuai struktur project.
- Gunakan komponen UI yang sudah tersedia.

Tambahkan test render dashboard jika memungkinkan.
```

## Prompt 4 - CRUD Modul Pembelajaran

```text
Baca AGENTS.md, docs/02_Analisis/Kebutuhan Sistem.md, docs/03_Desain/Database Design.md, dan docs/04_Development/Livewire Components.md.

Tugas:
Implementasikan CRUD modul pembelajaran untuk guru.

Scope:
1. Migration modules jika belum ada.
2. Model Module jika belum ada.
3. Factory dan seeder minimal jika sesuai.
4. Livewire component Guru/Modules/Index, Create, Edit.
5. Validasi form.
6. Authorization dasar: hanya guru/admin.
7. Test create/update module.

Batasan:
- Jangan implementasikan learning_units dulu kecuali diperlukan untuk relasi minimal.
- Jangan buat UI terlalu kompleks.
```

## Prompt 5 - CRUD Kegiatan Belajar

```text
Baca docs/03_Desain/Database Design.md dan docs/04_Development/Livewire Components.md.

Tugas:
Implementasikan kegiatan belajar sebagai child dari modul.

Scope:
- Migration learning_units.
- Model LearningUnit.
- Relasi Module hasMany LearningUnit.
- CRUD kegiatan belajar.
- Sorting order_number sederhana.
- Validasi title, objective, order_number.

Test:
- Guru dapat menambah learning unit pada module miliknya.
- Murid tidak dapat mengakses form admin/guru.
```

## Prompt 6 - Aktivitas E-LKM

```text
Baca docs/02_Analisis/Kebutuhan Sistem.md dan docs/03_Desain/Flow User Murid.md.

Tugas:
Implementasikan struktur awal aktivitas E-LKM.

Scope:
- Migration activities dan activity_answers jika belum ada.
- Activity type: mengamati, bertanya, mencoba, menalar, menyimpulkan.
- Guru dapat membuat aktivitas.
- Murid dapat submit jawaban teks.
- Simpan activity answer.
- Update progress sederhana.

Batasan:
- Jawaban tabel JSON boleh disiapkan, tetapi UI tabel kompleks tidak perlu dulu.
```

## Prompt 7 - Assessment Engine Awal

```text
Baca docs/04_Development/Scoring Service.md.

Tugas:
Implementasikan model dasar asesmen, soal, attempt, dan student answer.

Scope:
- Migration assessments.
- Migration questions.
- Migration assessment_attempts.
- Migration student_answers.
- Model dan relasi.
- Form guru untuk membuat asesmen minimal.
- Murid dapat start attempt dan submit jawaban.

Batasan:
- Scoring service cukup untuk pilihan ganda dulu.
- Jenis soal lain disiapkan secara struktur.
```

## Prompt 8 - Scoring Pilihan Ganda Kompleks

```text
Baca docs/04_Development/Scoring Service.md.

Tugas:
Implementasikan scoring pilihan ganda kompleks dengan skor parsial.

Aturan:
- Jawaban benar yang dipilih mendapat skor.
- Skor = jumlah benar dipilih / total kunci benar * bobot.
- Jawaban salah dipilih tidak menambah skor.
- Skor minimum 0 dan maksimum bobot.

Tambahkan unit test untuk:
- Semua benar.
- Sebagian benar.
- Semua salah.
- Jawaban kosong.
```

## Prompt 9 - Keyword Matching dan Similarity

```text
Baca docs/04_Development/Scoring Service.md.

Tugas:
Implementasikan KeywordMatcher dan TextSimilarityService tahap awal.

Scope:
- Normalize text.
- Keyword score 0-100.
- Jaccard similarity 0-100.
- Unit test.

Batasan:
- Jangan gunakan API eksternal.
- Jangan gunakan AI generatif.
- Fokus rule-based dulu.
```

## Prompt 10 - Review Perubahan Sebelum Commit

```text
Gunakan code-review-graph untuk review perubahan terakhir.

Tugas:
1. Jalankan/gunakan detect_changes.
2. Jelaskan file yang berubah.
3. Jelaskan impact radius.
4. Jelaskan potensi bug.
5. Cek apakah ada test terkait.
6. Sarankan command test minimal.
7. Sarankan commit message.

Jangan ubah kode kecuali saya minta.
```

## Prompt 11 - Perbaikan Bug

```text
Baca docs/04_Development/Bug Log.md.
Bug yang akan diperbaiki:
[PASTE BUG]

Tugas:
1. Gunakan code-review-graph untuk mencari file terkait.
2. Jelaskan dugaan penyebab.
3. Buat perubahan minimal.
4. Tambah/update test.
5. Jalankan test terkait.
6. Update ringkasan solusi.

Batasan:
- Jangan refactor besar.
- Jangan ubah fitur lain.
```
