---
type: development
status: draft
tags:
  - scoring
  - assessment
  - nlp
---

# Scoring Service

Dokumen ini merancang service penilaian otomatis.

## Tujuan

Scoring Service bertugas menghitung nilai jawaban murid secara otomatis untuk:

- Pilihan ganda biasa.
- Pilihan ganda kompleks.
- Benar/salah.
- Menjodohkan.
- Isian singkat.
- Uraian singkat.

Untuk uraian, scoring menggunakan kombinasi:

```text
Skor Akhir = 40% Rubric Score + 30% Keyword Score + 30% Similarity Score
```

Komposisi dapat dibuat configurable pada tahap lanjutan.

## Lokasi File yang Disarankan

```text
app/Services/Assessments/
├── AssessmentScoringService.php
├── QuestionScoringService.php
├── KeywordMatcher.php
├── TextSimilarityService.php
└── RubricScoringService.php
```

## Tipe Soal

Gunakan constant atau enum:

```php
enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case ComplexChoice = 'complex_choice';
    case TrueFalse = 'true_false';
    case Matching = 'matching';
    case ShortAnswer = 'short_answer';
    case Essay = 'essay';
}
```

## Kontrak Service

### AssessmentScoringService

Tanggung jawab:

- Mengambil semua jawaban dalam attempt.
- Mengirim setiap jawaban ke QuestionScoringService.
- Menghitung total nilai.
- Menentukan status passed/failed berdasarkan KKTP.
- Menyimpan hasil.

Pseudo flow:

```text
scoreAttempt(attempt)
  answers = attempt.studentAnswers
  total = 0

  foreach answer:
    result = questionScoringService.score(answer.question, answer)
    update answer score and feedback
    total += result.score

  update attempt total_score
  update attempt status
```

### QuestionScoringService

Tanggung jawab:

- Mendeteksi question type.
- Memanggil method scoring sesuai type.

Method:

```text
scoreMultipleChoice()
scoreComplexChoice()
scoreTrueFalse()
scoreMatching()
scoreShortAnswer()
scoreEssay()
```

## Algoritma Per Tipe Soal

### 1. Pilihan Ganda Biasa

Aturan:

- Jawaban benar = skor penuh.
- Jawaban salah = 0.

Pseudo:

```text
if answer == correct_answer:
    score = weight
else:
    score = 0
```

### 2. Pilihan Ganda Kompleks

Aturan default:

- Skor parsial.
- Jawaban benar dipilih mendapat poin.
- Jawaban benar tidak dipilih mengurangi peluang skor.
- Jawaban salah dipilih dapat bernilai 0 untuk opsi tersebut.
- Skor minimum 0.

Contoh:

```text
correct = [A, C, D]
student = [A, D]

score = 2/3 * weight
```

Alternative dengan penalti:

```text
score = (correct_selected - wrong_selected) / total_correct * weight
minimum 0
```

Untuk tahap awal gunakan tanpa penalti agar lebih ramah murid.

### 3. Benar/Salah

Aturan:

- Tiap item benar mendapat skor parsial.
- Total skor = benar / jumlah item * bobot.

### 4. Menjodohkan

Aturan:

- Tiap pasangan benar mendapat skor parsial.
- Total skor = pasangan benar / jumlah pasangan * bobot.

### 5. Isian Singkat

Aturan:

- Normalize jawaban.
- Cocokkan dengan keyword.
- Skor berdasarkan keyword yang ditemukan.

Preprocessing:

```text
lowercase
trim
hapus tanda baca sederhana
normalisasi spasi
```

### 6. Uraian Singkat

Komponen:

| Komponen | Bobot |
|---|---|
| Rubric Score | 40% |
| Keyword Score | 30% |
| Similarity Score | 30% |

Formula:

```text
final_score = (rubric_score * 0.40) + (keyword_score * 0.30) + (similarity_score * 0.30)
```

## Keyword Matching

Tanggung jawab:

- Mencocokkan kata kunci pada jawaban murid.
- Mendukung sinonim sederhana tahap lanjutan.
- Menghasilkan skor 0-100.

Pseudo:

```text
matched_weight = sum(weight keyword ditemukan)
total_weight = sum(weight semua keyword)
keyword_score = matched_weight / total_weight * 100
```

## Similarity Score

Tahap awal:

- Tokenize teks jawaban murid.
- Tokenize jawaban acuan.
- Hitung Jaccard similarity.
- Output 0-100.

Formula:

```text
similarity = intersection(tokens) / union(tokens) * 100
```

Tahap lanjutan:

- Cosine similarity dengan TF-IDF.
- Embedding lokal atau layanan AI jika disetujui.

## Rubric Score

Karena rubrik kualitatif sulit dinilai penuh oleh sistem rule-based, tahap awal:

- Rubric score default dihitung dari keyword dan struktur jawaban.
- Guru bisa override.
- Rubrik menjadi panduan feedback.

Tahap lanjutan:

- Rubric criterion dapat diberi keyword masing-masing.
- Sistem memberi skor per criterion.

## Data yang Disimpan

Pada `student_answers`:

- `score`
- `feedback`
- `keyword_score`
- `similarity_score`
- `rubric_score`

Ini penting agar guru bisa melihat kenapa sistem memberi nilai tertentu.

## Feedback Otomatis

Contoh feedback:

- "Jawaban sudah memuat konsep utama, tetapi belum menjelaskan dampak lingkungan."
- "Beberapa kata kunci penting belum muncul: emisi, energi fosil, transisi energi."
- "Jawaban sangat mirip dengan jawaban acuan."

## Test yang Wajib Ada

Unit test:

- [ ] Multiple choice benar.
- [ ] Multiple choice salah.
- [ ] Complex choice parsial.
- [ ] True/false parsial.
- [ ] Matching parsial.
- [ ] Keyword matching.
- [ ] Similarity score.
- [ ] Essay final score.
- [ ] Attempt pass/fail berdasarkan KKTP.

## Risiko

- Similarity sederhana bisa tidak memahami makna semantik.
- Keyword matching bisa bias terhadap hafalan istilah.
- Jawaban benar dengan bahasa berbeda bisa mendapat skor rendah.
- Guru tetap perlu review untuk soal uraian penting.

## Keputusan Tahap Awal

- Gunakan rule-based scoring.
- Jangan gunakan AI generatif penuh.
- Simpan skor komponen agar transparan.
- Buat service terpisah agar mudah diganti di masa depan.
