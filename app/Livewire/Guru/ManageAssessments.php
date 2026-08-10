<?php

namespace App\Livewire\Guru;

use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\Module;
use App\Services\Assessment\QuestionImportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageAssessments extends Component
{
    use WithFileUploads;

    public ?int $editingAssessmentId = null;

    public ?int $module_id = null;

    public ?int $learning_unit_id = null;

    public string $title = '';

    public string $type = 'formative';

    public ?string $description = null;

    public int $kktp = 75;

    public int $max_attempts = 2;

    public bool $is_published = false;

    public int $order = 1;

    public ?int $importModuleId = null;

    public $importFile;

    /** @var array<int, string> */
    public array $importErrors = [];

    public ?string $importStatus = null;

    public function updatedModuleId(): void
    {
        $this->learning_unit_id = null;
    }

    public function save(): void
    {
        $moduleIds = Module::where('created_by', auth()->id())->pluck('id')->all();
        $validated = $this->validate([
            'module_id' => ['required', Rule::in($moduleIds)],
            'learning_unit_id' => ['nullable', Rule::exists('learning_units', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['formative', 'final'])],
            'description' => ['nullable', 'string'],
            'kktp' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'is_published' => ['boolean'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['learning_unit_id'] && ! LearningUnit::where('module_id', $validated['module_id'])->whereKey($validated['learning_unit_id'])->exists()) {
            $this->addError('learning_unit_id', 'Kegiatan belajar harus berasal dari modul yang dipilih.');

            return;
        }

        $assessment = $this->editingAssessmentId
            ? Assessment::whereIn('module_id', $moduleIds)->findOrFail($this->editingAssessmentId)
            : new Assessment;

        $assessment->fill($validated)->save();
        $wasEditing = filled($this->editingAssessmentId);

        $this->resetForm();
        session()->flash('status', $wasEditing ? 'Asesmen berhasil diperbarui.' : 'Asesmen berhasil dibuat.');
    }

    public function edit(int $assessmentId): void
    {
        $assessment = $this->teacherAssessmentQuery()->findOrFail($assessmentId);

        $this->editingAssessmentId = $assessment->id;
        $this->module_id = $assessment->module_id;
        $this->learning_unit_id = $assessment->learning_unit_id;
        $this->title = $assessment->title;
        $this->type = $assessment->type;
        $this->description = $assessment->description;
        $this->kktp = $assessment->kktp;
        $this->max_attempts = $assessment->max_attempts;
        $this->is_published = $assessment->is_published;
        $this->order = $assessment->order;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function togglePublish(int $assessmentId): void
    {
        $assessment = $this->teacherAssessmentQuery()->findOrFail($assessmentId);
        $assessment->update(['is_published' => ! $assessment->is_published]);

        session()->flash('status', $assessment->is_published ? 'Asesmen dipublikasikan.' : 'Asesmen disembunyikan dari murid.');
    }

    public function delete(int $assessmentId): void
    {
        $this->teacherAssessmentQuery()->findOrFail($assessmentId)->delete();
        $this->resetForm();

        session()->flash('status', 'Asesmen berhasil dihapus.');
    }

    public function importQuestions(QuestionImportService $importer): void
    {
        $this->importErrors = [];
        $this->importStatus = null;

        $moduleIds = Module::where('created_by', auth()->id())->pluck('id')->all();

        $this->validate([
            'importModuleId' => ['required', Rule::in($moduleIds)],
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ], [
            'importModuleId.required' => 'Pilih modul tujuan import.',
            'importFile.required' => 'Pilih file Excel/CSV untuk diimport.',
            'importFile.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
            'importFile.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $result = $importer->import($this->importFile, $this->importModuleId);

        $this->reset('importFile');
        $this->dispatch('close-modal', 'import-questions-modal');

        if ($result['errors'] !== []) {
            $this->importErrors = $result['errors'];
        }

        $perKbSummary = collect($result['per_kb'])
            ->map(fn (int $count, int $kb) => "KB {$kb}: {$count} soal")
            ->implode(', ');

        if ($result['created'] > 0) {
            $this->importStatus = "{$result['created']} soal berhasil diimport.".($perKbSummary ? " ({$perKbSummary})" : '');
            session()->flash('status', $this->importStatus);
        } elseif ($result['errors'] !== []) {
            session()->flash('error', 'Import gagal. Periksa daftar error di bawah.');
        } else {
            session()->flash('error', 'Tidak ada soal yang berhasil diimport.');
        }
    }

    public function render()
    {
        $modules = Module::where('created_by', auth()->id())->orderBy('title')->get();
        $moduleIds = $modules->pluck('id');
        $assessments = Assessment::with('module', 'learningUnit')
            ->withCount(['questions', 'attempts'])
            ->whereIn('module_id', $moduleIds)
            ->orderBy('module_id')
            ->orderBy('order')
            ->latest()
            ->get();

        return view('livewire.guru.manage-assessments', [
            'modules' => $modules,
            'learningUnits' => LearningUnit::whereIn('module_id', $moduleIds)->orderBy('title')->get(),
            'formLearningUnits' => LearningUnit::query()
                ->when($this->module_id, fn ($query) => $query->where('module_id', $this->module_id), fn ($query) => $query->whereIn('module_id', $moduleIds))
                ->orderBy('title')
                ->get(),
            'assessments' => $assessments,
            'publishedCount' => $assessments->where('is_published', true)->count(),
            'draftCount' => $assessments->where('is_published', false)->count(),
            'questionCount' => $assessments->sum('questions_count'),
            'attemptCount' => $assessments->sum('attempts_count'),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingAssessmentId', 'module_id', 'learning_unit_id', 'title', 'description', 'is_published']);
        $this->type = 'formative';
        $this->kktp = 75;
        $this->max_attempts = 2;
        $this->order = 1;
    }

    private function teacherAssessmentQuery(): Builder
    {
        return Assessment::whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'));
    }
}
