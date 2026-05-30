---
type: index
project: E-LKM Interaktif Energi Terbarukan
status: active
tags:
  - elkm
  - index
  - obsidian
---

# 00 Index - E-LKM Interaktif

Dokumen ini adalah pusat navigasi Obsidian Vault untuk pengembangan **E-LKM Interaktif Energi Terbarukan Berbasis Web**.

## Tujuan Vault

Vault ini digunakan untuk:

- Menyimpan dokumentasi analisis, desain, implementasi, pengujian, dan review.
- Menjadi sumber konteks untuk **VSCode**, **Codex**, dan **code-review-graph**.
- Mencegah pengembangan fitur keluar dari tujuan utama sistem.
- Menjaga agar setiap perubahan kode memiliki dasar kebutuhan, desain, dan catatan review.

## Prinsip Kerja

```text
Dokumentasi → Task kecil → Coding → Test → Review Graph → Commit → Catatan Perubahan
```

## Navigasi Utama

### Roadmap

- [[01_Roadmap/Roadmap Pengembangan|Roadmap Pengembangan]]
- [[01_Roadmap/Sprint Log|Sprint Log]]

### Analisis

- [[02_Analisis/Kebutuhan Sistem|Kebutuhan Sistem]]
- [[02_Analisis/Aktor Sistem|Aktor Sistem]]
- [[02_Analisis/Modul Prioritas|Modul Prioritas]]

### Desain Sistem

- [[03_Desain/Database Design|Database Design]]
- [[03_Desain/ERD|ERD]]
- [[03_Desain/Use Case|Use Case]]
- [[03_Desain/Flow User Murid|Flow User Murid]]

### Development

- [[04_Development/Setup Laravel|Setup Laravel]]
- [[04_Development/Livewire Components|Livewire Components]]
- [[04_Development/Scoring Service|Scoring Service]]
- [[04_Development/Bug Log|Bug Log]]

### Codex

- [[05_Codex/Prompt Library|Prompt Library]]
- [[05_Codex/Task Queue|Task Queue]]
- [[05_Codex/Codex Review Log|Codex Review Log]]

### Code Review Graph

- [[06_Code Review Graph/Architecture Notes|Architecture Notes]]
- [[06_Code Review Graph/Impact Analysis|Impact Analysis]]
- [[06_Code Review Graph/Review Findings|Review Findings]]

## Domain Utama Sistem

Sistem E-LKM dikembangkan untuk mendukung:

- Admin
- Guru
- Murid
- Modul pembelajaran digital
- Kegiatan belajar bertahap
- Aktivitas interaktif
- Forum diskusi/refleksi
- Asesmen otomatis
- Penilaian uraian berbasis rubrik, keyword matching, dan similarity score
- Remedial otomatis
- Portofolio proyek
- Laporan hasil belajar

## Tech Stack

- Laravel
- Livewire
- Blade
- Tailwind CSS
- MySQL
- Pest/PHPUnit
- Laravel Pint
- Laravel Storage
- Custom Scoring Service

## Aturan Dokumentasi

- Catatan manual project ditulis di folder `/docs`.
- Folder `.code-review-graph/obsidian` hanya output otomatis dari code-review-graph.
- Setiap fitur besar harus memiliki catatan kebutuhan, desain, task, dan review.
- Setiap perubahan kode harus dicatat di [[05_Codex/Codex Review Log|Codex Review Log]] atau [[01_Roadmap/Sprint Log|Sprint Log]].
