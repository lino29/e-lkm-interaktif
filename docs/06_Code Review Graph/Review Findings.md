---
type: review-findings
status: active
tags:
  - review
  - findings
  - quality
---

# Review Findings

File ini digunakan untuk mencatat temuan review kode, baik dari manual review, Codex, maupun code-review-graph.

## Template Finding

```markdown
## FINDING-YYYYMMDD-001 - Judul

### Severity
Low / Medium / High / Critical

### Area
Auth / Module / Activity / Assessment / Scoring / Remedial / Project / Report

### Sumber
Manual / Codex / code-review-graph / Test

### Deskripsi
-

### Bukti
-

### Risiko
-

### Rekomendasi
-

### Status
Open / Fixed / Accepted Risk

### Link Task
-
```

## Finding Awal

Belum ada temuan review kode.

## Checklist Review Umum

### Security

- [ ] Route sudah dilindungi middleware auth.
- [ ] Role/permission diterapkan.
- [ ] User hanya dapat melihat data miliknya/kelasnya.
- [ ] File upload divalidasi.
- [ ] Tidak ada credential di repository.

### Database

- [ ] Foreign key benar.
- [ ] Index tersedia untuk query laporan.
- [ ] Relasi model sesuai.
- [ ] Soft delete dipertimbangkan untuk data penting.
- [ ] Migration tidak merusak data lama.

### Livewire

- [ ] Validasi form tersedia.
- [ ] Authorization tersedia.
- [ ] Component tidak terlalu gemuk.
- [ ] Logic bisnis dipindahkan ke service.
- [ ] Error state ditangani.

### Assessment

- [ ] Attempt tidak bisa submit dua kali.
- [ ] Attempt limit diterapkan.
- [ ] Kunci jawaban tidak bocor ke murid.
- [ ] Scoring transparan.
- [ ] Skor uraian bisa direview guru.

### Testing

- [ ] Feature test untuk role.
- [ ] Feature test untuk CRUD utama.
- [ ] Unit test untuk scoring.
- [ ] Test remedial.
- [ ] Test authorization.
