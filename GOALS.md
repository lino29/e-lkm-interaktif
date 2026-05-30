# AGENTS.md - E-LKM Interaktif

## Tujuan Project
Project ini adalah E-LKM Interaktif Energi Terbarukan berbasis web untuk Projek IPAS SMK kelas X.

## Tech Stack
- Laravel
- Livewire
- Blade
- Tailwind CSS
- MySQL
- Pest/PHPUnit
- Custom scoring service untuk asesmen otomatis

## Prinsip Pengembangan
1. Baca dokumentasi project di folder `docs/` sebelum mengubah kode.
2. Jangan menghapus fitur yang sudah ada tanpa alasan jelas.
3. Semua perubahan harus kecil, terukur, dan bisa direview.
4. Gunakan migration, model, policy, request validation, dan service class sesuai kebutuhan.
5. Hindari logic besar langsung di Blade atau Livewire component.
6. Jangan menyimpan credential di repository.
7. Setelah mengubah kode, jalankan test atau minimal command validasi yang relevan.

## Fokus Fitur E-LKM
- Role admin, guru, murid
- Modul pembelajaran
- Kegiatan belajar
- Aktivitas Ayo Mengamati, Ayo Bertanya, Ayo Mencoba, Ayo Menalar, Ayo Menyimpulkan
- Forum diskusi/refleksi
- Asesmen otomatis
- Remedial
- Proyek murid
- Laporan nilai dan progress

## Aturan Review
Sebelum selesai, berikan ringkasan:
- File yang diubah
- Alasan perubahan
- Risiko teknis
- Test yang dijalankan
- Saran lanjutan
