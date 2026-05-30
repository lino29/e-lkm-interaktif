---
type: analysis
status: active
tags:
  - aktor
  - user-role
  - analysis
---

# Aktor Sistem

Sistem memiliki tiga aktor utama: **Admin**, **Guru**, dan **Murid**.

## Admin

Admin adalah pengguna yang mengelola konfigurasi dasar sistem.

### Tanggung Jawab

- Mengelola pengguna.
- Mengelola role.
- Mengelola kelas.
- Mengelola data guru.
- Mengelola data murid.
- Melihat ringkasan aktivitas sistem.
- Mengelola pengaturan umum aplikasi.

### Hak Akses

| Fitur | Akses |
|---|---|
| Dashboard admin | Full |
| Data pengguna | Full |
| Data kelas | Full |
| Modul pembelajaran | View/Manage sesuai kebijakan |
| Laporan sistem | View |
| Pengaturan sistem | Full |

### Use Case

- Login.
- Kelola pengguna.
- Kelola kelas.
- Kelola guru.
- Kelola murid.
- Kelola role.
- Lihat laporan sistem.
- Logout.

### Risiko

- Admin dapat mengubah data besar.
- Perlu audit log pada tahap lanjutan.
- Perlu validasi ketat saat import data murid.

## Guru

Guru adalah pengguna yang mengelola konten pembelajaran dan memantau proses belajar murid.

### Tanggung Jawab

- Membuat modul pembelajaran.
- Membuat kegiatan belajar.
- Mengelola materi dan media.
- Membuat aktivitas interaktif.
- Membuat asesmen.
- Menentukan kunci jawaban.
- Mengatur rubrik dan keyword jawaban.
- Memantau progres murid.
- Memberi feedback.
- Menilai proyek.
- Melihat laporan kelas.

### Hak Akses

| Fitur | Akses |
|---|---|
| Dashboard guru | Full |
| Modul milik guru | Full |
| Kegiatan belajar | Full |
| Aktivitas E-LKM | Full |
| Bank soal | Full |
| Rubrik | Full |
| Jawaban murid | View/Review |
| Proyek murid | Review/Score |
| Laporan kelas | View/Export |

### Use Case

- Login.
- Kelola modul.
- Kelola kegiatan belajar.
- Kelola materi.
- Kelola aktivitas.
- Kelola asesmen.
- Kelola rubrik.
- Lihat jawaban murid.
- Lihat nilai.
- Beri feedback.
- Nilai proyek.
- Download laporan.
- Logout.

### Risiko

- Guru dapat salah input kunci jawaban.
- Guru dapat membuat asesmen tanpa rubrik.
- Perlu preview modul sebelum publish.

## Murid

Murid adalah pengguna utama yang mengikuti pembelajaran.

### Tanggung Jawab

- Membaca modul.
- Mengikuti aktivitas pembelajaran.
- Mengisi lembar kerja digital.
- Mengikuti forum diskusi.
- Mengerjakan asesmen.
- Mengikuti remedial jika belum tuntas.
- Mengunggah proyek.
- Melihat nilai dan feedback.

### Hak Akses

| Fitur | Akses |
|---|---|
| Dashboard murid | View |
| Modul aktif | View |
| Aktivitas | Submit |
| Forum | Comment/Reply |
| Asesmen | Attempt/Submit |
| Nilai | View own data |
| Proyek | Submit/Edit sebelum deadline |
| Portofolio | View own data |

### Use Case

- Login.
- Membuka modul.
- Membaca tujuan pembelajaran.
- Membaca materi.
- Mengamati media.
- Menjawab aktivitas.
- Diskusi/refleksi.
- Mengerjakan asesmen.
- Melihat nilai.
- Remedial.
- Mengunggah proyek.
- Melihat portofolio.
- Logout.

### Risiko

- Murid dapat submit jawaban kosong.
- Murid dapat mengulang asesmen di luar aturan jika attempt tidak dikunci.
- Murid tidak boleh melihat jawaban/kunci soal sebelum asesmen selesai.
