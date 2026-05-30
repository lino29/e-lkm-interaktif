# Panduan Pengembangan Semua Fitur Aktivitas Interaktif E-LKM

## 1. Tujuan Sprint Pengembangan

Sprint ini bertujuan menyelesaikan fitur **Aktivitas Interaktif E-LKM** agar seluruh aktivitas pada file Format Bahan Ajar dapat berjalan secara teknis di aplikasi Laravel Livewire.

Fitur yang harus selesai:

1. Ayo Mengamati
2. Ayo Bertanya
3. Ayo Mencoba
4. Ayo Menalar
5. Ayo Menyimpulkan
6. Forum Diskusi/Refleksi
7. Asesmen Formatif per Kegiatan Belajar
8. Progress dan penguncian alur belajar
9. Remedial otomatis berdasarkan KKTP
10. Proyek murid untuk Kegiatan Belajar 5

Target akhir:

```text
Murid membuka Kegiatan Belajar
→ membaca tujuan dan materi
→ mengerjakan Ayo Mengamati
→ mengerjakan Ayo Bertanya
→ mengerjakan Ayo Mencoba
→ mengerjakan Ayo Menalar
→ mengerjakan Ayo Menyimpulkan
→ mengikuti Forum Diskusi/Refleksi
→ mengerjakan Asesmen Formatif
→ sistem menghitung nilai
→ jika tuntas, lanjut kegiatan berikutnya
→ jika belum tuntas, masuk remedial
```

---

## 2. Prinsip Teknis Pengembangan

Pengembangan tidak boleh membuat halaman statis terpisah untuk setiap aktivitas. Sistem harus memakai pendekatan **activity engine berbasis schema**.

Artinya, satu komponen `ActivityPage` harus bisa merender beberapa jenis input:

```text
short_text
essay
table
file
discussion
project_form
```

Dengan pendekatan ini, guru/admin bisa membuat aktivitas berbeda tanpa membuat komponen baru setiap kali.

---

## 3. Struktur Data Utama yang Digunakan

Gunakan struktur tabel yang sudah ada di project:

```text
modules
learning_units
materials
media
activities
activity_answers
discussions
assessments
questions
question_keywords
rubrics
assessment_attempts
student_answers
progress
projects
```

Mapping teknis:

| Komponen Format Bahan Ajar | Tabel                             |
| -------------------------- | --------------------------------- |
| Kegiatan Belajar 1–5       | learning_units                    |
| Tujuan Pembelajaran        | learning_units.learning_objective |
| Pokok-Pokok Materi         | materials                         |
| Uraian Materi              | materials                         |
| Gambar/video/simulasi      | media                             |
| Ayo Mengamati              | activities                        |
| Ayo Bertanya               | activities                        |
| Ayo Mencoba                | activities                        |
| Ayo Menalar                | activities                        |
| Ayo Menyimpulkan           | activities                        |
| Forum Diskusi/Refleksi     | activities + discussions          |
| Asesmen Formatif           | assessments + questions           |
| Jawaban aktivitas          | activity_answers                  |
| Jawaban asesmen            | student_answers                   |
| Progress belajar           | progress                          |
| Proyek murid               | projects                          |

---

## 4. Standar Field pada Tabel Activities

Pastikan tabel `activities` memiliki field berikut:

```text
id
learning_unit_id
title
phase
prompt
input_type
is_required
order
answer_schema
display_config
validation_rules
sample_answer
rubric_schema
requires_teacher_review
created_at
updated_at
```

Jika `rubric_schema` belum ada, tambahkan migration:

```bash
php artisan make:migration add_rubric_schema_to_activities_table
```

Isi migration:

```php
Schema::table('activities', function (Blueprint $table) {
    $table->json('rubric_schema')->nullable()->after('sample_answer');
});
```

Lalu jalankan:

```bash
php artisan migrate
```

Alasan teknis:

`rubric_schema` dibutuhkan agar aktivitas seperti Ayo Menalar dan Ayo Menyimpulkan dapat dinilai tanpa harus selalu masuk ke tabel `questions`.

---

## 5. Standar Enum Phase Aktivitas

Gunakan phase berikut secara konsisten:

```php
[
    'ayo_mengamati',
    'ayo_bertanya',
    'ayo_mencoba',
    'ayo_menalar',
    'ayo_menyimpulkan',
    'forum_diskusi'
]
```

Jangan membuat phase baru seperti `observasi`, `refleksi`, atau `diskusi_umum` karena akan membuat alur tidak konsisten dengan file Format.

---

## 6. Standar Input Type

Gunakan input type berikut:

```php
[
    'short_text',
    'essay',
    'table',
    'file',
    'discussion',
    'project_form'
]
```

Keterangan:

| Input Type   | Fungsi                    |
| ------------ | ------------------------- |
| short_text   | Jawaban singkat           |
| essay        | Jawaban uraian            |
| table        | Tabel observasi/percobaan |
| file         | Upload dokumen/foto/video |
| discussion   | Forum/refleksi            |
| project_form | Rancangan proyek KB5      |

---

## 7. Pengembangan ActivityTemplateService

File prioritas:

```text
app/Services/Learning/ActivityTemplateService.php
```

Service ini harus dikembangkan agar tidak hanya memberi template umum, tetapi juga mendukung template berdasarkan:

```text
phase
learning_unit_order
activity_context
```

Buat method baru:

```php
public function templateFor(string $phase, ?int $learningUnitOrder = null): array
```

Struktur logic:

```php
return match (true) {
    $phase === 'ayo_mengamati' && $learningUnitOrder === 1 => $this->kb1AyoMengamati(),
    $phase === 'ayo_mencoba' && $learningUnitOrder === 1 => $this->kb1AyoMencoba(),
    $phase === 'ayo_mencoba' && $learningUnitOrder === 2 => $this->kb2AyoMencoba(),
    $phase === 'ayo_mencoba' && $learningUnitOrder === 3 => $this->kb3AyoMencoba(),
    $phase === 'ayo_mencoba' && $learningUnitOrder === 4 => $this->kb4AyoMencoba(),
    $phase === 'ayo_mencoba' && $learningUnitOrder === 5 => $this->kb5AyoMencoba(),
    default => $this->defaultTemplate($phase),
};
```

---

# 8. Detail Fitur per Aktivitas

## 8.1 Ayo Mengamati

### Tujuan

Murid melakukan pengamatan terhadap media atau lingkungan nyata.

Contoh sesuai Format:

```text
KB1: Mengamati penggunaan energi di kelas/sekolah selama 15 menit.
KB2: Mengamati kendaraan yang masuk ke sekolah selama 10 menit.
KB3: Mengamati video panel surya, PLTA, turbin angin, biogas, dan briket biomassa.
KB4: Menonton video/simulasi cara kerja panel surya atau kompor surya.
KB5: Mengamati masalah energi di sekolah.
```

### Backend

Gunakan:

```text
phase: ayo_mengamati
input_type: essay atau table
is_required: true
requires_teacher_review: false
```

### Schema KB1

```json
{
  "columns": [
    {
      "name": "objek_pengamatan",
      "label": "Objek/Alat yang Diamati",
      "type": "text",
      "required": true
    },
    {
      "name": "lokasi",
      "label": "Lokasi",
      "type": "text",
      "required": true
    },
    {
      "name": "fungsi",
      "label": "Fungsi Alat",
      "type": "text",
      "required": true
    },
    {
      "name": "catatan",
      "label": "Catatan Pengamatan",
      "type": "textarea",
      "required": false
    }
  ],
  "min_rows": 5,
  "allow_add": true
}
```

### Schema KB2

```json
{
  "columns": [
    {
      "name": "jenis_kendaraan",
      "label": "Jenis Kendaraan",
      "type": "text",
      "required": true
    },
    {
      "name": "jumlah",
      "label": "Jumlah",
      "type": "number",
      "required": true
    },
    {
      "name": "kategori",
      "label": "Kategori",
      "type": "select",
      "options": [
        "Berbahan bakar fosil",
        "Tanpa bahan bakar fosil"
      ],
      "required": true
    },
    {
      "name": "catatan",
      "label": "Catatan",
      "type": "textarea",
      "required": false
    }
  ],
  "min_rows": 3,
  "allow_add": true
}
```

### UI Murid

Tampilan harus berisi:

```text
Judul aktivitas
Instruksi aktivitas
Media pengamatan jika ada
Form jawaban
Tombol Simpan Draft
Tombol Kirim Aktivitas
Status jawaban
```

### Acceptance Criteria

```text
Murid dapat membuka Ayo Mengamati.
Murid dapat melihat instruksi dan media.
Murid dapat mengisi jawaban.
Murid dapat menyimpan jawaban.
Jawaban masuk ke activity_answers.
Progress unit ter-refresh.
```

---

## 8.2 Ayo Bertanya

### Tujuan

Murid menjawab pertanyaan pemantik.

Contoh sesuai Format:

```text
KB1: Jika sekolah ingin menghemat listrik, sumber energi alternatif apa yang dapat dimanfaatkan?
KB2: Apa akibatnya jika masyarakat terus bergantung pada bahan bakar fosil?
KB3: Mengapa daerah yang berbeda membutuhkan jenis energi terbarukan yang berbeda?
KB4: Mengapa warna hitam lebih cepat menyerap panas dibandingkan warna putih?
KB5: Masalah energi apa yang paling dekat dengan kehidupan kalian?
```

### Backend

```text
phase: ayo_bertanya
input_type: short_text atau essay
is_required: true
requires_teacher_review: false
```

### Validation Rules

```json
{
  "required": true,
  "min_words": 10,
  "max_words": 100
}
```

### UI Murid

```text
Pertanyaan pemantik
Input jawaban singkat
Indikator jumlah kata
Tombol kirim
```

### Acceptance Criteria

```text
Murid tidak bisa mengirim jawaban kosong.
Murid mendapat validasi minimal kata.
Jawaban tersimpan di answer_text.
Sistem menandai aktivitas selesai setelah submitted_at terisi.
```

---

## 8.3 Ayo Mencoba

### Tujuan

Murid mengisi lembar kerja digital, tabel observasi, tabel percobaan, peta masalah, atau rancangan aksi.

Fitur ini adalah bagian paling penting dan wajib dikembangkan sebagai **dynamic schema renderer**.

---

### 8.3.1 Ayo Mencoba KB1 — Tabel Energi

Instruksi sesuai Format:

```text
Murid mengamati alat di rumah, kelas, bengkel, atau laboratorium.
Murid mengisi 10 alat berbeda.
Kolom: Alat, Bentuk Energi Masuk, Bentuk Energi Keluar, Sumber Energi.
```

Schema:

```json
{
  "columns": [
    {
      "name": "alat",
      "label": "Alat",
      "type": "text",
      "required": true
    },
    {
      "name": "energi_masuk",
      "label": "Bentuk Energi Masuk",
      "type": "select",
      "options": [
        "Energi listrik",
        "Energi kimia",
        "Energi panas",
        "Energi gerak",
        "Energi cahaya"
      ],
      "required": true
    },
    {
      "name": "energi_keluar",
      "label": "Bentuk Energi Keluar",
      "type": "text",
      "required": true
    },
    {
      "name": "sumber_energi",
      "label": "Sumber Energi",
      "type": "select",
      "options": [
        "PLN",
        "Baterai",
        "Aki",
        "Matahari",
        "Bahan bakar",
        "Manusia"
      ],
      "required": true
    }
  ],
  "min_rows": 10,
  "allow_add": true,
  "allow_delete": true
}
```

---

### 8.3.2 Ayo Mencoba KB2 — Peta Masalah Energi Fosil

Instruksi sesuai Format:

```text
Energi fosil → pembakaran → emisi → dampak lingkungan, kesehatan, ekonomi → kebutuhan solusi.
```

Schema:

```json
{
  "fields": [
    {
      "name": "sumber_energi_fosil",
      "label": "Sumber Energi Fosil",
      "type": "text",
      "required": true
    },
    {
      "name": "proses_pembakaran",
      "label": "Proses Pembakaran/Penggunaan",
      "type": "textarea",
      "required": true
    },
    {
      "name": "emisi",
      "label": "Emisi yang Dihasilkan",
      "type": "text",
      "required": true
    },
    {
      "name": "dampak_lingkungan",
      "label": "Dampak Lingkungan",
      "type": "textarea",
      "required": true
    },
    {
      "name": "dampak_kesehatan",
      "label": "Dampak Kesehatan",
      "type": "textarea",
      "required": true
    },
    {
      "name": "dampak_ekonomi",
      "label": "Dampak Ekonomi",
      "type": "textarea",
      "required": true
    },
    {
      "name": "solusi",
      "label": "Kebutuhan Solusi",
      "type": "textarea",
      "required": true
    }
  ]
}
```

Renderer harus mendukung `fields` selain `columns`.

---

### 8.3.3 Ayo Mencoba KB3 — Tabel Kecocokan Energi Terbarukan

Instruksi sesuai Format:

```text
Murid melengkapi tabel kondisi lingkungan, energi terbarukan yang cocok, dan alasan.
```

Schema:

```json
{
  "columns": [
    {
      "name": "kondisi_lingkungan",
      "label": "Kondisi Lingkungan",
      "type": "readonly_text",
      "required": true
    },
    {
      "name": "energi_cocok",
      "label": "Energi Terbarukan yang Cocok",
      "type": "select",
      "options": [
        "Energi surya",
        "Energi biomassa/biogas",
        "Energi air/mikrohidro",
        "Energi angin"
      ],
      "required": true
    },
    {
      "name": "alasan",
      "label": "Alasan",
      "type": "textarea",
      "required": true
    }
  ],
  "preset_rows": [
    {
      "kondisi_lingkungan": "Banyak sinar matahari"
    },
    {
      "kondisi_lingkungan": "Banyak limbah organik"
    },
    {
      "kondisi_lingkungan": "Dekat sungai deras"
    },
    {
      "kondisi_lingkungan": "Daerah pesisir berangin"
    }
  ],
  "allow_add": false,
  "allow_delete": false
}
```

---

### 8.3.4 Ayo Mencoba KB4 — Percobaan Warna dan Penyerapan Panas

Instruksi sesuai Format:

```text
Murid melakukan percobaan dua gelas: kertas hitam dan kertas putih.
Murid mengukur suhu awal dan suhu akhir.
Sistem menghitung perubahan suhu.
```

Schema:

```json
{
  "columns": [
    {
      "name": "media",
      "label": "Media/Warna",
      "type": "readonly_text",
      "required": true
    },
    {
      "name": "suhu_awal",
      "label": "Suhu Awal (°C)",
      "type": "number",
      "required": true
    },
    {
      "name": "suhu_akhir",
      "label": "Suhu Akhir (°C)",
      "type": "number",
      "required": true
    },
    {
      "name": "perubahan_suhu",
      "label": "Perubahan Suhu (°C)",
      "type": "computed",
      "formula": "suhu_akhir - suhu_awal"
    },
    {
      "name": "catatan",
      "label": "Catatan Pengamatan",
      "type": "textarea",
      "required": false
    }
  ],
  "preset_rows": [
    {
      "media": "Gelas dibungkus kertas hitam"
    },
    {
      "media": "Gelas dibungkus kertas putih"
    }
  ],
  "allow_add": false,
  "allow_delete": false
}
```

Renderer harus menghitung `computed` field di frontend Livewire atau backend saat submit.

---

### 8.3.5 Ayo Mencoba KB5 — Rancangan Aksi Sederhana

Kegiatan Belajar 5 harus diarahkan ke sistem proyek.

Schema:

```json
{
  "fields": [
    {
      "name": "project_type",
      "label": "Pilihan Proyek",
      "type": "select",
      "options": [
        "Kompor surya mini",
        "Briket biomassa",
        "Audit energi kelas",
        "Kampanye hemat energi",
        "Lampu taman surya mini"
      ],
      "required": true
    },
    {
      "name": "problem",
      "label": "Masalah Energi yang Ditemukan",
      "type": "textarea",
      "required": true
    },
    {
      "name": "objective",
      "label": "Tujuan Aksi",
      "type": "textarea",
      "required": true
    },
    {
      "name": "tools_materials",
      "label": "Alat dan Bahan",
      "type": "textarea",
      "required": true
    },
    {
      "name": "procedure",
      "label": "Langkah Kerja",
      "type": "textarea",
      "required": true
    },
    {
      "name": "data_to_collect",
      "label": "Data yang Akan Dikumpulkan",
      "type": "textarea",
      "required": true
    },
    {
      "name": "expected_result",
      "label": "Hasil yang Diharapkan",
      "type": "textarea",
      "required": true
    }
  ]
}
```

Saat murid submit KB5 Ayo Mencoba, sistem harus membuat atau memperbarui record di tabel `projects`.

---

### Acceptance Criteria Ayo Mencoba

```text
Renderer mendukung columns.
Renderer mendukung fields.
Renderer mendukung preset_rows.
Renderer mendukung select.
Renderer mendukung textarea.
Renderer mendukung readonly_text.
Renderer mendukung number.
Renderer mendukung computed.
Renderer bisa menyimpan answer_json.
Validasi required berjalan.
Progress unit ter-refresh setelah submit.
```

---

## 8.4 Ayo Menalar

### Tujuan

Murid menjawab soal penalaran berbasis HOTS.

Contoh sesuai Format:

```text
KB1: Mengapa penggunaan energi listrik harus dihemat walaupun listrik tidak terlihat langsung menghasilkan asap di ruang kelas?
KB2: Mengapa transisi energi tidak dapat dilakukan secara tiba-tiba, tetapi harus bertahap?
KB3: Jika sekolah ingin memasang panel surya, faktor apa saja yang perlu dipertimbangkan?
KB4: Mengapa prinsip penyerapan panas penting dalam desain kompor surya?
KB5: Apakah proyek kalian realistis dilakukan oleh siswa SMK dengan alat sederhana? Apa risiko keselamatan yang perlu diperhatikan?
```

### Backend

```text
phase: ayo_menalar
input_type: essay
is_required: true
requires_teacher_review: true
```

### Rubric Schema

```json
{
  "criteria": [
    {
      "name": "ketepatan_konsep",
      "label": "Ketepatan Konsep",
      "max_score": 40,
      "keywords": []
    },
    {
      "name": "argumentasi",
      "label": "Kualitas Argumentasi",
      "max_score": 30,
      "keywords": []
    },
    {
      "name": "kontekstual",
      "label": "Kesesuaian dengan Konteks Sekolah/Lingkungan",
      "max_score": 20,
      "keywords": []
    },
    {
      "name": "bahasa",
      "label": "Kejelasan Bahasa",
      "max_score": 10,
      "keywords": []
    }
  ],
  "auto_score": true
}
```

### Scoring

Buat service:

```text
app/Services/Learning/ActivityScoringService.php
```

Method:

```php
public function score(Activity $activity, ActivityAnswer $answer): array
```

Output:

```php
[
    'score' => 85,
    'feedback' => 'Jawaban sudah memuat konsep utama, namun alasan dampak lingkungan perlu diperjelas.',
    'keyword_score' => 80,
    'length_score' => 90,
    'rubric_score' => 85,
]
```

### Acceptance Criteria

```text
Murid dapat mengirim jawaban uraian.
Sistem dapat menyimpan jawaban.
Jika rubric_schema tersedia, sistem menghitung skor awal.
Guru tetap dapat mereview jika requires_teacher_review = true.
```

---

## 8.5 Ayo Menyimpulkan

### Tujuan

Murid membuat kesimpulan akhir setelah seluruh aktivitas.

Contoh sesuai Format:

```text
KB1: Menyimpulkan hubungan energi, perubahan energi, dan sumber energi.
KB2: Menuliskan tiga alasan energi fosil perlu dikurangi.
KB3: Menentukan jenis energi terbarukan yang paling sesuai untuk sekolah.
KB4: Menyimpulkan berdasarkan data suhu.
KB5: Menyimpulkan manfaat aksi sederhana energi terbarukan.
```

### Backend

```text
phase: ayo_menyimpulkan
input_type: essay
is_required: true
requires_teacher_review: true
```

### Fitur Khusus

Pada halaman Ayo Menyimpulkan, sistem harus menampilkan ringkasan jawaban sebelumnya:

```text
Ringkasan Ayo Mengamati
Ringkasan Ayo Mencoba
Ringkasan Ayo Menalar
```

Tujuannya agar kesimpulan tidak lepas dari data aktivitas.

### Validation Rules

```json
{
  "required": true,
  "min_words": 30,
  "must_reference_previous_activity": true
}
```

### Acceptance Criteria

```text
Murid dapat melihat data aktivitas sebelumnya.
Murid dapat menulis kesimpulan.
Sistem menyimpan kesimpulan.
Sistem memberi status selesai.
Guru dapat mereview kesimpulan.
```

---

## 8.6 Forum Diskusi/Refleksi

### Tujuan

Murid menulis pendapat dan menanggapi teman.

Contoh sesuai Format:

```text
KB1: Aktivitas apa di sekolah yang paling banyak menggunakan energi?
KB2: Apakah sekolah dapat mengurangi penggunaan energi fosil?
KB3: Apakah briket biomassa dapat menjadi solusi energi alternatif?
KB4: Bagaimana teknologi sederhana membantu daerah sulit akses energi?
KB5: Bagaimana kelompok menyampaikan hasil proyek dan menerima masukan?
```

### Backend

Gunakan:

```text
phase: forum_diskusi
input_type: discussion
is_required: true
```

Saat murid submit, simpan ke:

```text
activity_answers
discussions
```

Aturan:

```text
Minimal 1 posting utama.
Minimal 1 balasan ke teman jika mode reply_required aktif.
Guru dapat memberi komentar.
Forum dihitung sebagai aktivitas wajib.
```

### Tambahan Field pada Activities

Untuk forum, gunakan `validation_rules`:

```json
{
  "required": true,
  "min_words": 25,
  "reply_required": true,
  "min_replies": 1
}
```

### Acceptance Criteria

```text
Murid dapat menulis refleksi.
Murid dapat membalas teman.
Guru dapat melihat semua diskusi.
Forum masuk ke progress kegiatan belajar.
Jika forum wajib belum dikerjakan, unit belum tuntas.
```

---

# 9. Pengembangan ActivityPage

File prioritas:

```text
app/Livewire/Murid/ActivityPage.php
resources/views/livewire/murid/activity-page.blade.php
```

## 9.1 Property yang Dibutuhkan

Tambahkan property:

```php
public ?Activity $currentActivity = null;
public string $answer_text = '';
public array $answer_json = [];
public ?string $answer_json_text = null;
public $file = null;
public array $previousAnswers = [];
```

## 9.2 Mount

Saat mount:

```php
public function mount(Activity $activity): void
{
    $this->currentActivity = Activity::with('learningUnit.module')
        ->whereHas('learningUnit.module', fn ($query) => $query->where('status', 'published'))
        ->findOrFail($activity->id);

    abort_unless(
        app(ProgressService::class)->isLearningUnitUnlocked(auth()->user(), $this->currentActivity->learningUnit),
        403
    );

    $this->loadExistingAnswer();
    $this->initializeSchemaAnswer();
    $this->loadPreviousAnswers();
}
```

## 9.3 Renderer Logic

Blade harus memilih renderer berdasarkan `input_type`:

```blade
@if ($activity->input_type === 'short_text')
    @include('livewire.murid.activities.inputs.short-text')
@elseif ($activity->input_type === 'essay')
    @include('livewire.murid.activities.inputs.essay')
@elseif ($activity->input_type === 'table')
    @include('livewire.murid.activities.inputs.table')
@elseif ($activity->input_type === 'file')
    @include('livewire.murid.activities.inputs.file')
@elseif ($activity->input_type === 'discussion')
    @include('livewire.murid.activities.inputs.discussion')
@elseif ($activity->input_type === 'project_form')
    @include('livewire.murid.activities.inputs.project-form')
@endif
```

Buat folder:

```text
resources/views/livewire/murid/activities/inputs/
├── short-text.blade.php
├── essay.blade.php
├── table.blade.php
├── file.blade.php
├── discussion.blade.php
└── project-form.blade.php
```

---

# 10. Dynamic Table Renderer

Renderer tabel wajib mendukung:

```text
text
textarea
number
select
readonly_text
computed
```

Pseudo logic:

```php
foreach ($schema['preset_rows'] ?? [] as $row) {
    $this->answer_json[] = $row;
}

if (! isset($schema['preset_rows'])) {
    for ($i = 0; $i < ($schema['min_rows'] ?? 1); $i++) {
        $this->answer_json[] = [];
    }
}
```

Untuk computed:

```php
if ($column['type'] === 'computed') {
    $row[$column['name']] = $this->calculateFormula($column['formula'], $row);
}
```

Minimal formula yang harus didukung:

```text
suhu_akhir - suhu_awal
```

---

# 11. Validasi Answer Schema

Buat service:

```text
app/Services/Learning/ActivitySchemaValidator.php
```

Method:

```php
public function validate(Activity $activity, array|string|null $answerJson, ?string $answerText): array
```

Rules:

```text
Jika validation_rules.required = true, jawaban tidak boleh kosong.
Jika input_type table, jumlah baris minimal harus sesuai min_rows.
Jika column required, nilai kolom tidak boleh kosong.
Jika field required, nilai field tidak boleh kosong.
Jika min_words tersedia, hitung jumlah kata answer_text.
Jika max_words tersedia, batasi jumlah kata.
```

Return:

```php
[
    'valid' => true,
    'errors' => []
]
```

Jika invalid, tampilkan error di UI.

---

# 12. Activity Answer Submit Flow

Alur submit:

```text
Murid klik submit
→ validasi form umum
→ validasi schema
→ upload file jika ada
→ hitung computed field
→ simpan ke activity_answers
→ jika forum, buat discussions
→ jika project_form, buat/update projects
→ jika rubric_schema tersedia, hitung skor aktivitas
→ refresh progress
→ redirect atau tampilkan status berhasil
```

Pseudo code:

```php
public function submit(): void
{
    $this->validateBaseInput();

    $schemaValidation = app(ActivitySchemaValidator::class)
        ->validate($this->currentActivity, $this->answer_json, $this->answer_text);

    if (! $schemaValidation['valid']) {
        $this->addError('activity', implode("\n", $schemaValidation['errors']));
        return;
    }

    $answer = app(ActivityAnswerService::class)->save(
        activity: $this->currentActivity,
        user: auth()->user(),
        answerText: $this->answer_text,
        answerJson: $this->answer_json,
        file: $this->file
    );

    if ($this->currentActivity->phase === 'forum_diskusi') {
        app(ActivityDiscussionService::class)->sync($answer);
    }

    if ($this->currentActivity->input_type === 'project_form') {
        app(ProjectDraftService::class)->syncFromActivityAnswer($answer);
    }

    if ($this->currentActivity->rubric_schema) {
        app(ActivityScoringService::class)->score($this->currentActivity, $answer);
    }

    app(ProgressService::class)->refreshLearningUnitProgress(auth()->user(), $this->currentActivity->learningUnit);

    session()->flash('status', 'Jawaban aktivitas berhasil disimpan.');
}
```

---

# 13. Services yang Harus Dibuat

Buat services berikut:

```text
app/Services/Learning/
├── ActivityTemplateService.php
├── ActivitySchemaValidator.php
├── ActivityAnswerService.php
├── ActivityScoringService.php
├── ActivityDiscussionService.php
├── ProjectDraftService.php
└── ProgressService.php
```

Fungsi masing-masing:

| Service                   | Fungsi                                             |
| ------------------------- | -------------------------------------------------- |
| ActivityTemplateService   | Template default setiap phase dan kegiatan belajar |
| ActivitySchemaValidator   | Validasi schema jawaban                            |
| ActivityAnswerService     | Simpan/update jawaban aktivitas                    |
| ActivityScoringService    | Hitung skor aktivitas uraian/refleksi              |
| ActivityDiscussionService | Sinkronisasi jawaban forum ke discussions          |
| ProjectDraftService       | Sinkronisasi aktivitas KB5 ke projects             |
| ProgressService           | Refresh status belajar                             |

---

# 14. Pengembangan ManageActivities Guru

File prioritas:

```text
app/Livewire/Guru/ManageActivities.php
resources/views/livewire/guru/manage-activities.blade.php
```

Fitur yang harus ada:

```text
Guru memilih kegiatan belajar.
Guru memilih phase aktivitas.
Sistem mengisi template otomatis.
Guru dapat mengedit judul.
Guru dapat mengedit instruksi.
Guru dapat memilih input_type.
Guru dapat menentukan wajib/tidak.
Guru dapat mengatur urutan.
Guru dapat melihat preview schema.
Guru dapat menyimpan aktivitas.
Guru dapat mengedit aktivitas.
Guru dapat menghapus aktivitas.
```

Tambahkan tombol:

```text
Gunakan Template Sesuai Format
Preview Aktivitas
Reset ke Template Default
```

Saat guru memilih phase dan learning unit, sistem memanggil:

```php
$template = app(ActivityTemplateService::class)
    ->templateFor($this->phase, $learningUnit->order);
```

---

# 15. Seeder Konten Aktivitas Sesuai Format

Buat seeder:

```bash
php artisan make:seeder RenewableEnergyActivitySeeder
```

Seeder harus membuat:

```text
KB1:
- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba
- Ayo Menalar
- Ayo Menyimpulkan
- Forum Diskusi/Refleksi

KB2:
- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba
- Ayo Menalar
- Ayo Menyimpulkan
- Forum Diskusi/Refleksi

KB3:
- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba
- Ayo Menalar
- Ayo Menyimpulkan
- Forum Diskusi/Refleksi

KB4:
- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba
- Ayo Menalar
- Ayo Menyimpulkan
- Forum Diskusi/Refleksi

KB5:
- Ayo Mengamati
- Ayo Bertanya
- Ayo Mencoba/Rancangan Proyek
- Ayo Menalar
- Ayo Menyimpulkan
- Forum Diskusi/Refleksi
```

Total minimal:

```text
5 kegiatan belajar × 6 aktivitas = 30 aktivitas
```

Semua aktivitas harus:

```text
is_required = true
order = sesuai urutan
```

Urutan:

```text
1. Ayo Mengamati
2. Ayo Bertanya
3. Ayo Mencoba
4. Ayo Menalar
5. Ayo Menyimpulkan
6. Forum Diskusi/Refleksi
```

---

# 16. Asesmen Formatif per Kegiatan Belajar

Setiap Kegiatan Belajar harus memiliki asesmen formatif.

Jenis soal yang wajib didukung:

```text
pilihan_ganda
pilihan_ganda_kompleks
benar_salah
isian
uraian
menjodohkan
```

Minimal per KB:

```text
5 Pilihan Ganda Biasa
5 Pilihan Ganda Kompleks
5 Benar/Salah
5 Isian/Uraian Singkat
5 Menjodohkan
```

Untuk MVP, boleh mulai dengan:

```text
2 Pilihan Ganda Biasa
2 Pilihan Ganda Kompleks
2 Benar/Salah
2 Isian/Uraian Singkat
2 Menjodohkan
```

Tetapi engine harus siap untuk jumlah penuh.

---

## 16.1 Scoring Pilihan Ganda Biasa

```php
if ($studentAnswer === $correctAnswer) {
    $score = $question->weight;
} else {
    $score = 0;
}
```

---

## 16.2 Scoring Pilihan Ganda Kompleks

Gunakan partial scoring:

```text
Skor = jumlah opsi benar yang dipilih / total opsi benar × bobot
```

Jika murid memilih opsi salah:

```text
Kurangi skor sebesar penalti.
Skor minimal 0.
```

Formula:

```php
$score = max(0, (($correctSelected / $totalCorrect) - ($wrongSelected / $totalOptions)) * $question->weight);
```

---

## 16.3 Scoring Benar/Salah

```php
$score = $studentAnswer === $correctAnswer ? $question->weight : 0;
```

---

## 16.4 Scoring Menjodohkan

Jawaban disimpan sebagai pasangan JSON:

```json
[
  {
    "left": "Panel surya",
    "right": "Energi matahari"
  }
]
```

Formula:

```text
Skor = jumlah pasangan benar / total pasangan × bobot
```

---

## 16.5 Scoring Isian/Uraian

Gunakan:

```text
keyword matching
similarity score
rubric score
```

Formula:

```text
Skor akhir = 40% rubrik + 30% keyword + 30% similarity
```

---

# 17. Remedial dan Progress

Progress harus mengikuti aturan:

```text
Unit belum tuntas jika masih ada aktivitas wajib yang belum dikirim.
Unit belum tuntas jika asesmen formatif belum tuntas.
Unit tuntas jika semua aktivitas wajib selesai dan asesmen mencapai KKTP.
Unit remedial jika asesmen sudah dikerjakan tetapi nilai < KKTP.
Unit berikutnya terkunci jika unit sebelumnya belum tuntas.
```

Status progress:

```text
belum_mulai
sedang_dikerjakan
tuntas
remedial
```

Progress dihitung ulang setelah:

```text
submit aktivitas
submit forum
submit asesmen
submit remedial
review guru jika dibutuhkan
```

---

# 18. Kegiatan Belajar 5 sebagai Project Workflow

KB5 tidak boleh hanya berupa essay biasa. KB5 harus tersambung dengan tabel `projects`.

Field project:

```text
student_id
module_id
learning_unit_id
project_title
project_type
problem
objective
tools_materials
procedure
collected_data
expected_result
conclusion
file_path
score
feedback
status
submitted_at
reviewed_at
```

Status:

```text
draft
submitted
reviewed
revision
approved
```

Alur:

```text
Murid mengisi rancangan proyek di Ayo Mencoba KB5
→ sistem membuat projects.status = draft
→ murid mengunggah bukti proyek
→ murid submit proyek
→ guru menilai proyek
→ jika perlu revisi, status = revision
→ jika diterima, status = approved
```

---

# 19. Testing Wajib

Gunakan Pest/PHPUnit.

Buat test:

```text
tests/Feature/ActivityFlowTest.php
tests/Feature/ActivitySchemaValidatorTest.php
tests/Feature/ProgressLockingTest.php
tests/Feature/DiscussionActivityTest.php
tests/Feature/ProjectActivityTest.php
tests/Feature/AssessmentScoringTest.php
```

## 19.1 Test Activity Schema

```text
KB1 Ayo Mencoba gagal jika kurang dari 10 baris.
KB1 Ayo Mencoba gagal jika kolom alat kosong.
KB3 Ayo Mencoba tidak boleh tambah baris jika allow_add false.
KB4 computed perubahan_suhu benar.
KB5 project_form membuat project draft.
```

## 19.2 Test Progress

```text
Unit 2 terkunci jika Unit 1 belum tuntas.
Unit tuntas jika semua aktivitas wajib selesai.
Unit tuntas jika asesmen formatif mencapai KKTP.
Unit remedial jika asesmen di bawah KKTP.
```

## 19.3 Test Forum

```text
Forum activity membuat record activity_answers.
Forum activity membuat record discussions.
Reply forum tersimpan dengan parent_id.
```

## 19.4 Test Assessment

```text
Pilihan ganda benar mendapat skor penuh.
Pilihan ganda salah mendapat skor 0.
Pilihan ganda kompleks mendapat skor parsial.
Benar/salah dikoreksi otomatis.
Menjodohkan dikoreksi berdasarkan pasangan.
Isian membaca keyword.
Uraian menghasilkan skor kombinasi.
```

---

# 20. Checklist Definition of Done

Fitur Aktivitas Interaktif dianggap selesai jika:

```text
[ ] 30 aktivitas dari 5 kegiatan belajar berhasil dibuat.
[ ] Semua phase aktivitas tersedia.
[ ] Semua input_type berjalan.
[ ] Dynamic schema renderer berjalan.
[ ] answer_json tersimpan dengan benar.
[ ] answer_text tersimpan dengan benar.
[ ] File upload tersimpan ke storage public.
[ ] Forum tersimpan ke discussions.
[ ] KB5 tersambung ke projects.
[ ] Progress terkunci sesuai urutan kegiatan belajar.
[ ] Asesmen formatif per kegiatan belajar berjalan.
[ ] Nilai asesmen dihitung otomatis.
[ ] Remedial aktif jika nilai kurang dari KKTP.
[ ] Guru bisa mengelola aktivitas.
[ ] Murid bisa mengerjakan aktivitas.
[ ] Guru bisa melihat jawaban murid.
[ ] Test utama berhasil.
[ ] Tidak ada logic besar langsung di Blade.
[ ] Tidak ada credential masuk repository.
```

---

# 21. Urutan Kerja Coding yang Disarankan

```text
1. Audit ulang model dan migration activities.
2. Tambahkan rubric_schema jika belum ada.
3. Upgrade ActivityTemplateService.
4. Buat ActivitySchemaValidator.
5. Buat ActivityAnswerService.
6. Upgrade ActivityPage.
7. Pecah input renderer menjadi partial Blade.
8. Tambahkan support table columns dan fields.
9. Tambahkan support preset_rows.
10. Tambahkan support select, number, readonly_text, computed.
11. Integrasikan forum_diskusi ke discussions.
12. Integrasikan project_form ke projects.
13. Upgrade ManageActivities guru.
14. Buat RenewableEnergyActivitySeeder.
15. Buat asesmen formatif per learning unit.
16. Buat AssessmentScoringService jika belum lengkap.
17. Sambungkan assessment ke ProgressService.
18. Buat test.
19. Jalankan migration dan seeder.
20. Jalankan test dan validasi manual.
```

---

# 22. Perintah Validasi Setelah Coding

Jalankan:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed
php artisan test
./vendor/bin/pint
npm run build
```

Untuk development lokal:

```bash
composer run dev
```

---

# 23. Output yang Harus Dilaporkan Setelah Implementasi

Setelah coding selesai, developer atau AI agent wajib melaporkan:

```text
1. File yang diubah.
2. Migration baru yang dibuat.
3. Service baru yang dibuat.
4. Komponen Livewire yang diubah.
5. Blade partial baru yang dibuat.
6. Seeder yang dibuat.
7. Fitur yang sudah berhasil.
8. Fitur yang belum berhasil.
9. Risiko teknis.
10. Test yang sudah dijalankan.
11. Screenshot halaman guru dan murid jika tersedia.
```

---

# 24. Prompt untuk AI Agent/Codex

Gunakan prompt berikut untuk melanjutkan pengembangan:

```text
Anda adalah senior Laravel developer untuk project E-LKM Interaktif.

Baca terlebih dahulu:
- AGENTS.md
- GOALS.md
- Panduan Pengembangan Sistem E-LKM Interaktif.md
- docs jika tersedia
- seluruh model, migration, service, Livewire component, route, dan Blade view terkait modul learning, activity, assessment, progress, discussion, dan project.

Tujuan tugas:
Kembangkan semua fitur Aktivitas Interaktif agar sesuai dengan Format Bahan Ajar E-LKM Energi Terbarukan.

Fitur wajib:
1. Ayo Mengamati
2. Ayo Bertanya
3. Ayo Mencoba
4. Ayo Menalar
5. Ayo Menyimpulkan
6. Forum Diskusi/Refleksi
7. Asesmen Formatif per Kegiatan Belajar
8. Progress dan penguncian unit
9. Remedial otomatis berdasarkan KKTP
10. Project workflow untuk Kegiatan Belajar 5

Ketentuan teknis:
- Jangan membuat halaman statis per aktivitas.
- Gunakan activity engine berbasis schema.
- Gunakan activities.answer_schema untuk table, fields, preset_rows, select, readonly_text, number, textarea, computed.
- Gunakan activity_answers.answer_text untuk jawaban teks.
- Gunakan activity_answers.answer_json untuk jawaban tabel/form schema.
- Gunakan discussions untuk forum.
- Gunakan projects untuk KB5.
- Gunakan ProgressService untuk status tuntas/remedial.
- Tambahkan service terpisah untuk validasi schema, penyimpanan jawaban, scoring aktivitas, diskusi, dan project.
- Hindari logic besar langsung di Blade atau Livewire.
- Buat atau perbaiki test.

Output akhir:
- Ringkasan file yang diubah.
- Ringkasan fitur yang selesai.
- Risiko teknis.
- Test yang dijalankan.
- Saran pengembangan lanjutan.
```
