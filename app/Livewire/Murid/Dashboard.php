<?php

namespace App\Livewire\Murid;

use App\Models\AssessmentAttempt;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Project;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.murid.dashboard', [
            'stats' => [
                'Modul Tersedia' => Module::where('status', 'published')->count(),
                'Progress' => Progress::where('user_id', auth()->id())->count(),
                'Attempt' => AssessmentAttempt::where('student_id', auth()->id())->count(),
                'Proyek' => Project::where('user_id', auth()->id())->count(),
            ],
        ]);
    }
}
