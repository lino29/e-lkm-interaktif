<?php

namespace App\Livewire\Guru;

use App\Models\AssessmentAttempt;
use App\Models\Progress;
use App\Models\Project;
use Livewire\Component;

class Reports extends Component
{
    public function render()
    {
        return view('livewire.guru.reports', [
            'attempts' => AssessmentAttempt::with('student', 'assessment.module')->latest()->limit(20)->get(),
            'progressRecords' => Progress::with('user', 'module', 'learningUnit')->latest()->limit(20)->get(),
            'projects' => Project::with('user', 'module')->latest()->limit(20)->get(),
        ]);
    }
}
