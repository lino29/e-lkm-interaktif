<?php

namespace App\Services\Learning;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Models\Material;
use App\Services\Assessment\QuestionGroupService;
use Illuminate\Support\Str;

class LearningUnitOutlineService
{
    public const ROOT_TITLES = [
        '1. Tujuan Pembelajaran',
        '2. Pokok-Pokok Materi',
        '3. Uraian Materi',
        '4. Aktivitas Pembelajaran',
        '5. Forum Diskusi/Refleksi',
        '6. Asesmen Formatif',
    ];

    public function ensureDefaultOutline(LearningUnit $unit): void
    {
        $unit->loadMissing(['materials', 'activities', 'assessments.questions']);

        $this->createLearningObjectiveSection($unit);
        $this->createKeyPointsSection($unit);
        $this->createMaterialSections($unit);
        $this->createActivitySections($unit);
        $this->createForumSection($unit);
        $this->createAssessmentSections($unit);
    }

    private function createLearningObjectiveSection(LearningUnit $unit): void
    {
        $this->syncSection(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'learning_objective',
                'slug' => 'tujuan-pembelajaran',
            ],
            [
                'title' => self::ROOT_TITLES[0],
                'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['learning_objective'],
                'content' => $unit->objectives,
                'order' => 1,
                'is_visible' => true,
                'is_required' => true,
            ],
        );
    }

    private function createKeyPointsSection(LearningUnit $unit): void
    {
        $this->syncSection(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'key_points',
                'slug' => 'pokok-pokok-materi',
            ],
            [
                'title' => self::ROOT_TITLES[1],
                'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['key_points'],
                'content_json' => $this->defaultKeyPointsFor($unit->order),
                'order' => 2,
                'is_visible' => true,
                'is_required' => true,
            ],
        );
    }

    private function createMaterialSections(LearningUnit $unit): void
    {
        $group = $this->syncSection(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'material_group',
                'slug' => 'uraian-materi',
            ],
            [
                'title' => self::ROOT_TITLES[2],
                'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['material_group'],
                'order' => 3,
                'is_visible' => true,
                'is_required' => true,
            ],
        );

        foreach ($unit->materials()->orderBy('order')->get() as $index => $material) {
            $this->syncSection(
                [
                    'learning_unit_id' => $unit->id,
                    'section_type' => 'material_item',
                    'linked_model_type' => Material::class,
                    'linked_model_id' => $material->id,
                ],
                [
                    'parent_id' => $group->id,
                    'title' => $material->title,
                    'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['material_item'],
                    'slug' => Str::slug($material->title),
                    'content' => $material->content,
                    'order' => $index + 1,
                    'is_visible' => true,
                ],
            );
        }
    }

    private function createActivitySections(LearningUnit $unit): void
    {
        $group = $this->syncSection(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'activity_group',
                'slug' => 'aktivitas-pembelajaran',
            ],
            [
                'title' => self::ROOT_TITLES[3],
                'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['activity_group'],
                'order' => 4,
                'is_visible' => true,
                'is_required' => true,
            ],
        );

        foreach (['ayo_mengamati', 'ayo_bertanya', 'ayo_mencoba', 'ayo_menalar', 'ayo_menyimpulkan'] as $index => $phase) {
            $activity = $unit->activities()->where('phase', $phase)->first();

            if (! $activity) {
                continue;
            }

            $this->syncSection(
                [
                    'learning_unit_id' => $unit->id,
                    'section_type' => 'activity_item',
                    'linked_model_type' => Activity::class,
                    'linked_model_id' => $activity->id,
                ],
                [
                    'parent_id' => $group->id,
                    'title' => $activity->title,
                    'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['activity_item'],
                    'slug' => $phase,
                    'order' => $index + 1,
                    'is_visible' => true,
                    'is_required' => $activity->is_required,
                ],
            );
        }
    }

    private function createForumSection(LearningUnit $unit): void
    {
        $forum = $unit->activities()->where('phase', 'forum_diskusi')->first();

        $this->syncSection(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'forum',
                'slug' => 'forum-diskusi-refleksi',
            ],
            [
                'title' => self::ROOT_TITLES[4],
                'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['forum'],
                'linked_model_type' => $forum ? Activity::class : null,
                'linked_model_id' => $forum?->id,
                'order' => 5,
                'is_visible' => true,
                'is_required' => true,
            ],
        );
    }

    private function createAssessmentSections(LearningUnit $unit): void
    {
        $assessment = $unit->assessments()
            ->where('type', 'formative')
            ->orderBy('order')
            ->first() ?? $unit->assessments()->orderBy('order')->first();

        $group = $this->syncSection(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'assessment_group',
                'slug' => 'asesmen-formatif',
            ],
            [
                'title' => self::ROOT_TITLES[5],
                'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['assessment_group'],
                'linked_model_type' => $assessment ? Assessment::class : null,
                'linked_model_id' => $assessment?->id,
                'order' => 6,
                'is_visible' => true,
                'is_required' => true,
            ],
        );

        $order = 1;

        foreach (QuestionGroupService::GROUP_LABELS as $slug => $title) {
            $this->syncSection(
                [
                    'learning_unit_id' => $unit->id,
                    'parent_id' => $group->id,
                    'section_type' => 'question_group',
                    'slug' => $slug,
                ],
                [
                    'title' => $title,
                    'editor_type' => DynamicOutlineService::DEFAULT_EDITORS['question_group'],
                    'order' => $order++,
                    'is_visible' => true,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    private function syncSection(array $attributes, array $values): LearningUnitSection
    {
        $section = LearningUnitSection::query()->firstOrCreate($attributes, $values);

        if ($section->wasRecentlyCreated) {
            return $section;
        }

        $updates = $values;

        foreach (['content', 'content_json'] as $contentField) {
            if (filled($section->{$contentField})) {
                unset($updates[$contentField]);
            }
        }

        $section->update($updates);

        return $section->fresh();
    }

    /**
     * @return array{konsep: ?string, fakta: ?string, prosedur: ?string, metakognitif: ?string}
     */
    private function defaultKeyPointsFor(int $kbOrder): array
    {
        return match ($kbOrder) {
            1 => [
                'konsep' => 'Energi adalah kemampuan untuk melakukan kerja atau menyebabkan perubahan.',
                'fakta' => 'Aktivitas manusia membutuhkan energi dalam bentuk panas, cahaya, gerak, bunyi, dan listrik.',
                'prosedur' => 'Amati penggunaan energi di rumah atau sekolah, catat jenis energi, lalu kelompokkan sumbernya.',
                'metakognitif' => 'Refleksikan apakah energi sehari-hari sudah digunakan secara bijak.',
            ],
            2 => [
                'konsep' => 'Energi fosil berasal dari minyak bumi, batu bara, dan gas alam.',
                'fakta' => 'Pembakaran energi fosil menghasilkan emisi dan polusi udara.',
                'prosedur' => 'Identifikasi contoh penggunaan energi fosil lalu analisis dampaknya.',
                'metakognitif' => 'Nilai kebiasaan menggunakan listrik, bahan bakar, dan alat elektronik.',
            ],
            3 => [
                'konsep' => 'Energi terbarukan berasal dari sumber alam yang dapat diperbarui.',
                'fakta' => 'Contohnya energi surya, angin, air, panas bumi, biomassa, dan energi laut.',
                'prosedur' => 'Kelompokkan teknologi berdasarkan sumber energi dan kondisi lingkungan.',
                'metakognitif' => 'Tentukan energi terbarukan yang paling sesuai untuk lingkungan sekitar.',
            ],
            4 => [
                'konsep' => 'STEM menggabungkan sains, teknologi, rekayasa, dan matematika.',
                'fakta' => 'Teknologi energi terbarukan membutuhkan komponen, pengukuran, dan evaluasi efisiensi.',
                'prosedur' => 'Rancang solusi, uji, catat data, lalu perbaiki rancangan.',
                'metakognitif' => 'Evaluasi efektivitas, keamanan, biaya, dan dampak rancangan.',
            ],
            5 => [
                'konsep' => 'Aksi energi terbarukan adalah pemecahan masalah energi melalui rancangan nyata.',
                'fakta' => 'Transisi energi membutuhkan efisiensi, elektrifikasi, dan penggunaan energi bersih.',
                'prosedur' => 'Identifikasi masalah, kumpulkan data, pilih solusi, rancang, uji, dan presentasikan.',
                'metakognitif' => 'Tinjau kekuatan, kelemahan, peluang, dan kendala proyek.',
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
