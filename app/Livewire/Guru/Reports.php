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
    public ?int $module_id = null;

    public function render()
    {
        $teacherModuleIds = Module::where('created_by', auth()->id())->pluck('id');
        $moduleIds = $this->module_id && $teacherModuleIds->contains($this->module_id)
            ? collect([$this->module_id])
            : $teacherModuleIds;

        $attemptsQuery = AssessmentAttempt::with('student', 'assessment.module')
            ->whereHas('assessment', fn ($query) => $query->whereIn('module_id', $moduleIds));

        $tuntasCount = (clone $attemptsQuery)->where('status', 'tuntas')->count();
        $remedialCount = (clone $attemptsQuery)->where('status', 'remedial')->count();

        return view('livewire.guru.reports', [
            'modules' => Module::whereIn('id', $teacherModuleIds)->orderBy('title')->get(),
            'tuntasCount' => $tuntasCount,
            'remedialCount' => $remedialCount,
            'submittedProjectCount' => Project::whereIn('module_id', $moduleIds)->where('status', 'submitted')->count(),
            'reviewedProjectCount' => Project::whereIn('module_id', $moduleIds)->where('status', 'reviewed')->count(),
            'discussionCount' => Discussion::whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))->count(),
            'attempts' => (clone $attemptsQuery)
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
            'remedialAttempts' => (clone $attemptsQuery)
                ->where('status', 'remedial')
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
