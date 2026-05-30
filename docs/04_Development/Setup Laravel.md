---
type: development
status: active
tags:
  - laravel
  - setup
  - local
---

# Setup Laravel

Dokumen ini menjadi catatan setup lokal project E-LKM Interaktif.

## Environment

Sesuaikan dengan project saat ini:

- PHP 8.3
- Laravel 13
- Livewire 4
- Tailwind CSS 4
- MySQL/MariaDB
- Pest/PHPUnit
- Laravel Pint
- Laravel Boost
- code-review-graph

## Command Harian

Jalankan dari root project:

```bash
composer run dev
```

Jika dijalankan manual:

```bash
php artisan serve
npm run dev
```

## Validasi Project

```bash
php artisan about
php artisan route:list
php artisan test --compact
npm run build
vendor/bin/pint --dirty --format agent
code-review-graph status
```

## Database

Cek konfigurasi:

```bash
php artisan config:show database.default
```

Migrasi:

```bash
php artisan migrate
```

Reset lokal:

```bash
php artisan migrate:fresh --seed
```

Catatan:

- Jangan jalankan `migrate:fresh` pada data produksi.
- Untuk debugging data, gunakan query read-only atau database tool, bukan membuat script sembarangan.

## Storage

Jika upload file tidak tampil:

```bash
php artisan storage:link
```

Folder upload sebaiknya dibagi:

```text
storage/app/public/media
storage/app/public/project-submissions
storage/app/public/activity-submissions
```

## Build Frontend

Jika tampilan tidak berubah:

```bash
npm run dev
```

Untuk build production:

```bash
npm run build
```

## Pint

Setelah mengubah PHP:

```bash
vendor/bin/pint --dirty --format agent
```

## Testing

Jalankan semua test:

```bash
php artisan test --compact
```

Jalankan test tertentu:

```bash
php artisan test --compact --filter=AssessmentScoringTest
```

## code-review-graph

Update graph:

```bash
code-review-graph update --brief
```

Cek perubahan:

```bash
code-review-graph detect-changes --brief
```

Export Obsidian generated graph:

```bash
code-review-graph visualize --format obsidian
```

Catatan:

- Output graph ada di `.code-review-graph/obsidian`.
- Vault utama tetap `/docs`.
- Jangan menulis dokumentasi manual di folder generated graph.

## Struktur Folder Penting

```text
app/
├── Livewire/
├── Models/
├── Services/
├── Policies/
└── Support/

database/
├── migrations/
├── seeders/
└── factories/

resources/
├── views/
│   ├── livewire/
│   ├── components/
│   └── layouts/
└── css/

routes/
├── web.php
└── auth.php

tests/
├── Feature/
└── Unit/
```

## Catatan untuk Codex

Saat meminta Codex mengubah project:

1. Minta membaca `AGENTS.md`.
2. Minta membaca `/docs/00_Index.md`.
3. Minta memakai code-review-graph dulu.
4. Batasi scope fitur.
5. Minta test dan summary perubahan.
