<?php

use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionKeyword;
use App\Models\Subject;
use App\Models\User;
use App\Services\Assessment\QuestionImportService;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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
});

test('imports questions from contoh_template_import.xlsx into correct KBs', function () {
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

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
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $kb1Assessment = Assessment::where('module_id', $this->module->id)
        ->whereHas('learningUnit', fn ($q) => $q->where('order', 1))
        ->first();

    $questions = $kb1Assessment->questions()->orderBy('order')->get();

    // KB1 row 2-3 are multiple_choice
    expect($questions[0]->question_type)->toBe('multiple_choice')
        ->and($questions[1]->question_type)->toBe('multiple_choice');
});

test('parses correct answer for multiple choice', function () {
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    $kb1Assessment = Assessment::where('module_id', $this->module->id)
        ->whereHas('learningUnit', fn ($q) => $q->where('order', 1))
        ->first();

    $firstQuestion = $kb1Assessment->questions()->orderBy('order')->first();

    // KB1-Q01 correct answer is B
    expect($firstQuestion->correct_answer)->toBe(['B']);
});

test('parses complex multiple choice answers', function () {
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

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
        ->and($complexQuestion->correct_answer)->toBeArray()
        ->and(count($complexQuestion->correct_answer))->toBeGreaterThan(1);
});

test('creates keywords for short answer questions', function () {
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

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
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

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

test('does not duplicate assessments on second import', function () {
    $templatePath = base_path('docs/contoh_template_import.xlsx');

    if (! file_exists($templatePath)) {
        $this->markTestSkipped('Template file not found at docs/contoh_template_import.xlsx');
    }

    $file = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $service = app(QuestionImportService::class);
    $service->import($file, $this->module->id);

    // Import again
    $file2 = new UploadedFile($templatePath, 'contoh_template_import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    $result2 = $service->import($file2, $this->module->id);

    // Should still have only 5 assessments (not 10)
    expect(Assessment::where('module_id', $this->module->id)->count())->toBe(5)
        ->and($result2['created'])->toBe(50);
});
