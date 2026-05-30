<?php

namespace App\Livewire\Guru;

use App\Models\Module;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ManageProjects extends Component
{
    use WithPagination;

    /**
     * @var array<int, string>
     */
    public array $projectRubric = [
        'Identifikasi masalah',
        'Kesesuaian solusi',
        'Kelengkapan rancangan',
        'Data pengamatan',
        'Keselamatan kerja',
        'Kreativitas',
        'Kelayakan',
        'Komunikasi hasil',
    ];

    public ?int $reviewingProjectId = null;

    public ?float $score = null;

    public ?string $feedback = null;

    public $filterModule = '';

    public $filterStatus = '';

    public function review(int $projectId): void
    {
        $project = $this->teacherProjectQuery()->findOrFail($projectId);

        $this->reviewingProjectId = $project->id;
        $this->score = $project->score === null ? null : (float) $project->score;
        $this->feedback = $project->feedback;
    }

    public function saveReview(): void
    {
        $validated = $this->validate([
            'reviewingProjectId' => ['required', 'integer'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $project = $this->teacherProjectQuery()->findOrFail($validated['reviewingProjectId']);

        $project->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'],
            'status' => 'reviewed',
        ]);

        $this->reset(['reviewingProjectId', 'score', 'feedback']);
        session()->flash('status', 'Proyek berhasil dinilai.');
    }

    public function downloadFile(int $projectId)
    {
        $project = $this->teacherProjectQuery()->findOrFail($projectId);

        if ($project->file_path && Storage::disk('public')->exists($project->file_path)) {
            return Storage::disk('public')->download($project->file_path);
        }

        session()->flash('error', 'File tidak ditemukan.');
    }

    public function render()
    {
        $modules = Module::where('created_by', auth()->id())->get();

        $query = $this->teacherProjectQuery();

        if ($this->filterModule !== '') {
            $query->where('module_id', $this->filterModule);
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.guru.manage-projects', [
            'projects' => $query->latest()->paginate(10),
            'modules' => $modules,
            'projectRubric' => $this->projectRubric,
        ]);
    }

    private function teacherProjectQuery()
    {
        return Project::with('module', 'user')
            ->whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'));
    }
}
