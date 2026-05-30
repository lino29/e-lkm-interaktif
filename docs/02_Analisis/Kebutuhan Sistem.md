---
type: analysis
status: active
tags:
  - kebutuhan
  - analisis
  - elkm
---

# Kebutuhan Sistem

## Ringkasan

Sistem yang dikembangkan adalah **E-LKM Interaktif Energi Terbarukan Berbasis Web**. Sistem bertujuan mengubah bahan ajar E-LKM menjadi aplikasi pembelajaran digital yang terstruktur, interaktif, adaptif, dan terukur.

## Tujuan Sistem

Sistem harus mampu:

1. Menyediakan modul pembelajaran digital berbasis kegiatan belajar.
2. Memfasilitasi aktivitas:
   - Ayo Mengamati
   - Ayo Bertanya
   - Ayo Mencoba
   - Ayo Menalar
   - Ayo Menyimpulkan
3. Menyediakan forum diskusi/refleksi.
4. Menyediakan asesmen formatif dan akhir.
5. Melakukan penilaian otomatis.
6. Menilai uraian singkat dengan rubrik, keyword matching, dan similarity score.
7. Menyediakan remedial otomatis.
8. Menyediakan portofolio proyek.
9. Menyediakan laporan progres dan nilai.

## Pengguna Sistem

- [[Aktor Sistem#Admin|Admin]]
- [[Aktor Sistem#Guru|Guru]]
- [[Aktor Sistem#Murid|Murid]]

## Kebutuhan Fungsional

### 1. Autentikasi dan Role

Fitur:

- Login.
- Logout.
- Manajemen akun.
- Role admin, guru, murid.
- Proteksi route berdasarkan role.

Acceptance Criteria:

- User tidak bisa mengakses halaman tanpa login.
- User diarahkan ke dashboard sesuai role.
- Route role lain menghasilkan akses ditolak.

### 2. Manajemen Pengguna

Fitur:

- Tambah, edit, hapus pengguna.
- Import data murid.
- Kelola kelas.
- Kelola guru.
- Hubungkan murid dengan kelas.
- Hubungkan guru dengan kelas/modul.

Acceptance Criteria:

- Admin dapat mengelola user.
- Email user unik.
- Password terenkripsi.
- Role dapat diberikan ke user.

### 3. Modul Pembelajaran

Fitur:

- CRUD modul.
- CRUD kegiatan belajar.
- CRUD tujuan pembelajaran.
- CRUD materi.
- Upload media gambar/video.
- Glosarium.
- Daftar pustaka.
- Aktivasi/nonaktivasi modul.

Acceptance Criteria:

- Guru dapat membuat modul.
- Modul memiliki minimal satu kegiatan belajar.
- Murid hanya melihat modul aktif.
- Urutan kegiatan belajar dapat ditentukan.

### 4. Aktivitas Interaktif

Fitur:

- Ayo Mengamati.
- Ayo Bertanya.
- Ayo Mencoba.
- Ayo Menalar.
- Ayo Menyimpulkan.
- Jawaban teks.
- Jawaban tabel.
- Upload file opsional.
- Feedback guru/sistem.

Acceptance Criteria:

- Murid dapat mengisi aktivitas.
- Jawaban tersimpan.
- Status aktivitas berubah menjadi selesai.
- Guru dapat membaca jawaban.

### 5. Forum Diskusi dan Refleksi

Fitur:

- Forum per kegiatan belajar.
- Komentar.
- Reply komentar.
- Feedback guru.
- Riwayat diskusi.

Acceptance Criteria:

- Murid dapat berdiskusi sesuai modul.
- Guru dapat memoderasi dan memberi tanggapan.
- Diskusi terhubung dengan kegiatan belajar.

### 6. Asesmen

Jenis soal:

- Pilihan ganda biasa.
- Pilihan ganda kompleks.
- Benar/salah.
- Menjodohkan.
- Isian singkat.
- Uraian singkat.

Fitur:

- Bank soal.
- Kunci jawaban.
- Bobot skor.
- Level kognitif.
- Aspek literasi.
- Attempt.
- Submit jawaban.

Acceptance Criteria:

- Murid dapat mengerjakan asesmen.
- Sistem menyimpan attempt.
- Sistem menghitung nilai.
- Guru dapat melihat hasil.

### 7. Scoring Otomatis

Fitur:

- Objective scoring.
- Partial scoring.
- Keyword matching.
- Similarity score.
- Rubric scoring.
- Feedback otomatis.

Acceptance Criteria:

- Semua tipe soal objektif dikoreksi otomatis.
- Uraian memiliki skor keyword dan similarity.
- Guru dapat meninjau skor uraian.

### 8. Remedial

Fitur:

- KKTP per asesmen.
- Status tuntas/belum tuntas.
- Rekomendasi belajar ulang.
- Batas percobaan.
- Riwayat attempt.

Acceptance Criteria:

- Nilai >= KKTP: lanjut.
- Nilai < KKTP: remedial.
- Attempt tidak melebihi batas.
- Guru dapat melihat daftar remedial.

### 9. Proyek dan Portofolio

Fitur:

- Pilih proyek.
- Form rancangan.
- Upload bukti.
- Kesimpulan.
- Rubrik proyek.
- Feedback guru.

Acceptance Criteria:

- Murid dapat mengirim proyek.
- Guru dapat menilai proyek.
- Proyek masuk portofolio murid.

### 10. Laporan

Fitur:

- Progres belajar.
- Nilai per asesmen.
- Status remedial.
- Aktivitas diskusi.
- Proyek murid.
- Rekap kelas.
- Export PDF/Excel tahap lanjutan.

Acceptance Criteria:

- Guru dapat melihat rekap kelas.
- Guru dapat melihat detail murid.
- Admin dapat melihat ringkasan sistem.

## Kebutuhan Nonfungsional

### Keamanan

- Password harus terenkripsi.
- Role dan permission wajib diterapkan.
- Validasi input wajib.
- File upload dibatasi tipe dan ukuran.
- Data murid tidak boleh diakses role tidak sah.

### Kinerja

- Halaman dashboard harus cepat.
- Query laporan harus efisien.
- Gunakan eager loading untuk relasi besar.
- Hindari logic berat di Blade.

### Skalabilitas

- Struktur database mendukung modul lain selain energi terbarukan.
- Scoring service dibuat modular.
- Activity type dibuat fleksibel.

### Maintainability

- Logic domain diletakkan di service class.
- Component Livewire tidak memuat logic scoring besar.
- Test dibuat untuk fitur penting.
- Gunakan Laravel Pint.

### Responsivitas

- UI harus nyaman di laptop dan smartphone.
- Form murid harus sederhana.
- Dashboard guru harus padat informasi.

## Batasan Sistem Tahap Awal

- Fokus materi energi terbarukan.
- Fokus Projek IPAS kelas X SMK.
- AI/NLP masih rule-based.
- Belum memakai AI generatif penuh.
- Sistem berbasis web, bukan Android native.
- Proyek tetap dinilai guru.
