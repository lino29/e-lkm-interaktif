---
type: architecture
status: active
tags:
  - code-review-graph
  - architecture
  - generated-reference
---

# Architecture Notes

Dokumen ini menjadi catatan manual hasil pembacaan code-review-graph.

## Posisi Folder

Vault utama:

```text
docs/
```

Output generated code-review-graph:

```text
.code-review-graph/obsidian
```

Aturan:

- Jangan jadikan `.code-review-graph/obsidian` sebagai vault utama.
- Jangan menulis catatan manual di folder generated.
- Copy insight penting dari generated graph ke file ini atau ke [[Impact Analysis]].

## Command Penting

Build graph:

```bash
code-review-graph build
```

Update graph:

```bash
code-review-graph update --brief
```

Pantau perubahan:

```bash
code-review-graph watch
```

Cek perubahan:

```bash
code-review-graph detect-changes --brief
```

Export visualisasi Obsidian:

```bash
code-review-graph visualize --format obsidian
```

## Cara Membaca Graph

Gunakan graph untuk menjawab pertanyaan:

- File apa yang terdampak jika model diubah?
- Component mana yang memanggil service tertentu?
- Route mana yang menuju component tertentu?
- Test mana yang menutup fitur tertentu?
- Apakah perubahan berisiko menyentuh banyak komunitas kode?

## Area Arsitektur Project

Catat hasil pembacaan di bawah ini setelah menjalankan graph.

### Models

Belum dianalisis.

### Livewire Components

Belum dianalisis.

### Routes

Belum dianalisis.

### Services

Belum dianalisis.

### Tests

Belum dianalisis.

## Catatan Struktur Ideal

```text
Routes → Livewire Component → Service → Model → Database
```

Contoh alur asesmen:

```text
Murid Assessment Page
  → AssessmentAttemptService
  → AssessmentScoringService
  → StudentAnswer
  → AssessmentAttempt
```

Contoh alur modul:

```text
Guru Module Management
  → Module model
  → LearningUnit model
  → Materials/Media/Activities
```

## Risiko Arsitektur yang Harus Dipantau

- Livewire component terlalu gemuk.
- Logic scoring tersebar di banyak file.
- Query laporan menyebabkan N+1.
- Route tidak diproteksi role.
- Migration berubah tanpa test.
- Generated docs masuk ke Git terlalu besar.
