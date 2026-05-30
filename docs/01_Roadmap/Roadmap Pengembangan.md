---
type: roadmap
status: active
tags:
  - roadmap
  - waterfall
  - elkm
---

# Roadmap Pengembangan

Roadmap ini mengikuti model **Waterfall**: analisis kebutuhan, desain sistem, implementasi, pengujian, penerapan, dan pemeliharaan.

## Target Produk

**E-LKM Interaktif Energi Terbarukan Berbasis Web** adalah aplikasi pembelajaran Projek IPAS kelas X SMK yang menyediakan modul pembelajaran, aktivitas interaktif, asesmen otomatis, remedial, forum, proyek, dan laporan.

## Tahap 0 - Baseline Project

Tujuan: memastikan fondasi Laravel siap untuk dikembangkan.

Checklist:

- [ ] Project Laravel berjalan lokal.
- [ ] Database MySQL terhubung.
- [ ] Authentication berjalan.
- [ ] Struktur starter kit dipahami.
- [ ] Git repository bersih.
- [ ] AGENTS.md tersedia.
- [ ] code-review-graph aktif.
- [ ] Obsidian Vault `/docs` tersedia.

Command validasi:

```bash
php artisan about
php artisan route:list
php artisan test --compact
npm run build
code-review-graph status
```

## Tahap 1 - Auth dan Role

Tujuan: membuat akses sistem untuk admin, guru, dan murid.

Scope:

- Role admin.
- Role guru.
- Role murid.
- Redirect dashboard berdasarkan role.
- Proteksi route berdasarkan role.

Output teknis:

- Model/User tetap mengikuti struktur Laravel.
- Role/permission menggunakan package yang sudah disetujui atau mekanisme internal project.
- Middleware atau policy untuk pembatasan akses.

Acceptance Criteria:

- [ ] Admin login ke dashboard admin.
- [ ] Guru login ke dashboard guru.
- [ ] Murid login ke dashboard murid.
- [ ] User tanpa role tidak bisa mengakses dashboard khusus.
- [ ] Test role redirect tersedia.

## Tahap 2 - Manajemen Data Dasar

Tujuan: admin dapat mengelola data awal sistem.

Scope:

- Kelas.
- Data guru.
- Data murid.
- Relasi murid dengan kelas.
- Relasi guru dengan modul/kelas.

Acceptance Criteria:

- [ ] Admin dapat membuat kelas.
- [ ] Admin dapat membuat akun guru.
- [ ] Admin dapat membuat akun murid.
- [ ] Admin dapat menghubungkan murid ke kelas.
- [ ] Validasi form berjalan.
- [ ] Data tidak dapat diakses oleh role yang tidak berhak.

## Tahap 3 - Modul Pembelajaran

Tujuan: guru dapat membuat struktur E-LKM.

Scope:

- Modul.
- Kegiatan belajar.
- Tujuan pembelajaran.
- Materi.
- Media.
- Glosarium.
- Daftar pustaka.

Struktur modul:

```text
Modul
├── Pendahuluan
├── Kegiatan Belajar
│   ├── Materi
│   ├── Media
│   ├── Aktivitas
│   ├── Forum
│   └── Asesmen Formatif
├── Asesmen Akhir
├── Glosarium
└── Referensi
```

Acceptance Criteria:

- [ ] Guru dapat membuat modul.
- [ ] Guru dapat membuat kegiatan belajar.
- [ ] Guru dapat mengatur urutan kegiatan belajar.
- [ ] Guru dapat menambahkan materi dan media.
- [ ] Murid hanya melihat modul yang aktif.
- [ ] Modul dapat dinonaktifkan.

## Tahap 4 - Aktivitas Interaktif E-LKM

Tujuan: murid mengikuti alur aktivitas belajar bertahap.

Jenis aktivitas:

- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba
- Ayo Menalar
- Ayo Menyimpulkan

Acceptance Criteria:

- [ ] Guru dapat membuat aktivitas per kegiatan belajar.
- [ ] Murid dapat mengisi jawaban aktivitas.
- [ ] Jawaban tersimpan.
- [ ] Status aktivitas tampil: belum mulai, dikerjakan, selesai.
- [ ] Guru dapat melihat jawaban murid.

## Tahap 5 - Forum Diskusi dan Refleksi

Tujuan: mendukung interaksi pembelajaran.

Scope:

- Forum per kegiatan belajar.
- Komentar utama.
- Balasan komentar.
- Moderasi guru.
- Penilaian partisipasi opsional.

Acceptance Criteria:

- [ ] Murid dapat menulis pendapat.
- [ ] Murid dapat membalas pendapat teman.
- [ ] Guru dapat memberi komentar.
- [ ] Komentar tersimpan dengan timestamp.
- [ ] Akses forum sesuai kelas/modul.

## Tahap 6 - Asesmen dan Bank Soal

Tujuan: guru dapat membuat asesmen formatif dan akhir.

Jenis soal:

- Pilihan ganda biasa
- Pilihan ganda kompleks
- Benar/salah
- Menjodohkan
- Isian singkat
- Uraian singkat

Acceptance Criteria:

- [ ] Guru dapat membuat asesmen.
- [ ] Guru dapat membuat soal.
- [ ] Guru dapat mengatur kunci jawaban.
- [ ] Guru dapat mengatur bobot skor.
- [ ] Murid dapat mengerjakan asesmen.
- [ ] Attempt tersimpan.

## Tahap 7 - Scoring Service

Tujuan: sistem dapat memberi nilai otomatis.

Scope:

- Koreksi pilihan ganda.
- Koreksi pilihan ganda kompleks.
- Koreksi benar/salah.
- Koreksi menjodohkan.
- Koreksi isian berbasis keyword.
- Koreksi uraian berbasis rubrik, keyword matching, similarity score.

Acceptance Criteria:

- [ ] Service scoring terpisah dari controller/Livewire component.
- [ ] Semua tipe soal memiliki test.
- [ ] Skor attempt dihitung otomatis.
- [ ] Umpan balik dasar muncul setelah submit.
- [ ] Skor uraian bisa ditinjau guru.

## Tahap 8 - Remedial

Tujuan: sistem mengarahkan murid yang belum tuntas.

Aturan:

- Jika nilai >= KKTP, murid lanjut.
- Jika nilai < KKTP, murid remedial.
- Attempt dibatasi oleh `max_attempt`.
- Sistem menyimpan riwayat attempt.

Acceptance Criteria:

- [ ] Status tuntas/belum tuntas tersimpan.
- [ ] Murid belum tuntas mendapat rekomendasi belajar ulang.
- [ ] Murid dapat mengulang asesmen sesuai batas percobaan.
- [ ] Guru dapat melihat daftar murid remedial.

## Tahap 9 - Proyek dan Portofolio

Tujuan: murid mengunggah hasil aksi sederhana energi terbarukan.

Scope:

- Pilihan proyek.
- Masalah yang ditemukan.
- Tujuan proyek.
- Alat dan bahan.
- Langkah kerja.
- Data yang dikumpulkan.
- Upload file.
- Kesimpulan.
- Penilaian guru.

Acceptance Criteria:

- [ ] Murid dapat membuat rancangan proyek.
- [ ] Murid dapat upload foto/video/laporan.
- [ ] Guru dapat memberi nilai dan feedback.
- [ ] Proyek masuk portofolio murid.

## Tahap 10 - Laporan dan Analitik Guru

Tujuan: guru dapat memantau proses dan hasil belajar.

Laporan:

- Progres murid.
- Nilai per kegiatan.
- Nilai asesmen.
- Status remedial.
- Aktivitas diskusi.
- Proyek murid.
- Rekap kelas.

Acceptance Criteria:

- [ ] Guru dapat melihat rekap kelas.
- [ ] Guru dapat melihat detail murid.
- [ ] Guru dapat melihat progres per modul.
- [ ] Export PDF/Excel disiapkan setelah fitur dasar stabil.

## Prioritas Implementasi

| Urutan | Modul | Status |
|---|---|---|
| 1 | Auth dan role | Todo |
| 2 | Dashboard role | Todo |
| 3 | Manajemen modul | Todo |
| 4 | Kegiatan belajar | Todo |
| 5 | Aktivitas E-LKM | Todo |
| 6 | Asesmen | Todo |
| 7 | Scoring service | Todo |
| 8 | Remedial | Todo |
| 9 | Proyek | Todo |
| 10 | Laporan | Todo |

## Definisi Selesai

Satu fitur dinyatakan selesai jika:

- Kebutuhan sudah jelas.
- Migration/model/component sesuai.
- UI dapat digunakan.
- Validasi dan authorization tersedia.
- Test minimal tersedia.
- Laravel Pint dijalankan.
- code-review-graph detect-changes sudah dibaca.
- Ringkasan perubahan dicatat di Sprint Log.
