# Rencana Pengembangan Fitur Export Laporan (PDF/Excel)

## 1. Tujuan
Memungkinkan Guru dan Admin untuk mengekspor data laporan kelas, progres murid, dan penilaian (asesmen, proyek, diskusi) ke dalam format PDF dan Excel.

## 2. Package Rekomendasi
Pemasangan package berikut menunggu persetujuan/task berikutnya:
- **Excel**: `maatwebsite/excel` (Mendukung pembuatan multiple sheets, styling, dan export ke berbagai format spreadsheet).
- **PDF**: `barryvdh/laravel-dompdf` (Ringan, mudah digunakan dengan blade templates untuk generate laporan PDF yang terformat rapi).

## 3. Format Data Target Export
Data yang akan direkap per kelas/modul:
1. **Overview Kelas/Modul**: Jumlah murid, rata-rata nilai, jumlah murid tuntas/remedial.
2. **Progress Murid**: Daftar murid dengan status penyelesaian (Sedang Dikerjakan, Tuntas, Remedial).
3. **Nilai Asesmen Formatif**: Nilai yang didapat murid untuk setiap Kegiatan Belajar (KB).
4. **Nilai Asesmen Akhir (Sumatif)**: Nilai total pemahaman konsep di akhir modul.
5. **Skor Proyek**: Nilai total proyek beserta rincian per kriteria rubrik (Identifikasi Masalah, Kelayakan, dll).
6. **Partisipasi Forum**: Jumlah diskusi/balasan murid beserta rata-rata skor partisipasi dari guru.

## 4. Struktur Implementasi
- **Service**: `app/Services/Report/ReportExportService.php` (Skeleton sudah dibuat).
- **Controller**: `ReportExportController` dengan method `exportPdf(int $moduleId)` dan `exportExcel(int $moduleId)`.
- **Livewire Integration**: Penambahan tombol "Export PDF" dan "Export Excel" pada komponen `Reports.php` (Guru & Admin).
- **View/Template**: Blade file khusus untuk layout PDF (misal `resources/views/exports/reports-pdf.blade.php`).

## 5. Prasyarat Pelaksanaan
- Seluruh core scoring (Rubric, Formative, Final Assessment, Participation) telah stabil dan lolos test.
- Relasi Eloquent untuk menarik seluruh data report dalam satu atau sedikit query (menggunakan Eager Loading) sudah dioptimalkan untuk menghindari N+1 problem saat export massal.
