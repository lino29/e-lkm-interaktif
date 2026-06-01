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
        LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'learning_objective',
                'slug' => 'tujuan-pembelajaran',
            ],
            [
                'title' => self::ROOT_TITLES[0],
                'content' => $unit->objectives,
                'order' => 1,
                'is_visible' => true,
                'is_required' => true,
            ],
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
                'title' => self::ROOT_TITLES[1],
                'content_json' => $this->defaultKeyPointsFor($unit->order),
                'order' => 2,
                'is_visible' => true,
                'is_required' => true,
            ],
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
                'title' => self::ROOT_TITLES[2],
                'order' => 3,
                'is_visible' => true,
                'is_required' => true,
            ],
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
                ],
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
                'title' => self::ROOT_TITLES[3],
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
                ],
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
                'title' => self::ROOT_TITLES[4],
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

        $group = LearningUnitSection::updateOrCreate(
            [
                'learning_unit_id' => $unit->id,
                'section_type' => 'assessment_group',
                'slug' => 'asesmen-formatif',
            ],
            [
                'title' => self::ROOT_TITLES[5],
                'linked_model_type' => $assessment ? Assessment::class : null,
                'linked_model_id' => $assessment?->id,
                'order' => 6,
                'is_visible' => true,
                'is_required' => true,
            ],
        );

        $order = 1;

        foreach (QuestionGroupService::GROUP_LABELS as $slug => $title) {
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
                ],
            );
        }
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
