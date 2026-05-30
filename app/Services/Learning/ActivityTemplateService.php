<?php

namespace App\Services\Learning;

class ActivityTemplateService
{
    /**
     * Get the default template configuration for a specific phase.
     */
    public function getTemplateForPhase(string $phase): array
    {
        return match ($phase) {
            'ayo_mengamati' => [
                'input_type' => 'essay',
                'title' => 'Ayo Mengamati',
                'prompt' => 'Tuliskan hasil pengamatan Anda pada kolom di bawah ini.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ],
            'ayo_bertanya' => [
                'input_type' => 'short_text',
                'title' => 'Ayo Bertanya',
                'prompt' => 'Tuliskan pertanyaan yang muncul di benak Anda terkait materi ini.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ],
            'ayo_mencoba' => [
                'input_type' => 'table',
                'title' => 'Ayo Mencoba',
                'prompt' => 'Lengkapi tabel percobaan di bawah ini dengan data yang sesuai.',
                'answer_schema' => [
                    'columns' => [
                        ['name' => 'alat', 'label' => 'Nama Alat/Bahan', 'type' => 'text'],
                        ['name' => 'hasil', 'label' => 'Hasil/Pengamatan', 'type' => 'text'],
                    ],
                    'min_rows' => 3,
                    'allow_add' => true,
                ],
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false,
            ],
            'ayo_menalar' => [
                'input_type' => 'essay',
                'title' => 'Ayo Menalar',
                'prompt' => 'Jelaskan alasan atau hubungan sebab-akibat dari hasil percobaan Anda.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => true,
            ],
            'ayo_menyimpulkan' => [
                'input_type' => 'essay',
                'title' => 'Ayo Menyimpulkan',
                'prompt' => 'Tuliskan kesimpulan akhir berdasarkan seluruh aktivitas di atas.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => true,
            ],
            'forum_diskusi' => [
                'input_type' => 'discussion',
                'title' => 'Forum Diskusi/Refleksi',
                'prompt' => 'Tuliskan pandangan Anda di forum ini, dan berikan tanggapan untuk teman yang lain.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => ['required' => true],
                'requires_teacher_review' => false, // Forums usually have their own scoring flow.
            ],
            default => [
                'input_type' => 'short_text',
                'title' => 'Aktivitas Umum',
                'prompt' => 'Isikan jawaban Anda.',
                'answer_schema' => null,
                'display_config' => null,
                'validation_rules' => null,
                'requires_teacher_review' => false,
            ],
        };
    }

    /**
     * Check if a given JSON string is valid for schema structure.
     */
    public function isValidSchema(?string $json): bool
    {
        if (blank($json)) {
            return true;
        }

        json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Additional simple structure check for tables
        $decoded = json_decode($json, true);
        if (is_array($decoded) && isset($decoded['columns'])) {
            return is_array($decoded['columns']);
        }

        return true;
    }
}
