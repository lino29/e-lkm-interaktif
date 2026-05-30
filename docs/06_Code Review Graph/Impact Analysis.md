---
type: impact-analysis
status: active
tags:
  - impact
  - review
  - code-review-graph
---

# Impact Analysis

Gunakan file ini untuk mencatat hasil analisis dampak perubahan.

## Template Impact Analysis

```markdown
## YYYY-MM-DD - Branch - Fitur

### Command
```bash
code-review-graph detect-changes --brief
```

### Ringkasan Perubahan
-

### File Terdampak
-

### Impact Radius
Low / Medium / High

### Flow Terdampak
-

### Test Terkait
-

### Risiko
-

### Keputusan
Proceed / Revise / Revert

### Catatan
-
```

## 2026-05-30 - Initial Docs

### Command

Belum dijalankan.

### Ringkasan Perubahan

Hanya dokumentasi `/docs`.

### File Terdampak

- Folder docs.

### Impact Radius

Low.

### Flow Terdampak

Tidak ada flow aplikasi.

### Test Terkait

Tidak ada.

### Risiko

Dokumentasi belum dibandingkan dengan source code aktual.

### Keputusan

Proceed.

### Catatan

Setelah fitur pertama dibuat, jalankan `code-review-graph detect-changes --brief`.
