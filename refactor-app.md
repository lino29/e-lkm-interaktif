# Panduan Lengkap Refactor Aplikasi E-LKM agar Sesuai OITLINE E-LKM V2

## 1. Tujuan Refactor

Tujuan refactor ini adalah mengubah struktur aplikasi dari tampilan learning unit yang masih datar menjadi **outline pembelajaran bertingkat** sesuai dokumen OITLINE E-LKM V2.

Target akhir:

```text
Modul E-LKM Energi Terbarukan
├── Pendahuluan
│   ├── Prakata
│   ├── Daftar Isi
│   ├── Deskripsi Singkat
│   ├── Capaian Pembelajaran
│   ├── Tujuan Pembelajaran
│   ├── Relevansi
│   └── Petunjuk Belajar
├── Kegiatan Belajar 1
│   ├── 1. Tujuan Pembelajaran
│   ├── 2. Pokok-Pokok Materi
│   ├── 3. Uraian Materi
│   ├── 4. Aktivitas Pembelajaran
│   ├── 5. Forum Diskusi/Refleksi
│   └── 6. Asesmen Formatif
├── Kegiatan Belajar 2
│   ├── 1. Tujuan Pembelajaran
│   ├── 2. Pokok-Pokok Materi
│   ├── 3. Uraian Materi
│   ├── 4. Aktivitas Pembelajaran
│   ├── 5. Forum Diskusi/Refleksi
│   └── 6. Asesmen Formatif
├── Kegiatan Belajar 3
├── Kegiatan Belajar 4
├── Kegiatan Belajar 5
└── Penutup
    ├── Rangkuman
    ├── Daftar Istilah
    └── Daftar Pustaka
```

Refactor ini tidak boleh membuang fitur yang sudah ada. Model lama seperti `Module`, `LearningUnit`, `Material`, `Media`, `Activity`, `Assessment`, `Question`, `Discussion`, dan `Project` tetap dipakai. Yang perlu ditambahkan adalah **lapisan outline/submenu** agar konten tampil sesuai OITLINE.

---

## 2. Masalah Utama pada Struktur Saat Ini

Saat ini struktur aplikasi masih cenderung seperti ini:

```text
Learning Unit
├── Objectives
├── Materials
├── Media
├── Activities
├── Assessments
└── Discussions
```

Masalahnya:

1. Belum ada submenu tetap per KB.
2. Pokok-Pokok Materi belum menjadi struktur khusus.
3. Uraian Materi belum bisa bertingkat.
4. Aktivitas Pembelajaran belum ditampilkan sebagai kelompok yang berisi Ayo Mengamati, Ayo Bertanya, Ayo Mencoba, Ayo Menalar, Ayo Menyimpulkan.
5. Forum masih berpotensi terpisah dari activity engine.
6. Asesmen belum dikelompokkan menjadi I–V sesuai OITLINE.
7. KB5 belum sepenuhnya menjadi workflow proyek terintegrasi.
8. UI murid belum menyerupai outline bahan ajar seperti gambar yang diberikan.

Maka refactor harus difokuskan pada **struktur konten dan flow pembelajaran**, bukan sekadar menambah halaman baru.

---

## 3. Prinsip Refactor

Gunakan prinsip berikut:

```text
Jangan rebuild total.
Jangan hapus model lama.
Tambahkan layer outline.
Hubungkan outline dengan model lama.
Gunakan service layer.
Jangan letakkan logic besar di Blade.
Gunakan migration baru.
Seeder harus idempotent.
UI murid harus mengikuti struktur OITLINE.
```

Arsitektur target:

```text
LearningUnit
├── LearningUnitSection
│   ├── linked to Material
│   ├── linked to Activity
│   ├── linked to Assessment
│   └── content_json untuk data terstruktur
├── Materials
├── Activities
├── Assessments
└── Discussions
```

---

# BAGIAN A — DATABASE REFACTOR

## 4. Tambahkan Tabel `learning_unit_sections`

Buat migration:

```bash
php artisan make:model LearningUnitSection -m
```

Isi migration:

```php
Schema::create('learning_unit_sections', function (Blueprint $table) {
    $table->id();

    $table->foreignId('learning_unit_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('parent_id')
        ->nullable()
        ->constrained('learning_unit_sections')
        ->cascadeOnDelete();

    $table->string('section_type');
    $table->string('title');
    $table->string('slug')->nullable();

    $table->longText('content')->nullable();
    $table->json('content_json')->nullable();

    $table->string('linked_model_type')->nullable();
    $table->unsignedBigInteger('linked_model_id')->nullable();

    $table->unsignedSmallInteger('order')->default(1);
    $table->boolean('is_visible')->default(true);
    $table->boolean('is_required')->default(false);

    $table->timestamps();

    $table->index(['learning_unit_id', 'section_type']);
    $table->index(['linked_model_type', 'linked_model_id']);
});
```

Jalankan:

```bash
php artisan migrate
```

## 5. Standar `section_type`

Gunakan enum string berikut:

```php
[
    'learning_objective',
    'key_points',
    'material_group',
    'material_item',
    'activity_group',
    'activity_item',
    'forum',
    'assessment_group',
    'question_group',
]
```

Makna teknis:

| section_type       | Fungsi                                                                |
| ------------------ | --------------------------------------------------------------------- |
| learning_objective | Menampilkan tujuan pembelajaran KB                                    |
| key_points         | Menampilkan Pokok-Pokok Materi: konsep, fakta, prosedur, metakognitif |
| material_group     | Parent untuk Uraian Materi                                            |
| material_item      | Submateri, misalnya Konsep Energi, Bentuk Energi                      |
| activity_group     | Parent untuk Aktivitas Pembelajaran                                   |
| activity_item      | Link ke Activity tertentu                                             |
| forum              | Link ke Activity phase `forum_diskusi`                                |
| assessment_group   | Parent untuk Asesmen Formatif                                         |
| question_group     | Kelompok soal: PG biasa, PG kompleks, benar/salah, isian, menjodohkan |

---

## 6. Model `LearningUnitSection`

Buat file:

```text
app/Models/LearningUnitSection.php
```

Isi model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningUnitSection extends Model
{
    protected $fillable = [
        'learning_unit_id',
        'parent_id',
        'section_type',
        'title',
        'slug',
        'content',
        'content_json',
        'linked_model_type',
        'linked_model_id',
        'order',
        'is_visible',
        'is_required',
    ];

    protected $casts = [
        'content_json' => 'array',
        'is_visible' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function learningUnit(): BelongsTo
    {
        return $this->belongsTo(LearningUnit::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function linkedModel(): mixed
    {
        if (! $this->linked_model_type || ! $this->linked_model_id) {
            return null;
        }

        if (! class_exists($this->linked_model_type)) {
            return null;
        }

        return $this->linked_model_type::find($this->linked_model_id);
    }
}
```

Tambahkan relasi di `LearningUnit.php`:

```php
public function sections()
{
    return $this->hasMany(LearningUnitSection::class)->orderBy('order');
}

public function rootSections()
{
    return $this->hasMany(LearningUnitSection::class)
        ->whereNull('parent_id')
        ->orderBy('order');
}
```

---

# BAGIAN B — OUTLINE SERVICE

## 7. Buat `LearningUnitOutlineService`

Buat file:

```text
app/Services/Learning/LearningUnitOutlineService.php
```

Service ini bertugas membuat, membaca, dan memastikan setiap KB memiliki submenu sesuai OITLINE.

Skeleton:

```php
<?php

namespace App\Services\Learning;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Models\Material;
use Illuminate\Support\Str;

class LearningUnitOutlineService
{
    public function ensureDefaultOutline(LearningUnit $unit): void
    {
        $this->createLearningObjectiveSection($unit);
        $this->createKeyPointsSection($unit);
        $this->createMaterialSections($unit);
        $this->createActivitySections($unit);
        $this->createForumSection($unit);
        $this->createAssessmentSections($unit);
    }

    private function createLearningObjectiveSection(LearningUnit $unit): void
    {
        LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'learning_objective',
                'slug' => 'tujuan-pembelajaran',
            ],
            [
                'title' => '1. Tujuan Pembelajaran',
                'content' => $unit->objectives,
                'order' => 1,
                'is_visible' => true,
                'is_required' => true,
            ]
        );
    }

    private function createKeyPointsSection(LearningUnit $unit): void
    {
        LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'key_points',
                'slug' => 'pokok-pokok-materi',
            ],
            [
                'title' => '2. Pokok-Pokok Materi',
                'content_json' => $this->defaultKeyPointsFor($unit->order),
                'order' => 2,
                'is_visible' => true,
                'is_required' => true,
            ]
        );
    }

    private function createMaterialSections(LearningUnit $unit): void
    {
        $group = LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'material_group',
                'slug' => 'uraian-materi',
            ],
            [
                'title' => '3. Uraian Materi',
                'order' => 3,
                'is_visible' => true,
                'is_required' => true,
            ]
        );

        foreach ($unit->materials()->orderBy('order')->get() as $index => $material) {
            LearningUnitSection::updateOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'section_type' => 'material_item',
                    'linked_model_type' => Material::class,
                    'linked_model_id' => $material->id,
                ],
                [
                    'parent_id' => $group->id,
                    'title' => $material->title,
                    'slug' => Str::slug($material->title),
                    'content' => $material->content,
                    'order' => $index + 1,
                    'is_visible' => true,
                ]
            );
        }
    }

    private function createActivitySections(LearningUnit $unit): void
    {
        $group = LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'activity_group',
                'slug' => 'aktivitas-pembelajaran',
            ],
            [
                'title' => '4. Aktivitas Pembelajaran',
                'order' => 4,
                'is_visible' => true,
                'is_required' => true,
            ]
        );

        $phases = [
            'ayo_mengamati',
            'ayo_bertanya',
            'ayo_mencoba',
            'ayo_menalar',
            'ayo_menyimpulkan',
        ];

        foreach ($phases as $index => $phase) {
            $activity = $unit->activities()->where('phase', $phase)->first();

            if (! $activity) {
                continue;
            }

            LearningUnitSection::updateOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'section_type' => 'activity_item',
                    'linked_model_type' => Activity::class,
                    'linked_model_id' => $activity->id,
                ],
                [
                    'parent_id' => $group->id,
                    'title' => $activity->title,
                    'slug' => $phase,
                    'order' => $index + 1,
                    'is_visible' => true,
                    'is_required' => $activity->is_required,
                ]
            );
        }
    }

    private function createForumSection(LearningUnit $unit): void
    {
        $forum = $unit->activities()->where('phase', 'forum_diskusi')->first();

        LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'forum',
                'slug' => 'forum-diskusi-refleksi',
            ],
            [
                'title' => '5. Forum Diskusi/Refleksi',
                'linked_model_type' => $forum ? Activity::class : null,
                'linked_model_id' => $forum?->id,
                'order' => 5,
                'is_visible' => true,
                'is_required' => true,
            ]
        );
    }

    private function createAssessmentSections(LearningUnit $unit): void
    {
        $assessment = $unit->assessments()->first();

        $group = LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'assessment_group',
                'slug' => 'asesmen-formatif',
            ],
            [
                'title' => '6. Asesmen Formatif',
                'linked_model_type' => $assessment ? Assessment::class : null,
                'linked_model_id' => $assessment?->id,
                'order' => 6,
                'is_visible' => true,
                'is_required' => true,
            ]
        );

        $questionGroups = [
            'pilihan_ganda_biasa' => 'I. Pilihan Ganda Biasa',
            'pilihan_ganda_kompleks' => 'II. Pilihan Ganda Kompleks',
            'benar_salah' => 'III. Benar atau Salah',
            'isian_uraian_singkat' => 'IV. Isian/Uraian Singkat',
            'menjodohkan' => 'V. Menjodohkan',
        ];

        $order = 1;

        foreach ($questionGroups as $slug => $title) {
            LearningUnitSection::updateOrCreate(
                [
                    'learning_unit_id' => $unit->id,
                    'parent_id' => $group->id,
                    'section_type' => 'question_group',
                    'slug' => $slug,
                ],
                [
                    'title' => $title,
                    'order' => $order++,
                    'is_visible' => true,
                ]
            );
        }
    }

    private function defaultKeyPointsFor(int $kbOrder): array
    {
        return match ($kbOrder) {
            1 => [
                'konsep' => 'Energi adalah kemampuan untuk melakukan kerja atau menyebabkan perubahan.',
                'fakta' => 'Semua aktivitas manusia membutuhkan energi, misalnya memasak, menyalakan lampu, mengisi daya ponsel, mengoperasikan mesin, dan menggerakkan kendaraan.',
                'prosedur' => 'Mengamati penggunaan energi di rumah/sekolah, mencatat jenis energi yang digunakan, lalu mengelompokkan sumber energinya.',
                'metakognitif' => 'Murid merefleksikan apakah energi yang digunakan sehari-hari sudah digunakan secara bijak.',
            ],
            2 => [
                'konsep' => 'Energi fosil berasal dari minyak bumi, batu bara, dan gas alam.',
                'fakta' => 'Bauran energi Indonesia masih banyak didominasi energi fosil.',
                'prosedur' => 'Mengamati contoh penggunaan energi fosil di lingkungan sekitar dan menganalisis dampaknya.',
                'metakognitif' => 'Murid merefleksikan kebiasaan menggunakan listrik, bahan bakar, dan alat elektronik.',
            ],
            3 => [
                'konsep' => 'Energi terbarukan berasal dari sumber alam yang dapat diperbarui atau tersedia terus-menerus.',
                'fakta' => 'Energi terbarukan mencakup energi surya, angin, air, panas bumi, bioenergi, dan energi laut.',
                'prosedur' => 'Mengelompokkan contoh teknologi berdasarkan sumber energinya.',
                'metakognitif' => 'Murid menilai jenis energi terbarukan yang paling sesuai dengan lingkungan tempat tinggalnya.',
            ],
            4 => [
                'konsep' => 'STEM menggabungkan sains, teknologi, rekayasa, dan matematika untuk memecahkan masalah nyata.',
                'fakta' => 'Integrasi energi terbarukan membutuhkan teknologi pendukung seperti baterai, digitalisasi, sistem hibrida, dan jaringan listrik cerdas.',
                'prosedur' => 'Mendesain solusi sederhana, menguji, mencatat hasil, lalu memperbaiki rancangan.',
                'metakognitif' => 'Murid mengevaluasi apakah rancangan sudah efektif, aman, murah, dan ramah lingkungan.',
            ],
            5 => [
                'konsep' => 'Aksi energi terbarukan adalah kegiatan nyata untuk memanfaatkan energi alternatif atau mengurangi ketergantungan pada energi fosil.',
                'fakta' => 'Transisi energi membutuhkan aksi berkelanjutan, efisiensi energi, elektrifikasi, dan pemanfaatan energi terbarukan sejak dini.',
                'prosedur' => 'Identifikasi masalah, kumpulkan data, pilih solusi, buat rancangan, uji coba, evaluasi, dan presentasikan hasil.',
                'metakognitif' => 'Murid menilai kekuatan, kelemahan, peluang, dan kendala proyeknya.',
            ],
            default => [
                'konsep' => null,
                'fakta' => null,
                'prosedur' => null,
                'metakognitif' => null,
            ],
        };
    }
}
```

---

# BAGIAN C — SEEDER OUTLINE

## 8. Buat Seeder `RenewableEnergyOutlineSeeder`

Buat:

```bash
php artisan make:seeder RenewableEnergyOutlineSeeder
```

Isi:

```php
<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Services\Learning\LearningUnitOutlineService;
use Illuminate\Database\Seeder;

class RenewableEnergyOutlineSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::where('slug', 'energi-terbarukan')->first();

        if (! $module) {
            return;
        }

        $outlineService = app(LearningUnitOutlineService::class);

        $module->learningUnits()
            ->with(['materials', 'activities', 'assessments.questions'])
            ->orderBy('order')
            ->get()
            ->each(function ($unit) use ($outlineService) {
                $outlineService->ensureDefaultOutline($unit);
            });
    }
}
```

Tambahkan ke `DatabaseSeeder.php` setelah seeder modul, materi, aktivitas, dan asesmen:

```php
$this->call([
    DemoLearningSeeder::class,
    RenewableEnergyActivitySeeder::class,
    RenewableEnergyOutlineSeeder::class,
]);
```

Jalankan:

```bash
php artisan db:seed --class=RenewableEnergyOutlineSeeder
```

---

# BAGIAN D — REFACTOR LEARNING UNIT PAGE

## 9. Refactor `LearningUnitPage.php`

File:

```text
app/Livewire/Murid/LearningUnitPage.php
```

Pastikan `mount()` memuat sections:

```php
$this->learningUnit = LearningUnit::with([
    'module',
    'rootSections.children',
    'materials.media',
    'media',
    'activities.answers',
    'assessments.questions',
])->findOrFail($learningUnit->id);
```

Tambahkan property:

```php
public ?int $activeSectionId = null;
```

Tambahkan method:

```php
public function openSection(int $sectionId): void
{
    $this->activeSectionId = $sectionId;
}
```

Tambahkan computed/helper:

```php
public function getActiveSectionProperty()
{
    if (! $this->activeSectionId) {
        return $this->learningUnit->rootSections->first();
    }

    return $this->learningUnit
        ->sections()
        ->with('children')
        ->find($this->activeSectionId);
}
```

---

## 10. Refactor Blade `learning-unit-page.blade.php`

File:

```text
resources/views/livewire/murid/learning-unit-page.blade.php
```

Ubah dari tampilan flat menjadi layout dua kolom:

```blade
<div class="grid grid-cols-1 gap-6 lg:grid-cols-[320px_1fr]">
    <aside class="space-y-3">
        <x-learning.unit-outline
            :sections="$learningUnit->rootSections"
            :active-section-id="$activeSectionId"
        />
    </aside>

    <main>
        <x-learning.unit-section-renderer
            :section="$this->activeSection"
            :learning-unit="$learningUnit"
            :activity-statuses="$activityStatuses"
        />
    </main>
</div>
```

---

# BAGIAN E — KOMPONEN OUTLINE

## 11. Buat Komponen `unit-outline`

Buat file:

```text
resources/views/components/learning/unit-outline.blade.php
```

Isi:

```blade
<div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-zinc-900">
    <div class="mb-3 font-semibold">
        Outline Kegiatan Belajar
    </div>

    <nav class="space-y-1">
        @foreach ($sections as $section)
            <button
                type="button"
                wire:click="openSection({{ $section->id }})"
                class="w-full rounded-lg px-3 py-2 text-left text-sm
                    {{ $activeSectionId === $section->id ? 'bg-blue-600 text-white' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
            >
                {{ $section->title }}
            </button>

            @if ($section->children->isNotEmpty())
                <div class="ml-4 space-y-1 border-l pl-3">
                    @foreach ($section->children as $child)
                        <button
                            type="button"
                            wire:click="openSection({{ $child->id }})"
                            class="w-full rounded-lg px-3 py-2 text-left text-xs
                                {{ $activeSectionId === $child->id ? 'bg-blue-100 font-semibold text-blue-700' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                        >
                            {{ $child->title }}
                        </button>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>
</div>
```

---

## 12. Buat Komponen `unit-section-renderer`

Buat file:

```text
resources/views/components/learning/unit-section-renderer.blade.php
```

Isi awal:

```blade
@props([
    'section',
    'learningUnit',
    'activityStatuses' => [],
])

@if (! $section)
    <div class="rounded-xl border p-6">
        Pilih bagian pembelajaran.
    </div>
@else
    <div class="space-y-5 rounded-xl border bg-white p-6 shadow-sm dark:bg-zinc-900">
        <h2 class="text-xl font-bold">
            {{ $section->title }}
        </h2>

        @switch($section->section_type)

            @case('learning_objective')
                <div class="prose max-w-none dark:prose-invert">
                    {!! nl2br(e($section->content ?? $learningUnit->objectives)) !!}
                </div>
                @break

            @case('key_points')
                <x-learning.key-points-table :items="$section->content_json" />
                @break

            @case('material_group')
                <div class="space-y-3">
                    @foreach ($section->children as $child)
                        <button
                            type="button"
                            wire:click="openSection({{ $child->id }})"
                            class="block w-full rounded-lg border p-4 text-left hover:bg-zinc-50 dark:hover:bg-zinc-800"
                        >
                            {{ $child->title }}
                        </button>
                    @endforeach
                </div>
                @break

            @case('material_item')
                @php($material = $section->linkedModel())
                @if($material)
                    <article class="prose max-w-none dark:prose-invert">
                        {!! $material->content !!}
                    </article>

                    @if(method_exists($material, 'media'))
                        <div class="mt-4 grid gap-4">
                            @foreach($material->media ?? [] as $media)
                                <x-learning.media-renderer :media="$media" />
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="text-sm text-zinc-500">
                        Materi belum tersedia.
                    </div>
                @endif
                @break

            @case('activity_group')
                <div class="space-y-3">
                    @foreach ($section->children as $child)
                        <x-learning.activity-section-card
                            :section="$child"
                            :activity-statuses="$activityStatuses"
                        />
                    @endforeach
                </div>
                @break

            @case('activity_item')
                <x-learning.activity-section-card
                    :section="$section"
                    :activity-statuses="$activityStatuses"
                />
                @break

            @case('forum')
                <x-learning.forum-section-card
                    :section="$section"
                    :activity-statuses="$activityStatuses"
                />
                @break

            @case('assessment_group')
                <x-learning.assessment-section-card :section="$section" />
                @break

            @case('question_group')
                <x-learning.question-group-preview :section="$section" />
                @break

            @default
                <div class="prose max-w-none dark:prose-invert">
                    {!! nl2br(e($section->content)) !!}
                </div>

        @endswitch
    </div>
@endif
```

---

# BAGIAN F — KOMPONEN PENDUKUNG

## 13. Komponen Pokok-Pokok Materi

Buat:

```text
resources/views/components/learning/key-points-table.blade.php
```

Isi:

```blade
@props(['items' => []])

<div class="overflow-hidden rounded-xl border">
    <table class="w-full text-sm">
        <thead class="bg-blue-600 text-white">
            <tr>
                <th class="w-40 px-4 py-3 text-left">Aspek</th>
                <th class="px-4 py-3 text-left">Uraian</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([
                'konsep' => 'Konsep',
                'fakta' => 'Fakta',
                'prosedur' => 'Prosedur',
                'metakognitif' => 'Metakognitif',
            ] as $key => $label)
                <tr class="border-t">
                    <td class="bg-blue-50 px-4 py-3 font-semibold dark:bg-zinc-800">
                        {{ $label }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $items[$key] ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

---

## 14. Komponen Activity Card

Buat:

```text
resources/views/components/learning/activity-section-card.blade.php
```

Isi:

```blade
@props([
    'section',
    'activityStatuses' => [],
])

@php
    $activity = $section->linkedModel();
    $status = $activity ? ($activityStatuses[$activity->id]['status'] ?? 'not_started') : 'not_found';
@endphp

@if (! $activity)
    <div class="rounded-lg border p-4 text-sm text-zinc-500">
        Aktivitas belum tersedia.
    </div>
@else
    <div class="rounded-xl border p-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="font-semibold">
                    {{ $section->title }}
                </div>

                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ str($activity->prompt)->limit(160) }}
                </div>

                <div class="mt-2 text-xs">
                    Status: {{ str_replace('_', ' ', $status) }}
                </div>
            </div>

            <div>
                <flux:button
                    size="sm"
                    variant="primary"
                    :href="route('murid.activities.show', $activity)"
                    wire:navigate
                >
                    Kerjakan
                </flux:button>
            </div>
        </div>
    </div>
@endif
```

---

## 15. Komponen Forum Card

Buat:

```text
resources/views/components/learning/forum-section-card.blade.php
```

Isi:

```blade
@props([
    'section',
    'activityStatuses' => [],
])

@php
    $activity = $section->linkedModel();
@endphp

<div class="rounded-xl border p-4">
    <div class="font-semibold">
        Forum Diskusi/Refleksi
    </div>

    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        Ikuti forum diskusi/refleksi sesuai instruksi kegiatan belajar.
    </p>

    @if($activity)
        <flux:button
            class="mt-4"
            size="sm"
            variant="primary"
            :href="route('murid.activities.show', $activity)"
            wire:navigate
        >
            Buka Forum
        </flux:button>
    @else
        <div class="mt-3 text-sm text-zinc-500">
            Aktivitas forum belum tersedia.
        </div>
    @endif
</div>
```

Dengan ini, forum tidak lagi dobel sebagai form terpisah di `LearningUnitPage`. Forum harus masuk lewat activity `forum_diskusi`.

---

## 16. Komponen Assessment Card

Buat:

```text
resources/views/components/learning/assessment-section-card.blade.php
```

Isi:

```blade
@props(['section'])

@php
    $assessment = $section->linkedModel();
@endphp

@if (! $assessment)
    <div class="rounded-lg border p-4 text-sm text-zinc-500">
        Asesmen belum tersedia.
    </div>
@else
    <div class="space-y-4">
        <div class="rounded-xl border p-4">
            <div class="font-semibold">
                {{ $assessment->title }}
            </div>

            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                KKTP: {{ $assessment->kktp }} |
                Maksimal Percobaan: {{ $assessment->max_attempts }}
            </div>

            <flux:button
                class="mt-4"
                size="sm"
                variant="primary"
                :href="route('murid.assessments.show', $assessment)"
                wire:navigate
            >
                Kerjakan Asesmen
            </flux:button>
        </div>

        <div class="space-y-2">
            @foreach($section->children as $child)
                <button
                    type="button"
                    wire:click="openSection({{ $child->id }})"
                    class="block w-full rounded-lg border px-4 py-3 text-left text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800"
                >
                    {{ $child->title }}
                </button>
            @endforeach
        </div>
    </div>
@endif
```

---

# BAGIAN G — ASSESSMENT GROUPING

## 17. Tambahkan Field `question_group`

Buat migration:

```bash
php artisan make:migration add_question_group_to_questions_table
```

Isi:

```php
Schema::table('questions', function (Blueprint $table) {
    $table->string('question_group')->nullable()->after('question_type');
});
```

Jalankan:

```bash
php artisan migrate
```

Update model `Question.php`:

```php
protected $fillable = [
    // field lama
    'question_group',
];
```

## 18. Mapping Question Type ke Group

Buat service kecil:

```text
app/Services/Assessment/QuestionGroupService.php
```

Isi:

```php
<?php

namespace App\Services\Assessment;

class QuestionGroupService
{
    public function groupForType(string $type): string
    {
        return match ($type) {
            'multiple_choice', 'pilihan_ganda' => 'pilihan_ganda_biasa',
            'complex_multiple_choice', 'pilihan_ganda_kompleks' => 'pilihan_ganda_kompleks',
            'true_false', 'benar_salah' => 'benar_salah',
            'short_answer', 'essay', 'isian', 'uraian' => 'isian_uraian_singkat',
            'matching', 'menjodohkan' => 'menjodohkan',
            default => 'lainnya',
        };
    }

    public function labelForGroup(string $group): string
    {
        return match ($group) {
            'pilihan_ganda_biasa' => 'I. Pilihan Ganda Biasa',
            'pilihan_ganda_kompleks' => 'II. Pilihan Ganda Kompleks',
            'benar_salah' => 'III. Benar atau Salah',
            'isian_uraian_singkat' => 'IV. Isian/Uraian Singkat',
            'menjodohkan' => 'V. Menjodohkan',
            default => 'Soal Lainnya',
        };
    }
}
```

Update saat menyimpan soal di `ManageQuestions`:

```php
$questionGroup = app(QuestionGroupService::class)
    ->groupForType($this->question_type);
```

Simpan:

```php
'question_group' => $questionGroup,
```

---

## 19. Refactor `AssessmentPage`

File:

```text
resources/views/livewire/murid/assessment-page.blade.php
```

Sebelum loop soal, group dulu:

```blade
@php
    $groups = $assessment->questions->groupBy(function ($question) {
        return $question->question_group ?? app(\App\Services\Assessment\QuestionGroupService::class)->groupForType($question->question_type);
    });

    $labels = [
        'pilihan_ganda_biasa' => 'I. Pilihan Ganda Biasa',
        'pilihan_ganda_kompleks' => 'II. Pilihan Ganda Kompleks',
        'benar_salah' => 'III. Benar atau Salah',
        'isian_uraian_singkat' => 'IV. Isian/Uraian Singkat',
        'menjodohkan' => 'V. Menjodohkan',
    ];
@endphp

@foreach ($labels as $groupKey => $groupLabel)
    @if($groups->has($groupKey))
        <section class="space-y-4">
            <h2 class="text-lg font-bold">
                {{ $groupLabel }}
            </h2>

            @foreach($groups[$groupKey] as $question)
                {{-- render question existing --}}
            @endforeach
        </section>
    @endif
@endforeach
```

---

# BAGIAN H — REFACTOR ACTIVITY PAGE

## 20. Fix Property dan Schema Renderer

File:

```text
app/Livewire/Murid/ActivityPage.php
```

Tambahkan property:

```php
public array $answer_json = [];
public array $field_data = [];
```

Saat initialize jawaban:

```php
private function initializeSchemaAnswer(): void
{
    $schema = $this->currentActivity->answer_schema ?? [];

    if (($this->currentActivity->input_type === 'table') && isset($schema['preset_rows'])) {
        $this->answer_json = $schema['preset_rows'];
        return;
    }

    if ($this->currentActivity->input_type === 'table') {
        $rows = $schema['min_rows'] ?? 1;

        $this->answer_json = collect(range(1, $rows))
            ->map(fn () => [])
            ->toArray();

        return;
    }

    if (isset($schema['fields'])) {
        $this->field_data = collect($schema['fields'])
            ->mapWithKeys(fn ($field) => [$field['name'] => null])
            ->toArray();
    }
}
```

---

## 21. Pecah Blade Input Renderer

Buat folder:

```text
resources/views/livewire/murid/activities/inputs
```

Isi:

```text
short-text.blade.php
essay.blade.php
table.blade.php
fields.blade.php
file.blade.php
discussion.blade.php
project-form.blade.php
```

Pada `activity-page.blade.php`:

```blade
@switch($currentActivity->input_type)
    @case('short_text')
        @include('livewire.murid.activities.inputs.short-text')
        @break

    @case('essay')
        @include('livewire.murid.activities.inputs.essay')
        @break

    @case('table')
        @include('livewire.murid.activities.inputs.table')
        @break

    @case('file')
        @include('livewire.murid.activities.inputs.file')
        @break

    @case('discussion')
        @include('livewire.murid.activities.inputs.discussion')
        @break

    @case('project_form')
        @include('livewire.murid.activities.inputs.project-form')
        @break

    @default
        @include('livewire.murid.activities.inputs.essay')
@endswitch
```

---

## 22. Renderer Tabel Wajib Mendukung Tipe Kolom

Pada `table.blade.php`, buat logic:

```blade
@foreach($answer_json as $rowIndex => $row)
    <tr>
        @foreach(($currentActivity->answer_schema['columns'] ?? []) as $column)
            <td>
                @switch($column['type'])
                    @case('select')
                        <select wire:model="answer_json.{{ $rowIndex }}.{{ $column['name'] }}">
                            <option value="">Pilih</option>
                            @foreach($column['options'] ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('textarea')
                        <textarea wire:model="answer_json.{{ $rowIndex }}.{{ $column['name'] }}"></textarea>
                        @break

                    @case('number')
                        <input type="number" wire:model.live="answer_json.{{ $rowIndex }}.{{ $column['name'] }}">
                        @break

                    @case('readonly_text')
                        <input
                            type="text"
                            value="{{ $answer_json[$rowIndex][$column['name']] ?? '' }}"
                            readonly
                        >
                        @break

                    @case('computed')
                        <input
                            type="text"
                            value="{{ $this->calculateComputedValue($column['formula'] ?? null, $answer_json[$rowIndex] ?? []) }}"
                            readonly
                        >
                        @break

                    @default
                        <input type="text" wire:model="answer_json.{{ $rowIndex }}.{{ $column['name'] }}">
                @endswitch
            </td>
        @endforeach
    </tr>
@endforeach
```

Tambahkan method di `ActivityPage.php`:

```php
public function calculateComputedValue(?string $formula, array $row): mixed
{
    return match ($formula) {
        'suhu_akhir - suhu_awal' => ((float) ($row['suhu_akhir'] ?? 0)) - ((float) ($row['suhu_awal'] ?? 0)),
        default => null,
    };
}
```

Sebelum submit, computed field harus disimpan ke `answer_json`.

---

# BAGIAN I — PROJECT WORKFLOW KB5

## 23. Fix Tabel `projects`

Saat ini KB5 harus menjadi workflow proyek. Tambahkan migration:

```bash
php artisan make:migration add_learning_unit_and_project_type_to_projects_table
```

Isi:

```php
Schema::table('projects', function (Blueprint $table) {
    $table->foreignId('learning_unit_id')
        ->nullable()
        ->after('module_id')
        ->constrained()
        ->nullOnDelete();

    $table->string('project_type')
        ->nullable()
        ->after('project_title');

    $table->longText('data_to_collect')
        ->nullable()
        ->after('collected_data');
});
```

Jalankan:

```bash
php artisan migrate
```

## 24. Fix `ProjectDraftService`

File:

```text
app/Services/Learning/ProjectDraftService.php
```

Pastikan menggunakan `user_id`, bukan `student_id`.

Contoh:

```php
$project = Project::updateOrCreate(
    [
        'user_id' => $answer->user_id,
        'module_id' => $activity->learningUnit->module_id,
        'learning_unit_id' => $activity->learning_unit_id,
    ],
    [
        'project_title' => $data['project_title'] ?? $data['project_type'] ?? 'Rancangan Proyek Energi Terbarukan',
        'project_type' => $data['project_type'] ?? null,
        'problem' => $data['problem'] ?? null,
        'objective' => $data['objective'] ?? null,
        'tools_materials' => $data['tools_materials'] ?? null,
        'procedure' => $data['procedure'] ?? null,
        'data_to_collect' => $data['data_to_collect'] ?? null,
        'expected_result' => $data['expected_result'] ?? null,
        'status' => 'draft',
    ]
);
```

---

# BAGIAN J — GURU MANAGE OUTLINE

## 25. Buat Halaman Guru untuk Kelola Outline

Buat component:

```bash
php artisan make:livewire Guru/ManageLearningUnitOutline
```

Route:

```php
Route::get('/guru/learning-units/{learningUnit}/outline', ManageLearningUnitOutline::class)
    ->name('guru.learning-units.outline');
```

Fitur minimal:

1. Guru melihat struktur outline KB.
2. Guru dapat edit title section.
3. Guru dapat edit content untuk tujuan dan key points.
4. Guru dapat reorder section.
5. Guru dapat regenerate outline dari template OITLINE.
6. Guru dapat link section ke material/activity/assessment.

Tombol penting:

```text
Generate Outline OITLINE
Sinkronkan Aktivitas
Sinkronkan Asesmen
Simpan Urutan
```

---

# BAGIAN K — UPDATE SIDEBAR / NAVIGASI MURID

## 26. Akses Murid yang Diharapkan

Route tetap:

```text
/murid/learning-units/{learningUnit}
```

Tetapi tampilan berubah:

```text
Kegiatan Belajar 1
Deteksi Potensi Energi di Indonesia

Sidebar:
1. Tujuan Pembelajaran
2. Pokok-Pokok Materi
3. Uraian Materi
   a. Konsep Energi
   b. Bentuk Energi
   c. Perubahan Energi
   d. Sumber Energi
4. Aktivitas Pembelajaran
   Ayo Mengamati
   Ayo Bertanya
   Ayo Mencoba
   Ayo Menalar
   Ayo Menyimpulkan
5. Forum Diskusi/Refleksi
6. Asesmen Formatif
   I. Pilihan Ganda Biasa
   II. Pilihan Ganda Kompleks
   III. Benar atau Salah
   IV. Isian/Uraian Singkat
   V. Menjodohkan
```

Konten kanan mengikuti section yang diklik.

---

# BAGIAN L — PROGRESS LOCKING

## 27. Sesuaikan `ProgressService`

Progress harus membaca struktur baru, tetapi tetap memakai status aktivitas dan asesmen yang sudah ada.

Aturan:

```text
Tujuan Pembelajaran dibaca
Pokok-Pokok Materi dibaca
Uraian Materi dibaca
Semua aktivitas required selesai
Forum Diskusi/Refleksi selesai
Asesmen Formatif dikerjakan
Nilai asesmen >= KKTP
Baru lanjut KB berikutnya
```

Untuk MVP, section baca materi boleh dianggap selesai saat halaman dibuka. Untuk versi lebih kuat, tambahkan tabel:

```text
section_progress
```

Field:

```php
id
user_id
learning_unit_section_id
status
viewed_at
completed_at
created_at
updated_at
```

Namun untuk sprint refactor awal, section progress bisa ditunda agar tidak memperbesar scope.

---

# BAGIAN M — TESTING

## 28. Test Wajib

Buat test:

```text
tests/Feature/LearningUnitOutlineTest.php
tests/Feature/ltc1q0gq5ghan358l8y6unf2yz7s42efgnqcut0pvu6.php
tests/Feature/AssessmentQuestionGroupingTest.php
tests/Feature/ProjectFormIntegrationTest.php
tests/Feature/ActivityRendererSchemaTest.php
```

### Test 1 — Outline Dibuat untuk Setiap KB

```php
it('creates default outline for every learning unit', function () {
    $this->seed();

    $module = Module::where('slug', 'energi-terbarukan')->first();

    foreach ($module->learningUnits as $unit) {
        expect($unit->rootSections()->count())->toBe(6);
    }
});
```

### Test 2 — Root Section Sesuai OITLINE

```php
it('has OITLINE root sections in correct order', function () {
    $unit = LearningUnit::first();

    $titles = $unit->rootSections()->pluck('title')->toArray();

    expect($titles)->toBe([
        '1. Tujuan Pembelajaran',
        '2. Pokok-Pokok Materi',
        '3. Uraian Materi',
        '4. Aktivitas Pembelajaran',
        '5. Forum Diskusi/Refleksi',
        '6. Asesmen Formatif',
    ]);
});
```

### Test 3 — Aktivitas Ada 5 + Forum

```php
it('links five learning activities and forum section', function () {
    $unit = LearningUnit::first();

    expect($unit->activities()->count())->toBeGreaterThanOrEqual(6);

    expect($unit->activities()->where('phase', 'ayo_mengamati')->exists())->toBeTrue();
    expect($unit->activities()->where('phase', 'ayo_bertanya')->exists())->toBeTrue();
    expect($unit->activities()->where('phase', 'ayo_mencoba')->exists())->toBeTrue();
    expect($unit->activities()->where('phase', 'ayo_menalar')->exists())->toBeTrue();
    expect($unit->activities()->where('phase', 'ayo_menyimpulkan')->exists())->toBeTrue();
    expect($unit->activities()->where('phase', 'forum_diskusi')->exists())->toBeTrue();
});
```

### Test 4 — Asesmen Punya 5 Group

```php
it('creates five question groups for formative assessment', function () {
    $unit = LearningUnit::first();

    $assessment = $unit->assessments()->first();

    expect($assessment)->not->toBeNull();

    $groups = $unit->sections()
        ->where('section_type', 'question_group')
        ->pluck('slug')
        ->toArray();

    expect($groups)->toContain('pilihan_ganda_biasa');
    expect($groups)->toContain('pilihan_ganda_kompleks');
    expect($groups)->toContain('benar_salah');
    expect($groups)->toContain('isian_uraian_singkat');
    expect($groups)->toContain('menjodohkan');
});
```

---

# BAGIAN N — URUTAN KERJA CODING

## 29. Sprint Refactor Bertahap

### Sprint 1 — Database dan Outline

```text
1. Buat model LearningUnitSection.
2. Buat migration learning_unit_sections.
3. Tambah relasi ke LearningUnit.
4. Buat LearningUnitOutlineService.
5. Buat RenewableEnergyOutlineSeeder.
6. Jalankan seeder.
7. Pastikan setiap KB punya 6 root sections.
```

Command:

```bash
php artisan make:model LearningUnitSection -m
php artisan migrate
php artisan make:seeder RenewableEnergyOutlineSeeder
php artisan db:seed --class=RenewableEnergyOutlineSeeder
```

---

### Sprint 2 — UI Murid Berbasis Outline

```text
1. Refactor LearningUnitPage.
2. Buat unit-outline component.
3. Buat unit-section-renderer component.
4. Buat key-points-table.
5. Buat activity-section-card.
6. Buat forum-section-card.
7. Buat assessment-section-card.
8. Hapus form forum langsung dari LearningUnitPage.
9. Pastikan forum masuk lewat activity forum_diskusi.
```

---

### Sprint 3 — Activity Renderer

```text
1. Tambah property answer_json.
2. Pecah input renderer.
3. Tambah support fields.
4. Tambah support preset_rows.
5. Tambah support select.
6. Tambah support textarea.
7. Tambah support readonly_text.
8. Tambah support computed.
9. Tambah support project_form.
10. Pastikan ActivityAnswerService dipakai penuh.
```

---

### Sprint 4 — Asesmen Grouping

```text
1. Tambah question_group.
2. Buat QuestionGroupService.
3. Update ManageQuestions.
4. Update AssessmentPage.
5. Tampilkan soal dalam kelompok I–V.
6. Pastikan scoring tetap berjalan.
```

---

### Sprint 5 — KB5 Project Workflow

```text
1. Tambah learning_unit_id ke projects.
2. Tambah project_type ke projects.
3. Tambah data_to_collect ke projects.
4. Fix ProjectDraftService.
5. Hubungkan project_form dari ActivityPage.
6. Pastikan guru tetap bisa review proyek.
```

---

### Sprint 6 — Test dan Hardening

```text
1. Buat test outline.
2. Buat test activity renderer.
3. Buat test assessment grouping.
4. Buat test project_form.
5. Jalankan test.
6. Jalankan Pint.
7. Jalankan npm build.
8. Perbaiki error.
```

---

# BAGIAN O — COMMAND VALIDASI

Setelah semua refactor:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed
php artisan test
./vendor/bin/pint
npm run build
```

Jika storage media belum aktif:

```bash
php artisan storage:link
```

Jika ingin reset lokal:

```bash
php artisan migrate:fresh --seed
```

---

# BAGIAN P — DEFINITION OF DONE

Refactor dianggap selesai jika:

```text
[ ] Setiap KB1–KB5 memiliki 6 root section.
[ ] Semua KB memiliki submenu yang sama.
[ ] Pokok-Pokok Materi tampil sebagai tabel Konsep/Fakta/Prosedur/Metakognitif.
[ ] Uraian Materi tampil sebagai group dan submateri.
[ ] Aktivitas Pembelajaran tampil sebagai group Ayo Mengamati–Ayo Menyimpulkan.
[ ] Forum Diskusi/Refleksi tampil sebelum Asesmen Formatif.
[ ] Forum membuka activity forum_diskusi, bukan form forum terpisah.
[ ] Asesmen Formatif tampil dengan kelompok I–V.
[ ] ActivityPage mendukung table, fields, select, textarea, readonly_text, computed, dan project_form.
[ ] KB5 project_form membuat/memperbarui data projects.
[ ] Progress locking tetap berjalan.
[ ] Murid tidak bisa lanjut KB berikutnya sebelum KB aktif selesai.
[ ] Guru bisa mengelola outline atau minimal generate ulang outline.
[ ] Test utama berhasil.
[ ] Tidak ada error migration.
[ ] Tidak ada logic besar langsung di Blade.
```

---

# BAGIAN Q — PROMPT UNTUK AI AGENT / CODEX

Gunakan prompt ini untuk mengeksekusi refactor:

```text
Anda adalah senior Laravel Livewire developer untuk project E-LKM Interaktif.

Baca seluruh source project terlebih dahulu, terutama:
- routes/web.php
- app/Models
- app/Livewire/Murid/LearningUnitPage.php
- resources/views/livewire/murid/learning-unit-page.blade.php
- app/Livewire/Murid/ActivityPage.php
- resources/views/livewire/murid/activity-page.blade.php
- app/Services/Learning
- app/Services/Assessment
- database/migrations
- database/seeders
- tests/Feature

Tujuan:
Refactor aplikasi agar struktur Kegiatan Belajar mengikuti OITLINE E-LKM V2.

Struktur wajib setiap KB:
1. Tujuan Pembelajaran
2. Pokok-Pokok Materi
3. Uraian Materi
4. Aktivitas Pembelajaran
   - Ayo Mengamati
   - Ayo Bertanya
   - Ayo Mencoba
   - Ayo Menalar
   - Ayo Menyimpulkan
5. Forum Diskusi/Refleksi
6. Asesmen Formatif
   - I. Pilihan Ganda Biasa
   - II. Pilihan Ganda Kompleks
   - III. Benar atau Salah
   - IV. Isian/Uraian Singkat
   - V. Menjodohkan

Tugas implementasi:
1. Tambahkan model dan tabel learning_unit_sections.
2. Buat LearningUnitOutlineService.
3. Buat RenewableEnergyOutlineSeeder.
4. Refactor LearningUnitPage menjadi outline/submenu renderer.
5. Buat komponen Blade:
   - learning/unit-outline.blade.php
   - learning/unit-section-renderer.blade.php
   - learning/key-points-table.blade.php
   - learning/activity-section-card.blade.php
   - learning/forum-section-card.blade.php
   - learning/assessment-section-card.blade.php
6. Pastikan Forum Diskusi/Refleksi memakai activity phase forum_diskusi.
7. Tambahkan question_group pada questions.
8. Buat QuestionGroupService.
9. Refactor AssessmentPage agar soal tampil dalam kelompok I–V.
10. Refactor ActivityPage agar mendukung answer_json, fields, preset_rows, select, textarea, readonly_text, computed, dan project_form.
11. Tambahkan learning_unit_id, project_type, dan data_to_collect ke projects.
12. Fix ProjectDraftService agar memakai user_id dan compatible dengan tabel projects.
13. Buat test untuk outline, activity renderer, assessment grouping, dan project_form.
14. Jalankan php artisan test, ./vendor/bin/pint, dan npm run build.

Batasan:
- Jangan hapus model lama.
- Jangan rebuild dari nol.
- Jangan hapus fitur yang sudah berjalan.
- Jangan membuat halaman statis per KB.
- Gunakan service layer.
- Seeder harus idempotent.
- Output akhir harus berisi daftar file yang diubah, migration baru, service baru, komponen baru, test yang dijalankan, dan fitur yang berhasil.
```

---

# BAGIAN R — CATATAN KHUSUS UNTUK PROJECT INI

Beberapa perhatian teknis berdasarkan kondisi source saat ini:

1. `learning_units` sudah punya `objectives`, jadi section Tujuan Pembelajaran bisa mengambil data dari field itu.
2. `materials` masih flat, maka perlu dipetakan menjadi `material_group` dan `material_item`.
3. `activities` sudah punya `phase`, jadi mudah dipetakan ke activity section.
4. `RenewableEnergyActivitySeeder` sudah bisa membuat 30 aktivitas, tetapi outline belum mengikat aktivitas itu ke submenu UI.
5. `LearningUnitPage` harus menjadi pusat perubahan UI.
6. Form forum lama di `LearningUnitPage` sebaiknya dipindahkan ke activity `forum_diskusi`.
7. `ActivityPage` perlu dibuat lebih kuat karena OITLINE memakai tabel, peta masalah, eksperimen suhu, dan project form.
8. KB5 harus diperlakukan sebagai proyek, bukan hanya essay.
9. Asesmen harus tampil seperti dokumen OITLINE, bukan loop soal datar.
10. Refactor ini harus tetap menjaga progress locking dan remedial yang sudah ada.
