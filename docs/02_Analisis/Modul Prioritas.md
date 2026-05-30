---
type: prioritization
status: active
tags:
  - modul
  - prioritas
  - backlog
---

# Modul Prioritas

Dokumen ini menentukan urutan pengembangan agar Codex tidak mengerjakan fitur secara acak.

## Prinsip Prioritas

Prioritas ditentukan berdasarkan dependensi teknis:

```text
Auth → Role → Dashboard → Data Master → Modul → Aktivitas → Asesmen → Scoring → Remedial → Proyek → Laporan
```

## P0 - Wajib Sebelum Fitur Lain

### 1. Auth dan Role

Alasan:

- Semua fitur butuh user.
- Semua akses bergantung pada role.

Task:

- [ ] Pastikan auth berjalan.
- [ ] Tambah role admin/guru/murid.
- [ ] Redirect dashboard berdasarkan role.
- [ ] Proteksi route.

### 2. Dashboard Role

Alasan:

- Menjadi titik masuk semua user.

Task:

- [ ] Dashboard admin.
- [ ] Dashboard guru.
- [ ] Dashboard murid.

## P1 - Fondasi Data Pembelajaran

### 3. Data Kelas dan User

Task:

- [ ] CRUD kelas.
- [ ] CRUD guru.
- [ ] CRUD murid.
- [ ] Assign murid ke kelas.
- [ ] Assign guru ke kelas/modul.

### 4. Modul Pembelajaran

Task:

- [ ] CRUD modul.
- [ ] CRUD kegiatan belajar.
- [ ] CRUD materi.
- [ ] CRUD media.
- [ ] Publish/unpublish modul.

## P2 - Core Learning Experience

### 5. Aktivitas E-LKM

Task:

- [ ] Activity type enum/config.
- [ ] Ayo Mengamati.
- [ ] Ayo Bertanya.
- [ ] Ayo Mencoba.
- [ ] Ayo Menalar.
- [ ] Ayo Menyimpulkan.
- [ ] Activity answers.

### 6. Forum Diskusi

Task:

- [ ] Forum per learning unit.
- [ ] Comment.
- [ ] Reply.
- [ ] Teacher feedback.

## P3 - Assessment Engine

### 7. Bank Soal

Task:

- [ ] CRUD assessment.
- [ ] CRUD question.
- [ ] Option handling.
- [ ] Correct answer handling.
- [ ] Weight handling.

### 8. Attempt dan Submit

Task:

- [ ] Start attempt.
- [ ] Save answer.
- [ ] Submit attempt.
- [ ] Lock submitted attempt.

### 9. Scoring Service

Task:

- [ ] Score pilihan ganda.
- [ ] Score pilihan ganda kompleks.
- [ ] Score benar/salah.
- [ ] Score menjodohkan.
- [ ] Score isian keyword.
- [ ] Score uraian rubrik + keyword + similarity.

## P4 - Adaptive Learning

### 10. Remedial

Task:

- [ ] KKTP.
- [ ] Status tuntas.
- [ ] Status remedial.
- [ ] Attempt limit.
- [ ] Rekomendasi materi ulang.

## P5 - Project Based Learning

### 11. Proyek Murid

Task:

- [ ] Project form.
- [ ] Upload file.
- [ ] Project status.
- [ ] Teacher scoring.
- [ ] Portfolio view.

## P6 - Reporting

### 12. Laporan Guru

Task:

- [ ] Rekap nilai kelas.
- [ ] Detail nilai murid.
- [ ] Progress module.
- [ ] Status remedial.
- [ ] Project report.

## Backlog Lanjutan

- [ ] Export PDF.
- [ ] Export Excel.
- [ ] Dashboard chart.
- [ ] Audit log.
- [ ] Import siswa dari Excel.
- [ ] Notification.
- [ ] Deadline assignment.
- [ ] Content versioning.
- [ ] AI generatif untuk feedback lanjutan.
