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
     * @var array<int, array{key: string, criterion: string, indicator: string, max_score: int}>
     */
    public array $projectRubric = [
        [
            'key' => 'identifikasi_masalah',
            'criterion' => 'Identifikasi masalah',
            'indicator' => 'Masalah energi nyata dijelaskan dengan bukti awal dan konteks lingkungan.',
            'max_score' => 12,
        ],
        [
            'key' => 'kesesuaian_solusi',
            'criterion' => 'Kesesuaian solusi',
            'indicator' => 'Solusi hemat energi atau energi terbarukan sesuai dengan masalah yang dipilih.',
            'max_score' => 13,
        ],
        [
            'key' => 'kelengkapan_rancangan',
            'criterion' => 'Kelengkapan rancangan',
            'indicator' => 'Tujuan, alat bahan, langkah kerja, dan hasil yang diharapkan ditulis lengkap.',
            'max_score' => 13,
        ],
        [
            'key' => 'data_pengamatan',
            'criterion' => 'Data pengamatan',
            'indicator' => 'Data yang dikumpulkan relevan, dapat diperiksa, dan dipakai untuk menarik kesimpulan.',
            'max_score' => 12,
        ],
        [
            'key' => 'keselamatan_kerja',
            'criterion' => 'Keselamatan kerja',
            'indicator' => 'Rancangan mempertimbangkan risiko alat, bahan, listrik, panas, atau lingkungan kerja.',
            'max_score' => 12,
        ],
        [
            'key' => 'kreativitas',
            'criterion' => 'Kreativitas',
            'indicator' => 'Ada gagasan baru atau adaptasi solusi sederhana yang sesuai kemampuan murid.',
            'max_score' => 13,
        ],
        [
            'key' => 'kelayakan',
            'criterion' => 'Kelayakan',
            'indicator' => 'Proyek realistis dilaksanakan dari sisi waktu, alat, biaya, dan dampak.',
            'max_score' => 13,
        ],
        [
            'key' => 'komunikasi_hasil',
            'criterion' => 'Komunikasi hasil',
            'indicator' => 'Kesimpulan, bukti, dan refleksi disampaikan runtut serta mudah dipahami.',
            'max_score' => 12,
        ],
    ];

    public ?int $reviewingProjectId = null;

    public ?float $score = null;

    public ?string $feedback = null;

    /**
     * @var array<string, float|int|string|null>
     */
    public array $rubricScores = [];

    public $filterModule = '';

    public $filterStatus = '';

    public function review(int $projectId): void
    {
        $project = $this->teacherProjectQuery()->findOrFail($projectId);

        $project->load('rubricScores');

        $this->reviewingProjectId = $project->id;
        $this->score = $project->score === null ? null : (float) $project->score;
        $this->feedback = $project->feedback;
        $this->rubricScores = collect($this->projectRubric)
            ->mapWithKeys(fn (array $criterion) => [
                $criterion['key'] => $project->rubricScores->firstWhere('criterion_key', $criterion['key'])?->score,
            ])
            ->all();
    }

    public function saveReview(): void
    {
        $validated = $this->validate([
            'reviewingProjectId' => ['required', 'integer'],
            'rubricScores' => ['required', 'array'],
            'rubricScores.*' => ['required', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string'],
        ]);

        $project = $this->teacherProjectQuery()->findOrFail($validated['reviewingProjectId']);
        $totalScore = 0.0;

        foreach ($this->projectRubric as $criterion) {
            $score = (float) ($validated['rubricScores'][$criterion['key']] ?? 0);

            if ($score > $criterion['max_score']) {
                $this->addError('rubricScores.'.$criterion['key'], 'Skor tidak boleh melebihi '.$criterion['max_score'].'.');

                return;
            }

            $totalScore += $score;

            $project->rubricScores()->updateOrCreate(
                ['criterion_key' => $criterion['key']],
                [
                    'criterion' => $criterion['criterion'],
                    'max_score' => $criterion['max_score'],
                    'score' => $score,
                ],
            );
        }

        $project->update([
            'score' => round($totalScore, 2),
            'feedback' => $validated['feedback'],
            'status' => 'reviewed',
        ]);

        $this->reset(['reviewingProjectId', 'score', 'feedback', 'rubricScores']);
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
        return Project::with('module', 'user', 'rubricScores')
            ->whereIn('module_id', Module::where('created_by', auth()->id())->pluck('id'));
    }
}
