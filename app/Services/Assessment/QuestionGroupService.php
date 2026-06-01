<?php

namespace App\Services\Assessment;

class QuestionGroupService
{
    public const GROUP_LABELS = [
        'pilihan_ganda_biasa' => 'I. Pilihan Ganda Biasa',
        'pilihan_ganda_kompleks' => 'II. Pilihan Ganda Kompleks',
        'benar_salah' => 'III. Benar atau Salah',
        'isian_uraian_singkat' => 'IV. Isian/Uraian Singkat',
        'menjodohkan' => 'V. Menjodohkan',
    ];

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
        return self::GROUP_LABELS[$group] ?? 'Soal Lainnya';
    }
}
