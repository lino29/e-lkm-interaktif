<?php

namespace App\Services\Assessment;

use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ImportedQuestionRepairService
{
    public function __construct(
        private readonly QuestionGroupService $questionGroups,
        private readonly QuestionImportService $questionImporter,
    ) {}

    /**
     * @return array{scanned: int, repaired: int, unchanged: int, duplicates: int, errors: array<int, string>}
     */
    public function repair(bool $apply = false, ?int $moduleId = null): array
    {
        $questions = Question::query()
            ->with('assessment:id,module_id')
            ->when($moduleId !== null, fn (Builder $query) => $query->whereHas(
                'assessment',
                fn (Builder $assessmentQuery) => $assessmentQuery->where('module_id', $moduleId),
            ))
            ->orderBy('id')
            ->get();

        $importedQuestions = $questions->filter(
            fn (Question $question): bool => filled(data_get($question->metadata, 'question_id_template')),
        );
        $duplicateCounts = $importedQuestions
            ->countBy(fn (Question $question): string => $question->assessment_id.':'.Str::upper((string) data_get($question->metadata, 'question_id_template')));

        $result = [
            'scanned' => 0,
            'repaired' => 0,
            'unchanged' => 0,
            'duplicates' => $duplicateCounts->filter(fn (int $count): bool => $count > 1)->sum(fn (int $count): int => $count - 1),
            'errors' => [],
        ];

        foreach ($importedQuestions as $question) {
            $result['scanned']++;

            try {
                $payload = $this->buildRepairPayload($question);
            } catch (InvalidArgumentException $exception) {
                $result['errors'][] = "Soal {$question->id}: {$exception->getMessage()}";

                continue;
            }

            $question->fill($payload);
            if (! $question->isDirty()) {
                $result['unchanged']++;

                continue;
            }

            $result['repaired']++;
            if ($apply) {
                DB::transaction(fn () => $question->save());
            }
        }

        return $result;
    }

    /** @return array<string, mixed> */
    private function buildRepairPayload(Question $question): array
    {
        $payload = [
            'question_group' => $this->questionGroups->groupForType($question->question_type),
        ];

        if (in_array($question->question_type, ['multiple_choice', 'complex_multiple_choice'], true)) {
            $payload['options'] = $this->normalizeLegacyChoiceOptions($question->options);

            return $payload;
        }

        if ($question->question_type === 'true_false') {
            $payload['options'] = ['True' => 'Benar', 'False' => 'Salah'];
            $payload['correct_answer'] = [$this->normalizeTrueFalseAnswer($question->correct_answer)];

            return $payload;
        }

        if ($question->question_type === 'matching') {
            $options = $question->options ?? [];
            if (isset($options['left'], $options['right']) && is_array($options['left']) && is_array($options['right'])) {
                return $payload;
            }

            $prompt = $this->questionImporter->parseLegacyMatchingPrompt($question->question_text);
            $payload['question_text'] = $prompt['question_text'];
            $payload['options'] = [
                'left' => $prompt['left'],
                'right' => $this->normalizeLegacyChoiceOptions($options),
            ];

            return $payload;
        }

        if (in_array($question->question_type, ['short_answer', 'essay'], true) && $question->options === null) {
            $payload['options'] = ['use_ai_scoring' => false];
        }

        return $payload;
    }

    /**
     * @param  array<mixed>|null  $options
     * @return array<string, string>
     */
    private function normalizeLegacyChoiceOptions(?array $options): array
    {
        if ($options === null || $options === []) {
            throw new InvalidArgumentException('Opsi jawaban kosong dan tidak dapat diperbaiki otomatis.');
        }

        $normalized = [];
        foreach ($options as $key => $option) {
            if (is_array($option) && filled($option['key'] ?? null) && filled($option['text'] ?? null)) {
                $normalized[Str::upper((string) $option['key'])] = trim((string) $option['text']);

                continue;
            }

            if (is_string($key) && ! is_numeric($key) && is_scalar($option)) {
                $normalized[$key] = trim((string) $option);

                continue;
            }

            throw new InvalidArgumentException('Struktur opsi lama tidak dikenali.');
        }

        return $normalized;
    }

    /** @param array<mixed>|null $correctAnswer */
    private function normalizeTrueFalseAnswer(?array $correctAnswer): string
    {
        $answer = $correctAnswer[0] ?? null;

        if (is_bool($answer)) {
            return $answer ? 'True' : 'False';
        }

        return match (Str::lower(trim((string) $answer))) {
            'benar', 'true', '1' => 'True',
            'salah', 'false', '0' => 'False',
            default => throw new InvalidArgumentException('Kunci benar/salah lama tidak dikenali.'),
        };
    }
}
