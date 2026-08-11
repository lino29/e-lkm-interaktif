<?php

namespace App\Services\Assessment;

use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class QuestionImportService
{
    /** @var array<string, string> */
    private const TYPE_MAP = [
        'pilihan_ganda' => 'multiple_choice',
        'pilihan_ganda_kompleks' => 'complex_multiple_choice',
        'benar_salah' => 'true_false',
        'uraian_singkat' => 'short_answer',
        'uraian' => 'essay',
        'essay' => 'essay',
        'menjodohkan' => 'matching',
    ];

    private const OPTION_COLUMNS = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];

    private const OPTION_KEYS = ['A', 'B', 'C', 'D', 'E'];

    public function __construct(private readonly QuestionGroupService $questionGroups) {}

    /**
     * @return array{created: int, updated: int, per_kb: array<int, int>, errors: array<int, string>}
     */
    public function import(UploadedFile $file, int $moduleId): array
    {
        $module = Module::findOrFail($moduleId);
        $learningUnits = LearningUnit::where('module_id', $module->id)
            ->orderBy('order')
            ->get()
            ->keyBy('order');

        try {
            $rows = $this->parseFile($file);
        } catch (Throwable $exception) {
            report($exception);

            return $this->emptyResult('File tidak dapat dibaca. Pastikan format dan isi file sesuai template.');
        }

        if ($rows === []) {
            return $this->emptyResult('File kosong atau format tidak dikenali.');
        }

        $created = 0;
        $updated = 0;
        $perKb = [];
        $errors = [];
        $assessmentCache = [];
        $assessmentHasSubmittedAttempts = [];
        $questionCache = [];
        $seenTemplateIds = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $kb = (int) ($row['kb'] ?? 0);

            if ($kb <= 0) {
                $errors[] = "Baris {$rowNumber}: Kolom KB tidak valid.";

                continue;
            }

            $learningUnit = $learningUnits->get($kb);
            if ($learningUnit === null) {
                $errors[] = "Baris {$rowNumber}: KB {$kb} tidak ditemukan pada modul '{$module->title}'. Pastikan kegiatan belajar dengan urutan {$kb} sudah ada.";

                continue;
            }

            try {
                $questionData = $this->buildQuestionData($row);
            } catch (InvalidArgumentException $exception) {
                $errors[] = "Baris {$rowNumber}: {$exception->getMessage()}";

                continue;
            }

            $templateId = $questionData['metadata']['question_id_template'];
            if (isset($seenTemplateIds[$templateId])) {
                $errors[] = "Baris {$rowNumber}: question_id '{$templateId}' duplikat dalam file import.";

                continue;
            }

            $seenTemplateIds[$templateId] = true;
            $assessment = $this->findOrCreateAssessment($assessmentCache, $module, $learningUnit, $row);

            if (! isset($questionCache[$assessment->id])) {
                $questionCache[$assessment->id] = $this->loadImportedQuestionCache($assessment);
            }

            $existingQuestions = $questionCache[$assessment->id][$templateId] ?? [];
            if (count($existingQuestions) > 1) {
                $errors[] = "Baris {$rowNumber}: Ditemukan lebih dari satu soal lama dengan question_id '{$templateId}'. Jalankan perintah repair dan periksa duplikat sebelum import ulang.";

                continue;
            }

            $hasSubmittedAttempts = $assessmentHasSubmittedAttempts[$assessment->id]
                ??= $assessment->attempts()->whereNotNull('submitted_at')->exists();

            if ($existingQuestions !== [] && $hasSubmittedAttempts) {
                $errors[] = "Baris {$rowNumber}: Soal '{$templateId}' tidak diperbarui karena asesmen sudah memiliki jawaban yang dikirim murid.";

                continue;
            }

            $payload = [
                ...$questionData,
                'assessment_id' => $assessment->id,
            ];

            DB::transaction(function () use (&$created, &$updated, &$questionCache, $assessment, $templateId, $existingQuestions, $payload): void {
                if ($existingQuestions === []) {
                    $question = Question::create($payload);
                    $questionCache[$assessment->id][$templateId] = [$question];
                    $created++;
                } else {
                    $question = $existingQuestions[0];
                    $question->fill($payload)->save();
                    $updated++;
                }

                $this->syncKeywords($question);
            });

            $perKb[$kb] = ($perKb[$kb] ?? 0) + 1;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'per_kb' => $perKb,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{question_text: string, question_type: string, question_group: string, options: array<mixed>|null, correct_answer: array<mixed>|null, reference_answer: string|null, weight: float, order: int, metadata: array<string, string>}
     */
    private function buildQuestionData(array $row): array
    {
        $templateId = Str::upper(trim((string) ($row['question_id'] ?? '')));
        if ($templateId === '' || mb_strlen($templateId) > 255) {
            throw new InvalidArgumentException('Kolom question_id wajib diisi dan maksimal 255 karakter.');
        }

        $importType = Str::lower(trim((string) ($row['tipe_import'] ?? '')));
        $questionType = self::TYPE_MAP[$importType] ?? null;
        if ($questionType === null) {
            throw new InvalidArgumentException("Tipe import '{$importType}' tidak dikenali. Gunakan: ".implode(', ', array_keys(self::TYPE_MAP)).'.');
        }

        $questionText = trim((string) ($row['pertanyaan'] ?? ''));
        if ($questionText === '') {
            throw new InvalidArgumentException('Kolom pertanyaan kosong.');
        }

        $weightRaw = trim((string) ($row['bobot_skor'] ?? ''));
        if (! is_numeric($weightRaw) || (float) $weightRaw < 0.01 || (float) $weightRaw > 9999.99) {
            throw new InvalidArgumentException('Bobot skor harus berupa angka antara 0.01 dan 9999.99.');
        }

        $orderRaw = trim((string) ($row['no'] ?? ''));
        if (filter_var($orderRaw, FILTER_VALIDATE_INT) === false || (int) $orderRaw < 1 || (int) $orderRaw > 65535) {
            throw new InvalidArgumentException('Kolom No harus berupa bilangan bulat antara 1 dan 65535.');
        }

        $options = $this->buildOptions($row, $questionType, $questionText);
        if ($questionType === 'matching') {
            $questionText = $options['question_text'];
            $options = $options['options'];
        }

        $rawAnswer = (string) ($row['kunci/_jawaban_acuan'] ?? $row['kunci_/_jawaban_acuan'] ?? '');
        $correctAnswer = $this->parseCorrectAnswer($rawAnswer, $questionType);
        $referenceAnswer = $this->buildReferenceAnswer($rawAnswer, $questionType);

        $this->validateAnswerContract($questionType, $options, $correctAnswer, $referenceAnswer);

        return [
            'question_text' => $questionText,
            'question_type' => $questionType,
            'question_group' => $this->questionGroups->groupForType($questionType),
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'reference_answer' => $referenceAnswer,
            'weight' => (float) $weightRaw,
            'order' => (int) $orderRaw,
            'metadata' => [
                ...$this->buildMetadata($row),
                'question_id_template' => $templateId,
            ],
        ];
    }

    /**
     * @return array{created: int, updated: int, per_kb: array<int, int>, errors: array<int, string>}
     */
    private function emptyResult(string $error): array
    {
        return ['created' => 0, 'updated' => 0, 'per_kb' => [], 'errors' => [$error]];
    }

    /** @return array<int, array<string, string|null>> */
    private function parseFile(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        return match ($extension) {
            'xlsx', 'xls' => $this->parseExcel($file),
            'csv', 'txt' => $this->parseCsv($file),
            default => throw new InvalidArgumentException('Ekstensi file tidak didukung.'),
        };
    }

    /** @return array<int, array<string, string|null>> */
    private function parseExcel(UploadedFile $file): array
    {
        $worksheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $rawRows = $worksheet->toArray(null, true, true, false);

        return $this->mapRows($rawRows);
    }

    /** @return array<int, array<string, string|null>> */
    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return [];
        }

        $rawRows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rawRows[] = $data;
        }

        fclose($handle);

        return $this->mapRows($rawRows);
    }

    /**
     * @param  array<int, array<int, mixed>>  $rawRows
     * @return array<int, array<string, string|null>>
     */
    private function mapRows(array $rawRows): array
    {
        if (count($rawRows) < 2) {
            return [];
        }

        $header = array_map(
            fn (mixed $column): string => $this->normalizeHeader((string) $column),
            array_shift($rawRows),
        );

        if (in_array('', $header, true) || count($header) !== count(array_unique($header))) {
            throw new InvalidArgumentException('Header file kosong atau duplikat.');
        }

        $rows = [];
        foreach ($rawRows as $rawRow) {
            $values = array_slice(array_pad($rawRow, count($header), null), 0, count($header));
            $values = array_map(fn (mixed $value): ?string => $value !== null ? trim((string) $value) : null, $values);
            $mapped = array_combine($header, $values);

            if (! $this->isEmptyRow($mapped)) {
                $rows[] = $mapped;
            }
        }

        return $rows;
    }

    private function normalizeHeader(string $value): string
    {
        return Str::snake(Str::ascii(Str::lower(trim($value))));
    }

    /** @param array<string, string|null> $row */
    private function isEmptyRow(array $row): bool
    {
        return collect($row)->every(fn (?string $value): bool => $value === null || trim($value) === '');
    }

    /**
     * @param  array<string, Assessment>  $cache
     * @param  array<string, string|null>  $row
     */
    private function findOrCreateAssessment(array &$cache, Module $module, LearningUnit $learningUnit, array $row): Assessment
    {
        $cacheKey = $module->id.'-'.$learningUnit->id;

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $assessment = Assessment::where('module_id', $module->id)
            ->where('learning_unit_id', $learningUnit->id)
            ->first();

        if ($assessment === null) {
            $title = trim((string) ($row['judul_asesmen'] ?? ''));
            $assessment = Assessment::create([
                'module_id' => $module->id,
                'learning_unit_id' => $learningUnit->id,
                'title' => $title !== '' ? $title : "Asesmen Formatif {$learningUnit->title}",
                'type' => 'formative',
                'kktp' => 75,
                'max_attempts' => 2,
                'is_published' => false,
                'order' => $learningUnit->order,
            ]);
        }

        return $cache[$cacheKey] = $assessment;
    }

    /**
     * @return array<string, array<int, Question>>
     */
    private function loadImportedQuestionCache(Assessment $assessment): array
    {
        $cache = [];

        foreach ($assessment->questions()->get() as $question) {
            $templateId = Str::upper(trim((string) data_get($question->metadata, 'question_id_template', '')));
            if ($templateId !== '') {
                $cache[$templateId][] = $question;
            }
        }

        return $cache;
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<mixed>|null
     */
    private function buildOptions(array $row, string $questionType, string $questionText): ?array
    {
        if (in_array($questionType, ['short_answer', 'essay'], true)) {
            return ['use_ai_scoring' => false];
        }

        if ($questionType === 'true_false') {
            return ['True' => 'Benar', 'False' => 'Salah'];
        }

        $rightOptions = $this->buildChoiceOptions($row);
        if ($questionType !== 'matching') {
            return $rightOptions;
        }

        $matchingPrompt = $this->parseLegacyMatchingPrompt($questionText);

        return [
            'question_text' => $matchingPrompt['question_text'],
            'options' => [
                'left' => $matchingPrompt['left'],
                'right' => $rightOptions,
            ],
        ];
    }

    /** @param array<string, string|null> $row
     * @return array<string, string>
     */
    private function buildChoiceOptions(array $row): array
    {
        $options = [];

        foreach (self::OPTION_COLUMNS as $index => $column) {
            $text = trim((string) ($row[$column] ?? ''));
            if ($text !== '') {
                $options[self::OPTION_KEYS[$index]] = $text;
            }
        }

        return $options;
    }

    /**
     * @return array{question_text: string, left: array<string, string>}
     */
    public function parseLegacyMatchingPrompt(string $questionText): array
    {
        $parts = preg_split('/\R\s*Kolom A\s*:\s*/iu', trim($questionText), 2);
        if (! is_array($parts) || count($parts) !== 2 || trim($parts[0]) === '' || trim($parts[1]) === '') {
            throw new InvalidArgumentException('Soal menjodohkan harus memuat daftar kiri setelah teks "Kolom A:".');
        }

        $leftOptions = [];
        foreach (preg_split('/\s*\|\s*/u', trim($parts[1])) ?: [] as $item) {
            if (preg_match('/^\s*(\d+)[.)]\s*(.+?)\s*$/u', $item, $matches) !== 1) {
                throw new InvalidArgumentException("Format pasangan kiri '{$item}' tidak valid.");
            }

            $key = $matches[1];
            if (isset($leftOptions[$key])) {
                throw new InvalidArgumentException("Kunci pasangan kiri '{$key}' duplikat.");
            }

            $leftOptions[$key] = trim($matches[2]);
        }

        if ($leftOptions === []) {
            throw new InvalidArgumentException('Soal menjodohkan tidak memiliki pasangan kiri.');
        }

        return ['question_text' => trim($parts[0]), 'left' => $leftOptions];
    }

    /** @return array<int|string, mixed>|null */
    private function parseCorrectAnswer(string $raw, string $questionType): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        return match ($questionType) {
            'multiple_choice' => [Str::upper($raw)],
            'complex_multiple_choice' => array_values(array_unique(array_filter(array_map(
                fn (string $answer): string => Str::upper(trim($answer)),
                explode(',', $raw),
            )))),
            'true_false' => $this->parseTrueFalseAnswer($raw),
            'matching' => $this->parseMatchingAnswer($raw),
            'short_answer', 'essay' => null,
            default => null,
        };
    }

    /** @return array{0: string} */
    private function parseTrueFalseAnswer(string $raw): array
    {
        return match (Str::lower(trim($raw))) {
            'benar', 'true' => ['True'],
            'salah', 'false' => ['False'],
            default => throw new InvalidArgumentException("Kunci benar/salah '{$raw}' tidak valid. Gunakan Benar atau Salah."),
        };
    }

    /** @return array<string, string> */
    private function parseMatchingAnswer(string $raw): array
    {
        $result = [];

        foreach (array_map('trim', explode(',', $raw)) as $pair) {
            $parts = array_map('trim', explode('-', $pair, 2));
            if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
                throw new InvalidArgumentException("Pasangan kunci '{$pair}' tidak valid.");
            }

            $leftKey = $parts[0];
            if (isset($result[$leftKey])) {
                throw new InvalidArgumentException("Kunci pasangan '{$leftKey}' duplikat.");
            }

            $result[$leftKey] = Str::upper($parts[1]);
        }

        return $result;
    }

    private function buildReferenceAnswer(string $raw, string $questionType): ?string
    {
        if (! in_array($questionType, ['short_answer', 'essay'], true)) {
            return null;
        }

        $answer = trim($raw);

        return $answer !== '' ? $answer : null;
    }

    /**
     * @param  array<mixed>|null  $options
     * @param  array<mixed>|null  $correctAnswer
     */
    private function validateAnswerContract(string $questionType, ?array $options, ?array $correctAnswer, ?string $referenceAnswer): void
    {
        if (in_array($questionType, ['short_answer', 'essay'], true)) {
            if ($referenceAnswer === null) {
                throw new InvalidArgumentException('Jawaban acuan wajib diisi untuk uraian.');
            }

            return;
        }

        if ($correctAnswer === null || $correctAnswer === []) {
            throw new InvalidArgumentException('Kunci jawaban wajib diisi.');
        }

        if ($questionType === 'true_false') {
            return;
        }

        if ($questionType === 'matching') {
            $leftKeys = array_keys($options['left'] ?? []);
            $rightKeys = array_keys($options['right'] ?? []);

            if ($leftKeys === [] || $rightKeys === []) {
                throw new InvalidArgumentException('Soal menjodohkan wajib memiliki pasangan kiri dan kanan.');
            }

            if (array_keys($correctAnswer) !== $leftKeys || array_diff(array_values($correctAnswer), $rightKeys) !== []) {
                throw new InvalidArgumentException('Kunci menjodohkan harus mencakup seluruh pasangan kiri dan menunjuk opsi kanan yang tersedia.');
            }

            return;
        }

        $optionKeys = array_keys($options ?? []);
        if (count($optionKeys) < 2) {
            throw new InvalidArgumentException('Pilihan ganda wajib memiliki minimal dua opsi.');
        }

        if ($questionType === 'multiple_choice' && count($correctAnswer) !== 1) {
            throw new InvalidArgumentException('Pilihan ganda biasa harus memiliki tepat satu kunci jawaban.');
        }

        if (array_diff($correctAnswer, $optionKeys) !== []) {
            throw new InvalidArgumentException('Kunci jawaban tidak tersedia pada kolom opsi.');
        }
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, string>
     */
    private function buildMetadata(array $row): array
    {
        return array_filter([
            'materi_pokok' => $row['materi_pokok'] ?? null,
            'indikator_soal' => $row['indikator_soal'] ?? null,
            'literasi' => $row['literasi'] ?? null,
            'level_kognitif' => $row['level_kognitif'] ?? null,
            'kategori' => $row['kategori'] ?? null,
            'taksonomi_solo' => $row['taksonomi_solo'] ?? null,
            'sumber_file' => $row['sumber_file'] ?? null,
            'bentuk_soal' => $row['bentuk_soal(sumber)'] ?? $row['bentuk_soal_(sumber)'] ?? $row['bentuk_soal_sumber'] ?? null,
        ], fn (mixed $value): bool => $value !== null && trim((string) $value) !== '');
    }

    private function syncKeywords(Question $question): void
    {
        $question->keywords()->delete();

        if (! in_array($question->question_type, ['short_answer', 'essay'], true) || blank($question->reference_answer)) {
            return;
        }

        $question->keywords()->createMany(array_map(
            fn (string $keyword): array => ['keyword' => $keyword, 'weight' => 1],
            $this->extractKeywords($question->reference_answer),
        ));
    }

    /** @return array<int, string> */
    private function extractKeywords(string $text): array
    {
        $stopWords = [
            'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'adalah', 'untuk',
            'dengan', 'pada', 'tidak', 'akan', 'atau', 'juga', 'dapat', 'bisa',
            'serta', 'karena', 'oleh', 'sebagai', 'dalam', 'secara', 'agar',
            'lebih', 'ada', 'harus', 'telah', 'sudah', 'maka', 'tersebut',
            'suatu', 'saat', 'jika', 'bila', 'ialah', 'yakni', 'yaitu',
            'apakah', 'bagaimana', 'mengapa', 'dimana', 'kemudian', 'lalu',
            'tetapi', 'namun', 'maupun', 'bahwa', 'seperti', 'antara',
            'tanpa', 'melalui', 'tentang', 'sebelum', 'sesudah', 'terhadap',
            'masih', 'sangat', 'belum', 'sehingga', 'berupa', 'disebut',
            'contoh', 'jawaban', 'misalnya',
        ];

        $words = preg_split('/[\s,.:;!?\-\/\\\\()]+/', Str::lower($text));
        $words = array_filter($words ?: [], fn (string $word): bool => mb_strlen($word) >= 3 && ! in_array($word, $stopWords, true));

        return array_values(array_unique(array_slice($words, 0, 10)));
    }
}
