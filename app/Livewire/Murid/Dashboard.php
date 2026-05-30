<?php

namespace App\Livewire\Murid;

use App\Models\AssessmentAttempt;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use App\Services\Learning\ProgressService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $student = auth()->user();
        $modules = Module::with('learningUnits')->where('status', 'published')->orderBy('title')->get();
        $progressService = app(ProgressService::class);
        $moduleProgress = $modules->map(fn (Module $module): array => [
            'module' => $module,
            'percentage' => $progressService->moduleCompletionPercentage($student, $module),
        ]);
        $averageProgress = $moduleProgress->isEmpty()
            ? 0
            : (int) round($moduleProgress->avg('percentage'));

        return view('livewire.murid.dashboard', [
            'stats' => [
                'Modul Tersedia' => $modules->count(),
                'Progress Rata-rata' => $averageProgress.'%',
                'Attempt' => AssessmentAttempt::where('student_id', $student->id)->count(),
                'Proyek' => Project::where('user_id', $student->id)->count(),
            ],
            'moduleProgress' => $moduleProgress,
            'progressRecordsCount' => Progress::where('user_id', $student->id)->count(),
        ]);
    }
}
