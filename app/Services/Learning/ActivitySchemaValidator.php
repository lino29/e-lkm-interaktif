<?php

namespace App\Services\Learning;

use App\Models\Activity;

class ActivitySchemaValidator
{
    /**
     * @return array{valid: bool, errors: array<int, string>}
     */
    public function validate(Activity $activity, array|string|null $answerJson, ?string $answerText): array
    {
        $errors = [];
        $rules = $activity->validation_rules ?? [];
        $schema = $activity->answer_schema ?? [];

        $isRequired = $activity->is_required || ($rules['required'] ?? false);

        if ($isRequired) {
            if ($activity->input_type === 'short_text' || $activity->input_type === 'essay' || $activity->input_type === 'discussion') {
                if (empty(trim((string) $answerText))) {
                    $errors[] = 'Jawaban teks tidak boleh kosong.';
                } else {
                    $wordCount = str_word_count(trim((string) $answerText));

                    if (isset($rules['min_words']) && $wordCount < $rules['min_words']) {
                        $errors[] = "Jawaban minimal terdiri dari {$rules['min_words']} kata. Saat ini: {$wordCount} kata.";
                    }
                    if (isset($rules['max_words']) && $wordCount > $rules['max_words']) {
                        $errors[] = "Jawaban maksimal terdiri dari {$rules['max_words']} kata. Saat ini: {$wordCount} kata.";
                    }
                }
            }

            if ($activity->input_type === 'table') {
                if (! is_array($answerJson)) {
                    $errors[] = 'Format tabel tidak valid.';
                } else {
                    $minRows = $schema['min_rows'] ?? 1;
                    if (count($answerJson) < $minRows) {
                        $errors[] = "Tabel minimal harus diisi dengan {$minRows} baris jawaban.";
                    }

                    $columns = $schema['columns'] ?? [];
                    foreach ($answerJson as $index => $row) {
                        foreach ($columns as $column) {
                            if (($column['required'] ?? false) && $column['type'] !== 'computed') {
                                if (empty(trim((string) ($row[$column['name']] ?? '')))) {
                                    $errors[] = 'Baris '.($index + 1).": Kolom '{$column['label']}' wajib diisi.";
                                }
                            }
                        }
                    }
                }
            }

            if ($activity->input_type === 'project_form') {
                if (! is_array($answerJson) || empty($answerJson)) {
                    $errors[] = 'Form proyek tidak boleh kosong.';
                } else {
                    // Assuming project_form uses schema['fields'] or schema['columns']?
                    // The sprint doc says project_form uses `fields`.
                    $fields = $schema['fields'] ?? [];
                    foreach ($fields as $field) {
                        if ($field['required'] ?? false) {
                            $row = $answerJson[0] ?? []; // fields usually map to a single row array
                            if (empty(trim((string) ($row[$field['name']] ?? '')))) {
                                $errors[] = "Field '{$field['label']}' wajib diisi.";
                            }
                        }
                    }
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
