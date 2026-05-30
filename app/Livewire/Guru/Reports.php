<?php

namespace App\Livewire\Guru;

use App\Models\AssessmentAttempt;
use App\Models\Discussion;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use Livewire\Component;

class Reports extends Component
{
    public function render()
    {
        $moduleIds = Module::where('created_by', auth()->id())->pluck('id');

        return view('livewire.guru.reports', [
            'attempts' => AssessmentAttempt::with('student', 'assessment.module')
                ->whereHas('assessment', fn ($query) => $query->whereIn('module_id', $moduleIds))
                ->latest()
                ->limit(20)
                ->get(),
            'progressRecords' => Progress::with('user', 'module', 'learningUnit')
                ->whereIn('module_id', $moduleIds)
                ->latest()
                ->limit(20)
                ->get(),
            'projects' => Project::with('user', 'module')
                ->whereIn('module_id', $moduleIds)
                ->latest()
                ->limit(20)
                ->get(),
            'remedialAttempts' => AssessmentAttempt::with('student', 'assessment.module')
                ->where('status', 'remedial')
                ->whereHas('assessment', fn ($query) => $query->whereIn('module_id', $moduleIds))
                ->latest()
                ->limit(20)
                ->get(),
            'discussions' => Discussion::with('user', 'learningUnit.module')
                ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }
}
