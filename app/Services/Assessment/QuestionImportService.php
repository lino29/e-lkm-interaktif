<?php

namespace App\Services\Assessment;

use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuestionImportService
{
    /**
     * Mapping from template tipe_import values to system question_type values.
     */
    private const TYPE_MAP = [
        'pilihan_ganda' => 'multiple_choice',
        'pilihan_ganda_kompleks' => 'complex_multiple_choice',
        'benar_salah' => 'true_false',
        'uraian_singkat' => 'short_answer',
        'menjodohkan' => 'matching',
    ];

    private const OPTION_COLUMNS = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];

    /**
     * @return array{created: int, per_kb: array<int, int>, errors: array<int, string>}
     */
    public function import(UploadedFile $file, int $moduleId): array
    {
        $module = Module::findOrFail($moduleId);
        $learningUnits = LearningUnit::where('module_id', $module->id)
            ->orderBy('order')
            ->get()
            ->keyBy('order');

        $rows = $this->parseFile($file);

        if ($rows === []) {
            return ['created' => 0, 'per_kb' => [], 'errors' => ['File kosong atau format tidak dikenali.']];
        }

        $created = 0;
        $perKb = [];
        $errors = [];
        $assessmentCache = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // 1-indexed, skip header

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

            $tipeImport = trim((string) ($row['tipe_import'] ?? ''));
            $questionType = self::TYPE_MAP[$tipeImport] ?? null;
            if ($questionType === null) {
                $errors[] = "Baris {$rowNumber}: Tipe import '{$tipeImport}' tidak dikenali. Gunakan: ".implode(', ', array_keys(self::TYPE_MAP)).'.';

                continue;
            }

            $questionText = trim((string) ($row['pertanyaan'] ?? ''));
            if ($questionText === '') {
                $errors[] = "Baris {$rowNumber}: Kolom pertanyaan kosong.";

                continue;
            }

            $assessment = $this->findOrCreateAssessment($assessmentCache, $module, $learningUnit, $row);

            $options = $this->buildOptions($row, $questionType);
            $correctAnswer = $this->parseCorrectAnswer($row['kunci/_jawaban_acuan'] ?? $row['kunci_/_jawaban_acuan'] ?? '', $questionType);
            $referenceAnswer = $this->buildReferenceAnswer($row, $questionType);
            $weight = max(0.01, (float) ($row['bobot_skor'] ?? 1));
            $order = max(1, (int) ($row['no'] ?? 1));
            $metadata = $this->buildMetadata($row);

            $question = Question::create([
                'assessment_id' => $assessment->id,
                'question_text' => $questionText,
                'question_type' => $questionType,
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'reference_answer' => $referenceAnswer,
                'weight' => $weight,
                'order' => $order,
                'metadata' => $metadata,
            ]);

            $this->createKeywordsIfNeeded($question, $referenceAnswer, $questionType);

            $created++;
            $perKb[$kb] = ($perKb[$kb] ?? 0) + 1;
        }

        return ['created' => $created, 'per_kb' => $perKb, 'errors' => $errors];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            return $this->parseExcel($file);
        }

        return $this->parseCsv($file);
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function parseExcel(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rawRows = $worksheet->toArray(null, true, true, false);

        if (count($rawRows) < 2) {
            return [];
        }

        $header = array_map(
            fn (?string $col): string => $this->normalizeHeader((string) $col),
            array_shift($rawRows),
        );

        $rows = [];
        foreach ($rawRows as $rawRow) {
            $mapped = array_combine($header, array_pad(array_map(fn ($v) => $v !== null ? trim((string) $v) : null, $rawRow), count($header), null));

            if ($this->isEmptyRow($mapped)) {
                continue;
            }

            $rows[] = $mapped;
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return [];
        }

        $headerRaw = fgetcsv($handle, 0, ',');
        if ($headerRaw === false) {
            fclose($handle);

            return [];
        }

        $header = array_map(fn (?string $col): string => $this->normalizeHeader((string) $col), $headerRaw);

        $rows = [];
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $mapped = array_combine($header, array_pad(array_map(fn ($v) => $v !== null ? trim((string) $v) : null, $data), count($header), null));

            if ($this->isEmptyRow($mapped)) {
                continue;
            }

            $rows[] = $mapped;
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeHeader(string $value): string
    {
        return Str::snake(
            Str::ascii(
                strtolower(trim($value))
            )
        );
    }

    /**
     * @param  array<string, string|null>  $row
     */
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
            if ($title === '') {
                $title = "Asesmen Formatif {$learningUnit->title}";
            }

            $assessment = Assessment::create([
                'module_id' => $module->id,
                'learning_unit_id' => $learningUnit->id,
                'title' => $title,
                'type' => 'formative',
                'kktp' => 75,
                'max_attempts' => 2,
                'is_published' => false,
                'order' => $learningUnit->order,
            ]);
        }

        $cache[$cacheKey] = $assessment;

        return $assessment;
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<int, array{key: string, text: string}>|null
     */
    private function buildOptions(array $row, string $questionType): ?array
    {
        if (in_array($questionType, ['short_answer', 'true_false'], true)) {
            return null;
        }

        $optionLetters = ['A', 'B', 'C', 'D', 'E'];
        $options = [];

        foreach (self::OPTION_COLUMNS as $index => $column) {
            $text = trim((string) ($row[$column] ?? ''));
            if ($text !== '') {
                $options[] = ['key' => $optionLetters[$index], 'text' => $text];
            }
        }

        return $options ?: null;
    }

    /**
     * @return array<int|string, mixed>|null
     */
    private function parseCorrectAnswer(string $raw, string $questionType): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        return match ($questionType) {
            'multiple_choice' => [trim($raw)],
            'complex_multiple_choice' => array_values(array_map('trim', explode(',', $raw))),
            'true_false' => [Str::lower($raw) === 'benar' || Str::lower($raw) === 'true'],
            'matching' => $this->parseMatchingAnswer($raw),
            'short_answer' => null,
            default => null,
        };
    }

    /**
     * Parse matching answer format like "1-B, 2-C, 3-D, 4-E, 5-A".
     *
     * @return array<string, string>
     */
    private function parseMatchingAnswer(string $raw): array
    {
        $pairs = array_map('trim', explode(',', $raw));
        $result = [];

        foreach ($pairs as $pair) {
            $parts = array_map('trim', explode('-', $pair, 2));
            if (count($parts) === 2 && $parts[0] !== '' && $parts[1] !== '') {
                $result[$parts[0]] = $parts[1];
            }
        }

        return $result;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    private function buildReferenceAnswer(array $row, string $questionType): ?string
    {
        if (! in_array($questionType, ['short_answer', 'essay'], true)) {
            return null;
        }

        $raw = trim((string) ($row['kunci/_jawaban_acuan'] ?? $row['kunci_/_jawaban_acuan'] ?? ''));

        return $raw !== '' ? $raw : null;
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, string|null>
     */
    private function buildMetadata(array $row): array
    {
        return array_filter([
            'question_id_template' => $row['question_id'] ?? null,
            'materi_pokok' => $row['materi_pokok'] ?? null,
            'indikator_soal' => $row['indikator_soal'] ?? null,
            'literasi' => $row['literasi'] ?? null,
            'level_kognitif' => $row['level_kognitif'] ?? null,
            'kategori' => $row['kategori'] ?? null,
            'taksonomi_solo' => $row['taksonomi_solo'] ?? null,
            'sumber_file' => $row['sumber_file'] ?? null,
            'bentuk_soal' => $row['bentuk_soal(sumber)'] ?? $row['bentuk_soal_(sumber)'] ?? $row['bentuk_soal_sumber'] ?? null,
        ], fn ($value) => $value !== null && trim((string) $value) !== '');
    }

    private function createKeywordsIfNeeded(Question $question, ?string $referenceAnswer, string $questionType): void
    {
        if (! in_array($questionType, ['short_answer', 'essay'], true)) {
            return;
        }

        if ($referenceAnswer === null || trim($referenceAnswer) === '') {
            return;
        }

        $keywords = $this->extractKeywords($referenceAnswer);

        foreach ($keywords as $keyword) {
            QuestionKeyword::create([
                'question_id' => $question->id,
                'keyword' => $keyword,
                'weight' => 1,
            ]);
        }
    }

    /**
     * Extract meaningful keywords from a reference answer text.
     *
     * @return array<int, string>
     */
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
        ];

        $words = preg_split('/[\s,.:;!?\-\/\\\\()]+/', Str::lower($text));
        $words = array_filter($words, fn (string $word): bool => mb_strlen($word) >= 3 && ! in_array($word, $stopWords, true));

        return array_values(array_unique(array_slice($words, 0, 10)));
    }
}
