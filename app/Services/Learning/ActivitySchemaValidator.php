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

        if ($activity->is_required || ($rules['required'] ?? false)) {
            $this->validateTextAnswer($activity, $answerText, $rules, $errors);
            $this->validateTableAnswer($activity, $answerJson, $schema, $errors);
            $this->validateFieldAnswer($activity, $answerJson, $schema, $errors);
            $this->validateForumAnswer($activity, $answerJson, $rules, $errors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<int, string>  $errors
     */
    private function validateTextAnswer(Activity $activity, ?string $answerText, array $rules, array &$errors): void
    {
        if (! in_array($activity->input_type, ['short_text', 'essay', 'discussion'], true)) {
            return;
        }

        if (blank(trim((string) $answerText))) {
            $errors[] = 'Jawaban teks tidak boleh kosong.';

            return;
        }

        $wordCount = str_word_count(trim((string) $answerText));

        if (isset($rules['min_words']) && $wordCount < $rules['min_words']) {
            $errors[] = "Jawaban minimal terdiri dari {$rules['min_words']} kata. Saat ini: {$wordCount} kata.";
        }

        if (isset($rules['max_words']) && $wordCount > $rules['max_words']) {
            $errors[] = "Jawaban maksimal terdiri dari {$rules['max_words']} kata. Saat ini: {$wordCount} kata.";
        }
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<int, string>  $errors
     */
    private function validateTableAnswer(Activity $activity, array|string|null $answerJson, array $schema, array &$errors): void
    {
        if ($activity->input_type !== 'table') {
            return;
        }

        if (! is_array($answerJson)) {
            $errors[] = 'Format tabel tidak valid.';

            return;
        }

        $presetRows = $schema['preset_rows'] ?? [];
        $presetCount = is_array($presetRows) ? count($presetRows) : 0;
        $rowCount = count($answerJson);
        $minRows = (int) ($schema['min_rows'] ?? 1);

        if ($rowCount < $minRows) {
            $errors[] = "Tabel minimal harus diisi dengan {$minRows} baris jawaban.";
        }

        if (($schema['allow_add'] ?? true) === false && $presetCount > 0 && $rowCount > $presetCount) {
            $errors[] = 'Tabel ini tidak mengizinkan penambahan baris.';
        }

        if (($schema['allow_delete'] ?? true) === false && $presetCount > 0 && $rowCount < $presetCount) {
            $errors[] = 'Tabel ini tidak mengizinkan penghapusan baris.';
        }

        foreach ($answerJson as $index => $row) {
            if (! is_array($row)) {
                $errors[] = 'Baris '.($index + 1).' tidak valid.';

                continue;
            }

            $this->validateColumns($schema['columns'] ?? [], $row, $presetRows[$index] ?? [], $index, $errors);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $presetRow
     * @param  array<int, string>  $errors
     */
    private function validateColumns(array $columns, array $row, array $presetRow, int $index, array &$errors): void
    {
        foreach ($columns as $column) {
            $name = (string) $column['name'];
            $label = $column['label'] ?? $name;
            $type = $column['type'] ?? 'text';
            $value = $row[$name] ?? null;

            if (($column['required'] ?? false) && $type !== 'computed' && blank(trim((string) $value))) {
                $errors[] = 'Baris '.($index + 1).": Kolom '{$label}' wajib diisi.";
            }

            if ($type === 'select' && filled($value) && ! in_array($value, $column['options'] ?? [], true)) {
                $errors[] = 'Baris '.($index + 1).": Kolom '{$label}' berisi pilihan yang tidak valid.";
            }

            if ($type === 'number' && filled($value) && ! is_numeric($value)) {
                $errors[] = 'Baris '.($index + 1).": Kolom '{$label}' harus berupa angka.";
            }

            if ($type === 'readonly_text' && array_key_exists($name, $presetRow) && (string) $value !== (string) $presetRow[$name]) {
                $errors[] = 'Baris '.($index + 1).": Kolom '{$label}' tidak boleh diubah.";
            }
        }
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<int, string>  $errors
     */
    private function validateFieldAnswer(Activity $activity, array|string|null $answerJson, array $schema, array &$errors): void
    {
        if (! in_array($activity->input_type, ['fields', 'project_form'], true)) {
            return;
        }

        if (! is_array($answerJson) || empty($answerJson)) {
            $errors[] = 'Form tidak boleh kosong.';

            return;
        }

        $row = $answerJson[0] ?? [];

        foreach ($schema['fields'] ?? [] as $field) {
            $name = (string) $field['name'];
            $label = $field['label'] ?? $name;
            $value = is_array($row) ? ($row[$name] ?? null) : null;

            if (($field['required'] ?? false) && blank(trim((string) $value))) {
                $errors[] = "Field '{$label}' wajib diisi.";
            }

            if (($field['type'] ?? 'text') === 'select' && filled($value) && ! in_array($value, $field['options'] ?? [], true)) {
                $errors[] = "Field '{$label}' berisi pilihan yang tidak valid.";
            }

            if (($field['type'] ?? 'text') === 'number' && filled($value) && ! is_numeric($value)) {
                $errors[] = "Field '{$label}' harus berupa angka.";
            }

            if (($field['type'] ?? 'text') === 'readonly_text' && array_key_exists('value', $field) && (string) $value !== (string) $field['value']) {
                $errors[] = "Field '{$label}' tidak boleh diubah.";
            }
        }
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<int, string>  $errors
     */
    private function validateForumAnswer(Activity $activity, array|string|null $answerJson, array $rules, array &$errors): void
    {
        if ($activity->input_type !== 'discussion') {
            return;
        }

        if (($rules['reply_required'] ?? false) && is_array($answerJson) && array_key_exists('reply_count', $answerJson)) {
            $replyCount = (int) $answerJson['reply_count'];
            $minimumReplies = (int) ($rules['min_replies'] ?? 1);

            if ($replyCount < $minimumReplies) {
                $errors[] = "Forum membutuhkan minimal {$minimumReplies} balasan.";
            }
        }
    }
}
