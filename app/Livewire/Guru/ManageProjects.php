<?php

namespace App\Livewire\Guru;

use App\Models\Module;
use App\Models\Project;
use Livewire\Component;

class ManageProjects extends Component
{
    public ?int $reviewingProjectId = null;

    public ?float $score = null;

    public ?string $feedback = null;

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

    public function render()
    {
        return view('livewire.guru.manage-projects', [
            'projects' => $this->teacherProjectQuery()->latest()->get(),
        ]);
    }

    private function teacherProjectQuery()
    {
        return Project::with('module', 'user')
            ->whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'));
    }
}
