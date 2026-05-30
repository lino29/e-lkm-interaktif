---
type: sprint-log
status: active
tags:
  - sprint
  - log
  - development
---

# Sprint Log

Gunakan file ini untuk mencatat progres harian/mingguan. Jangan menunggu fitur selesai besar. Catat perubahan kecil agar project mudah dilacak.

## Format Catatan Sprint

```markdown
## YYYY-MM-DD - Nama Sprint

### Tujuan
-

### Branch
-

### Task
- [ ] Task 1
- [ ] Task 2

### Perubahan Kode
-

### Test
```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
code-review-graph detect-changes --brief
```

### Risiko
-

### Catatan Keputusan
-

### Next Step
-
```

## 2026-05-30 - Inisialisasi Dokumentasi Project

### Tujuan

Membuat struktur `/docs` sebagai Obsidian Vault utama untuk pengembangan E-LKM.

### Branch

Belum ditentukan.

### Task

- [x] Membuat folder dokumentasi.
- [x] Membuat roadmap pengembangan.
- [x] Membuat analisis kebutuhan.
- [x] Membuat desain database awal.
- [x] Membuat prompt library untuk Codex.
- [x] Membuat dokumentasi code-review-graph.

### Perubahan Kode

Belum ada perubahan source aplikasi.

### Test

Belum perlu, karena hanya dokumentasi.

### Risiko

- Dokumentasi bisa tidak sinkron jika perubahan kode tidak dicatat.
- Task Codex bisa melebar jika prompt tidak spesifik.

### Catatan Keputusan

- `/docs` menjadi vault utama Obsidian.
- `.code-review-graph/obsidian` hanya output otomatis.
- AGENTS.md tetap dipakai sebagai instruksi AI agent.

### Next Step

- Jalankan `code-review-graph update --brief`.
- Minta Codex membaca `AGENTS.md` dan `/docs`.
- Mulai fitur pertama: Auth dan role.
