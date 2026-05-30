---
type: design
status: draft
tags:
  - flow
  - murid
  - activity
---

# Flow User Murid

Dokumen ini menjelaskan alur utama murid dari login sampai menyelesaikan modul.

## Flow Utama

```mermaid
flowchart TD
    A([Mulai]) --> B[Login]
    B --> C{Credential valid?}
    C -- Tidak --> B
    C -- Ya --> D[Dashboard Murid]
    D --> E[Pilih Modul E-LKM]
    E --> F[Baca Pendahuluan dan Tujuan]
    F --> G[Pilih Kegiatan Belajar]
    G --> H[Ayo Mengamati]
    H --> I[Ayo Bertanya]
    I --> J[Baca Materi Inti]
    J --> K[Ayo Mencoba]
    K --> L[Ayo Menalar]
    L --> M[Ayo Menyimpulkan]
    M --> N[Forum Diskusi/Refleksi]
    N --> O[Asesmen Formatif]
    O --> P[Sistem Scoring]
    P --> Q{Nilai >= KKTP?}
    Q -- Ya --> R{Masih ada kegiatan?}
    R -- Ya --> G
    R -- Tidak --> S[Asesmen Akhir / Proyek]
    Q -- Tidak --> T[Remedial]
    T --> U[Pelajari Ulang Materi]
    U --> O
    S --> V[Portofolio dan Nilai]
    V --> W([Selesai])
```

## State Progress Murid

| Status | Arti |
|---|---|
| not_started | Murid belum membuka kegiatan |
| in_progress | Murid sedang mengerjakan kegiatan |
| completed | Murid selesai kegiatan dan tuntas |
| remedial | Murid belum memenuhi KKTP |
| locked | Kegiatan belum bisa diakses |

## Aturan Unlock Kegiatan Belajar

Aturan default:

```text
Kegiatan Belajar N+1 terbuka jika:
- Kegiatan Belajar N selesai
- Asesmen formatif N tuntas
```

Aturan opsional:

```text
Guru dapat mengatur apakah kegiatan terbuka bebas atau berurutan.
```

## Flow Aktivitas

```mermaid
flowchart TD
    A[Open Activity] --> B[Read Instruction]
    B --> C{Activity Type}
    C --> D[Mengamati: lihat media]
    C --> E[Bertanya: jawab pertanyaan]
    C --> F[Mencoba: isi tabel/form]
    C --> G[Menalar: jawab analisis]
    C --> H[Menyimpulkan: tulis kesimpulan]
    D --> I[Submit]
    E --> I
    F --> I
    G --> I
    H --> I
    I --> J[Save Answer]
    J --> K[Update Progress]
```

## Flow Asesmen

```mermaid
flowchart TD
    A[Open Assessment] --> B{Attempt masih tersedia?}
    B -- Tidak --> C[Tampilkan pesan attempt habis]
    B -- Ya --> D[Create/Resume Attempt]
    D --> E[Answer Questions]
    E --> F[Submit]
    F --> G[Validate Answers]
    G --> H[Calculate Score]
    H --> I[Store Student Answers]
    I --> J[Update Attempt Status]
    J --> K{Score >= KKTP?}
    K -- Ya --> L[Passed]
    K -- Tidak --> M[Failed/Remedial]
```

## UX Requirement untuk Murid

- Instruksi harus singkat dan jelas.
- Progress per kegiatan harus terlihat.
- Nilai dan feedback harus mudah dibaca.
- Tombol lanjut hanya aktif jika aturan terpenuhi.
- Status remedial harus menjelaskan apa yang perlu dipelajari ulang.

## Risiko Flow

- Murid bingung jika semua menu terbuka sekaligus.
- Murid bisa skip materi jika unlock tidak diatur.
- Murid bisa submit jawaban kosong jika validasi lemah.
- Murid bisa kehilangan jawaban jika tidak ada autosave atau draft.
