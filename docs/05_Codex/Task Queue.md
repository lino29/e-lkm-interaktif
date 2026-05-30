---
type: codex-task-queue
status: active
tags:
  - task
  - codex
  - backlog
---

# Task Queue

Gunakan file ini untuk menyusun task sebelum dikirim ke Codex.

## Cara Menggunakan

1. Tulis task kecil.
2. Pastikan ada acceptance criteria.
3. Tentukan file docs yang harus dibaca Codex.
4. Setelah selesai, pindahkan ke Done.
5. Catat hasil di [[Codex Review Log]].

## Todo

### TASK-001 - Analisis Kondisi Repository Saat Ini

Status: Todo  
Prioritas: P0  
Branch: `analysis/current-state`

Docs yang wajib dibaca:

- `AGENTS.md`
- `docs/00_Index.md`
- `docs/01_Roadmap/Roadmap Pengembangan.md`

Prompt:

```text
Baca AGENTS.md dan folder docs.
Gunakan code-review-graph untuk menganalisis struktur project saat ini.
Jangan ubah kode.

Laporkan:
1. Fitur yang sudah ada.
2. Struktur model.
3. Struktur Livewire.
4. Struktur route.
5. Gap terhadap roadmap.
6. Rekomendasi task berikutnya.
```

Acceptance Criteria:

- [ ] Ada laporan kondisi project.
- [ ] Ada daftar fitur yang sudah dan belum ada.
- [ ] Ada rekomendasi prioritas.

### TASK-002 - Auth Role Admin Guru Murid

Status: Todo  
Prioritas: P0  
Branch: `feature/auth-role`

Docs yang wajib dibaca:

- `docs/02_Analisis/Aktor Sistem.md`
- `docs/02_Analisis/Kebutuhan Sistem.md`

Acceptance Criteria:

- [ ] Role admin/guru/murid tersedia.
- [ ] Dashboard redirect berdasarkan role.
- [ ] Route terproteksi.
- [ ] Test role redirect tersedia.

### TASK-003 - Dashboard Role

Status: Todo  
Prioritas: P0  
Branch: `feature/role-dashboard`

Acceptance Criteria:

- [ ] Admin dashboard render.
- [ ] Guru dashboard render.
- [ ] Murid dashboard render.
- [ ] Navigasi dasar sesuai role.

### TASK-004 - CRUD Modul Pembelajaran

Status: Todo  
Prioritas: P1  
Branch: `feature/module-management`

Docs:

- `docs/03_Desain/Database Design.md`
- `docs/04_Development/Livewire Components.md`

Acceptance Criteria:

- [ ] Migration modules.
- [ ] Model Module.
- [ ] CRUD guru.
- [ ] Validasi form.
- [ ] Test create/update.

### TASK-005 - CRUD Kegiatan Belajar

Status: Todo  
Prioritas: P1  
Branch: `feature/learning-units`

Acceptance Criteria:

- [ ] Migration learning_units.
- [ ] Relasi module-learning_units.
- [ ] CRUD kegiatan belajar.
- [ ] Sorting sederhana.

### TASK-006 - Aktivitas E-LKM

Status: Todo  
Prioritas: P2  
Branch: `feature/learning-activities`

Acceptance Criteria:

- [ ] Migration activities.
- [ ] Migration activity_answers.
- [ ] Guru dapat membuat aktivitas.
- [ ] Murid dapat submit jawaban.
- [ ] Status aktivitas tersimpan.

### TASK-007 - Assessment Structure

Status: Todo  
Prioritas: P3  
Branch: `feature/assessment-structure`

Acceptance Criteria:

- [ ] Migration assessments.
- [ ] Migration questions.
- [ ] Migration assessment_attempts.
- [ ] Migration student_answers.
- [ ] Relasi model benar.

### TASK-008 - Scoring Service Pilihan Ganda

Status: Todo  
Prioritas: P3  
Branch: `feature/scoring-mcq`

Acceptance Criteria:

- [ ] Service scoring tersedia.
- [ ] Pilihan ganda dikoreksi otomatis.
- [ ] Unit test tersedia.

### TASK-009 - Scoring Service Uraian Awal

Status: Todo  
Prioritas: P3  
Branch: `feature/scoring-essay-basic`

Acceptance Criteria:

- [ ] KeywordMatcher.
- [ ] TextSimilarityService.
- [ ] Formula skor uraian.
- [ ] Unit test tersedia.

### TASK-010 - Remedial Flow

Status: Todo  
Prioritas: P4  
Branch: `feature/remedial-flow`

Acceptance Criteria:

- [ ] KKTP diterapkan.
- [ ] Status passed/failed.
- [ ] Attempt limit.
- [ ] Rekomendasi ulang.

## In Progress

Belum ada.

## Done

Belum ada.

## Blocked

Belum ada.

## Template Task Baru

```markdown
### TASK-XXX - Judul

Status: Todo  
Prioritas: P0/P1/P2/P3/P4/P5  
Branch: `feature/name`

Docs:
-

Acceptance Criteria:

- [ ] 
- [ ] 
- [ ] 

Prompt:
```text

```
```
