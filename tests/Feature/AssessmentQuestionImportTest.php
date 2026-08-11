<?php

use App\Livewire\Guru\ManageAssessments;
use App\Livewire\Murid\AssessmentPage;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Models\Subject;
use App\Models\User;
use App\Services\Assessment\AssessmentScoringService;
use App\Services\Assessment\QuestionImportService;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $teacher = User::factory()->create();
    $teacher->assignRole('guru');
    $this->teacher = $teacher;

    $subject = Subject::create(['name' => 'IPAS', 'code' => 'IPAS']);
    $module = Module::create([
        'subject_id' => $subject->id,
        'created_by' => $teacher->id,
        'title' => 'Energi Terbarukan',
        'slug' => 'energi-terbarukan',
        'status' => 'published',
    ]);
    $this->module = $module;

    for ($i = 1; $i <= 5; $i++) {
        LearningUnit::create([
            'module_id' => $module->id,
            'title' => "Kegiatan Belajar {$i}",
            'slug' => Str::slug("Kegiatan Belajar {$i}"),
            'order' => $i,
        ]);
    }

    $this->templatePath = resource_path('templates/contoh_template_import.xlsx');
    $this->assertFileExists($this->templatePath);
});

test('imports questions from contoh_template_import.xlsx into correct KBs', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $result = $service->import($file, $this->module->id);

    expect($result['created'])->toBe(50)
        ->and($result['errors'])->toBeEmpty()
        ->and($result['per_kb'])->toHaveCount(5)
        ->and($result['per_kb'][1])->toBe(10)
        ->and($result['per_kb'][2])->toBe(10)
        ->and($result['per_kb'][3])->toBe(10)
        ->and($result['per_kb'][4])->toBe(10)
        ->and($result['per_kb'][5])->toBe(10);

    // Verify assessments were created for each KB
    expect(Assessment::where('module_id', $this->module->id)->count())->toBe(5);
});

test('maps question types correctly from template', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $kb1Assessment = Assessment::where('module_id', $this->module->id)
        ->whereHas('learningUnit', fn ($q) => $q->where('order', 1))
        ->first();

    $questions = $kb1Assessment->questions()->orderBy('order')->get();

    expect($questions->pluck('question_type')->all())->toBe([
        'multiple_choice',
        'multiple_choice',
        'complex_multiple_choice',
        'complex_multiple_choice',
        'true_false',
        'true_false',
        'short_answer',
        'short_answer',
        'matching',
        'matching',
    ]);
});

test('parses correct answer for multiple choice', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $kb1Assessment = Assessment::where('module_id', $this->module->id)
        ->whereHas('learningUnit', fn ($q) => $q->where('order', 1))
        ->first();

    $firstQuestion = $kb1Assessment->questions()->orderBy('order')->first();

    expect($firstQuestion->options)->toBe([
        'A' => 'benda yang hanya terdapat pada alat elektronik',
        'B' => 'kemampuan untuk melakukan kerja atau menyebabkan perubahan',
        'C' => 'bahan bakar yang hanya berasal dari minyak bumi',
        'D' => 'cahaya yang berasal dari matahari',
        'E' => 'listrik yang digunakan untuk menyalakan lampu',
    ])->and($firstQuestion->correct_answer)->toBe(['B'])
        ->and($firstQuestion->question_group)->toBe('pilihan_ganda_biasa');
});

test('parses complex multiple choice answers', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $kb1Assessment = Assessment::where('module_id', $this->module->id)
        ->whereHas('learningUnit', fn ($q) => $q->where('order', 1))
        ->first();

    // Find complex_multiple_choice question in KB1
    $complexQuestion = $kb1Assessment->questions()
        ->where('question_type', 'complex_multiple_choice')
        ->orderBy('order')
        ->first();

    expect($complexQuestion)->not->toBeNull()
        ->and($complexQuestion->options)->toBe([
            'A' => 'Lampu — energi listrik — energi cahaya dan panas',
            'B' => 'Kipas angin — energi listrik — energi gerak dan bunyi',
            'C' => 'Speaker aktif — energi listrik — energi bunyi',
            'D' => 'Buku tulis — energi listrik — energi cahaya',
            'E' => 'Panel surya — energi cahaya matahari — energi listrik',
        ])
        ->and($complexQuestion->correct_answer)->toBe(['A', 'B', 'C', 'E'])
        ->and($complexQuestion->question_group)->toBe('pilihan_ganda_kompleks');
});

test('imports true false answers using the editor contract', function () {
    $file = new UploadedFile($this->templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    app(QuestionImportService::class)->import($file, $this->module->id);

    $questions = Question::where('question_type', 'true_false')
        ->whereHas('assessment', fn ($query) => $query->where('module_id', $this->module->id))
        ->orderBy('id')
        ->get();

    expect($questions)->toHaveCount(10)
        ->and($questions->first()->options)->toBe(['True' => 'Benar', 'False' => 'Salah'])
        ->and($questions->first()->correct_answer)->toBe(['True'])
        ->and($questions->firstWhere('metadata.question_id_template', 'KB2-Q06')?->correct_answer)->toBe(['False'])
        ->and($questions->every(fn (Question $question): bool => $question->question_group === 'benar_salah'))->toBeTrue();
});

test('imports matching prompts options and answers as structured pairs', function () {
    $file = new UploadedFile($this->templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    app(QuestionImportService::class)->import($file, $this->module->id);

    $question = Question::where('question_type', 'matching')
        ->whereHas('assessment', fn ($query) => $query->where('module_id', $this->module->id))
        ->orderBy('id')
        ->firstOrFail();

    expect($question->question_text)->toBe('Pasangkan alat dengan perubahan energi yang terjadi!')
        ->and($question->options)->toBe([
            'left' => [
                '1' => 'Lampu LED',
                '2' => 'Kipas angin',
                '3' => 'Setrika',
                '4' => 'Speaker',
                '5' => 'Charger HP',
            ],
            'right' => [
                'A' => 'Energi listrik menjadi energi gerak dan bunyi',
                'B' => 'Energi listrik menjadi energi cahaya dan panas',
                'C' => 'Energi listrik menjadi energi panas',
                'D' => 'Energi listrik menjadi energi bunyi',
                'E' => 'Energi listrik menjadi energi kimia pada baterai',
            ],
        ])
        ->and($question->correct_answer)->toBe(['1' => 'B', '2' => 'A', '3' => 'C', '4' => 'D', '5' => 'E'])
        ->and($question->question_group)->toBe('menjodohkan');
});

test('scores known correct answers from imported objective questions', function () {
    $file = new UploadedFile($this->templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    app(QuestionImportService::class)->import($file, $this->module->id);

    $questions = Question::whereHas('assessment', fn ($query) => $query->where('module_id', $this->module->id))
        ->get()
        ->keyBy(fn (Question $question): string => $question->metadata['question_id_template']);
    $scoring = app(AssessmentScoringService::class);

    expect($scoring->scoreQuestion($questions['KB1-Q01'], 'B')['score'])->toBe((float) $questions['KB1-Q01']->weight)
        ->and($scoring->scoreQuestion($questions['KB1-Q03'], ['A', 'B', 'C', 'E'])['score'])->toBe((float) $questions['KB1-Q03']->weight)
        ->and($scoring->scoreQuestion($questions['KB1-Q05'], 'True')['score'])->toBe((float) $questions['KB1-Q05']->weight)
        ->and($scoring->scoreQuestion($questions['KB1-Q09'], ['1' => 'B', '2' => 'A', '3' => 'C', '4' => 'D', '5' => 'E'])['score'])->toBe((float) $questions['KB1-Q09']->weight);
});

test('student can render and submit one imported assessment with a full score', function () {
    $file = new UploadedFile($this->templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    app(QuestionImportService::class)->import($file, $this->module->id);

    $assessment = Assessment::where('module_id', $this->module->id)
        ->whereHas('learningUnit', fn ($query) => $query->where('order', 1))
        ->with('questions')
        ->firstOrFail();
    $assessment->update(['is_published' => true]);

    $student = User::factory()->create();
    $student->assignRole('murid');
    AssessmentAttempt::create([
        'assessment_id' => $assessment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'status' => 'sedang_dikerjakan',
        'started_at' => now(),
    ]);
    $component = Livewire::actingAs($student)
        ->test(AssessmentPage::class, ['assessment' => $assessment->id])
        ->assertSee('kemampuan untuk melakukan kerja atau menyebabkan perubahan');

    $component->set('currentGroupIndex', 4)
        ->assertSee('Lampu LED')
        ->assertSee('Energi listrik menjadi energi cahaya dan panas');

    foreach ($assessment->questions as $question) {
        $answer = match ($question->question_type) {
            'multiple_choice', 'true_false' => $question->correct_answer[0],
            'complex_multiple_choice', 'matching' => $question->correct_answer,
            default => $question->reference_answer,
        };
        $component->set("answers.{$question->id}", $answer);
    }

    $component->call('submit')->assertHasNoErrors();

    $attempt = AssessmentAttempt::where('assessment_id', $assessment->id)
        ->where('student_id', $student->id)
        ->firstOrFail();

    expect((float) $attempt->total_score)->toBe((float) $attempt->max_score)
        ->and((float) $attempt->max_score)->toBe((float) $assessment->questions->sum('weight'));
});

test('creates keywords for short answer questions', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $shortAnswerQuestions = Question::where('question_type', 'short_answer')
        ->whereHas('assessment', fn ($q) => $q->where('module_id', $this->module->id))
        ->get();

    expect($shortAnswerQuestions)->not->toBeEmpty();

    foreach ($shortAnswerQuestions as $question) {
        expect($question->reference_answer)->not->toBeNull()
            ->and(QuestionKeyword::where('question_id', $question->id)->count())->toBeGreaterThan(0);
    }
});

test('stores curriculum metadata', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $question = Question::whereHas('assessment', fn ($q) => $q->where('module_id', $this->module->id))
        ->whereNotNull('metadata')
        ->first();

    expect($question)->not->toBeNull()
        ->and($question->metadata)->toBeArray()
        ->and($question->metadata)->toHaveKey('indikator_soal');
});

test('returns errors for invalid KB numbers', function () {
    // Create a CSV with an invalid KB number
    $csv = "question_id,KB,Judul Asesmen,No,Bentuk Soal (Sumber),Tipe Import,Pertanyaan,Opsi A,Opsi B,Opsi C,Opsi D,Opsi E,Kunci / Jawaban Acuan,Bobot Skor,Materi Pokok,Indikator Soal,Literasi,Level Kognitif,Kategori,Taksonomi SOLO,Sumber File\n";
    $csv .= "Q1,99,Test,1,PG,pilihan_ganda,Pertanyaan test,A,B,C,D,,B,1,,,,,,\n";

    $tempPath = tempnam(sys_get_temp_dir(), 'import_test_');
    file_put_contents($tempPath, $csv);
    $file = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);

    $service = app(QuestionImportService::class);
    $result = $service->import($file, $this->module->id);

    expect($result['created'])->toBe(0)
        ->and($result['errors'])->not->toBeEmpty()
        ->and($result['errors'][0])->toContain('KB 99');

    @unlink($tempPath);
});

test('returns errors for invalid question types', function () {
    $csv = "question_id,KB,Judul Asesmen,No,Bentuk Soal (Sumber),Tipe Import,Pertanyaan,Opsi A,Opsi B,Opsi C,Opsi D,Opsi E,Kunci / Jawaban Acuan,Bobot Skor,Materi Pokok,Indikator Soal,Literasi,Level Kognitif,Kategori,Taksonomi SOLO,Sumber File\n";
    $csv .= "Q1,1,Test,1,PG,tipe_aneh,Pertanyaan test,A,B,C,D,,B,1,,,,,,\n";

    $tempPath = tempnam(sys_get_temp_dir(), 'import_test_');
    file_put_contents($tempPath, $csv);
    $file = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);

    $service = app(QuestionImportService::class);
    $result = $service->import($file, $this->module->id);

    expect($result['created'])->toBe(0)
        ->and($result['errors'])->not->toBeEmpty()
        ->and($result['errors'][0])->toContain('tipe_aneh');

    @unlink($tempPath);
});

test('rejects invalid answers for each objective question type before persisting', function () {
    $headers = [
        'question_id', 'KB', 'Judul Asesmen', 'No', 'Bentuk Soal (Sumber)', 'Tipe Import',
        'Pertanyaan', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E',
        'Kunci / Jawaban Acuan', 'Bobot Skor', 'Materi Pokok', 'Indikator Soal',
        'Literasi', 'Level Kognitif', 'Kategori', 'Taksonomi SOLO', 'Sumber File',
    ];
    $rows = [
        ['BAD-PG', 1, 'Test', 1, 'PG', 'pilihan_ganda', 'Pertanyaan PG', 'A', 'B', '', '', '', 'Z', 1],
        ['BAD-TF', 1, 'Test', 2, 'BS', 'benar_salah', 'Pertanyaan BS', '', '', '', '', '', 'Benra', 1],
        ['BAD-MATCH', 1, 'Test', 3, 'Menjodohkan', 'menjodohkan', 'Pertanyaan tanpa kolom pasangan kiri', 'Kanan A', 'Kanan B', '', '', '', '1-A', 1],
    ];
    $tempPath = tempnam(sys_get_temp_dir(), 'invalid_answers_');
    $handle = fopen($tempPath, 'w');
    fputcsv($handle, $headers);
    foreach ($rows as $row) {
        fputcsv($handle, array_pad($row, count($headers), ''));
    }
    fclose($handle);

    $file = new UploadedFile($tempPath, 'invalid.csv', 'text/csv', null, true);
    $result = app(QuestionImportService::class)->import($file, $this->module->id);

    expect($result['created'])->toBe(0)
        ->and($result['updated'])->toBe(0)
        ->and($result['errors'])->toHaveCount(3)
        ->and($result['errors'][0])->toContain('Kunci jawaban tidak tersedia')
        ->and($result['errors'][1])->toContain('tidak valid')
        ->and($result['errors'][2])->toContain('Kolom A')
        ->and(Assessment::where('module_id', $this->module->id)->count())->toBe(0)
        ->and(Question::count())->toBe(0);

    @unlink($tempPath);
});

test('handles empty file gracefully', function () {
    $csv = '';

    $tempPath = tempnam(sys_get_temp_dir(), 'import_test_');
    file_put_contents($tempPath, $csv);
    $file = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);

    $service = app(QuestionImportService::class);
    $result = $service->import($file, $this->module->id);

    expect($result['created'])->toBe(0)
        ->and($result['errors'])->not->toBeEmpty();

    @unlink($tempPath);
});

test('teacher can access question template download', function () {
    $this->actingAs($this->teacher)
        ->get(route('guru.downloads.question-template'))
        ->assertOk()
        ->assertDownload('template-import-soal.xlsx');
});

test('updates imported questions without duplicating them on second import', function () {
    $templatePath = $this->templatePath;

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $file2 = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    $result2 = $service->import($file2, $this->module->id);

    expect(Assessment::where('module_id', $this->module->id)->count())->toBe(5)
        ->and(Question::whereHas('assessment', fn ($query) => $query->where('module_id', $this->module->id))->count())->toBe(50)
        ->and($result2['created'])->toBe(0)
        ->and($result2['updated'])->toBe(50)
        ->and($result2['errors'])->toBeEmpty();
});

test('teacher import UI reports created and updated question counts', function () {
    $this->actingAs($this->teacher);
    $component = Livewire::test(ManageAssessments::class)
        ->set('importModuleId', $this->module->id)
        ->set('importFile', UploadedFile::fake()->createWithContent('contoh_template_import.xlsx', file_get_contents($this->templatePath)))
        ->call('importQuestions')
        ->assertHasNoErrors()
        ->assertSet('importStatus', 'Import berhasil: 50 soal baru. (KB 1: 10 soal, KB 2: 10 soal, KB 3: 10 soal, KB 4: 10 soal, KB 5: 10 soal)');

    $component
        ->set('importFile', UploadedFile::fake()->createWithContent('contoh_template_import.xlsx', file_get_contents($this->templatePath)))
        ->call('importQuestions')
        ->assertHasNoErrors()
        ->assertSet('importStatus', 'Import berhasil: 50 soal diperbarui. (KB 1: 10 soal, KB 2: 10 soal, KB 3: 10 soal, KB 4: 10 soal, KB 5: 10 soal)');

    expect(Question::whereHas('assessment', fn ($query) => $query->where('module_id', $this->module->id))->count())->toBe(50);
});

test('repair command defaults to dry run and normalizes legacy imported questions in place', function () {
    $file = new UploadedFile($this->templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    app(QuestionImportService::class)->import($file, $this->module->id);

    $question = Question::where('question_type', 'multiple_choice')->orderBy('id')->firstOrFail();
    $questionId = $question->id;
    $legacyOptions = collect($question->options)
        ->map(fn (string $text, string $key): array => ['key' => $key, 'text' => $text])
        ->values()
        ->all();
    $question->update(['options' => $legacyOptions, 'question_group' => null]);

    $matchingQuestion = Question::where('question_type', 'matching')->orderBy('id')->firstOrFail();
    $matchingQuestionId = $matchingQuestion->id;
    $matchingOptions = $matchingQuestion->options;
    $legacyMatchingPrompt = $matchingQuestion->question_text."\nKolom A: ".collect($matchingOptions['left'])
        ->map(fn (string $text, string $key): string => "{$key}. {$text}")
        ->implode(' | ');
    $legacyMatchingOptions = collect($matchingOptions['right'])
        ->map(fn (string $text, string $key): array => ['key' => $key, 'text' => $text])
        ->values()
        ->all();
    $matchingQuestion->update([
        'question_text' => $legacyMatchingPrompt,
        'options' => $legacyMatchingOptions,
        'question_group' => null,
    ]);

    $this->artisan('questions:repair-imported-options')->assertSuccessful();

    expect($question->refresh()->options)->toBe($legacyOptions)
        ->and($question->question_group)->toBeNull()
        ->and($matchingQuestion->refresh()->options)->toBe($legacyMatchingOptions)
        ->and($matchingQuestion->question_text)->toBe($legacyMatchingPrompt)
        ->and($matchingQuestion->question_group)->toBeNull();

    $this->artisan('questions:repair-imported-options --apply')->assertSuccessful();

    expect($question->refresh()->id)->toBe($questionId)
        ->and($question->options)->toBe([
            'A' => 'benda yang hanya terdapat pada alat elektronik',
            'B' => 'kemampuan untuk melakukan kerja atau menyebabkan perubahan',
            'C' => 'bahan bakar yang hanya berasal dari minyak bumi',
            'D' => 'cahaya yang berasal dari matahari',
            'E' => 'listrik yang digunakan untuk menyalakan lampu',
        ])
        ->and($question->question_group)->toBe('pilihan_ganda_biasa')
        ->and($matchingQuestion->refresh()->id)->toBe($matchingQuestionId)
        ->and($matchingQuestion->question_text)->not->toContain('Kolom A:')
        ->and($matchingQuestion->options)->toBe($matchingOptions)
        ->and($matchingQuestion->question_group)->toBe('menjodohkan');
});
