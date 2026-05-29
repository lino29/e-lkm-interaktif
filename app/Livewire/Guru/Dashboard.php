<?php

namespace App\Livewire\Guru;

use App\Models\ActivityAnswer;
use App\Models\AssessmentAttempt;
use App\Models\Module;
use App\Models\Project;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $teacherId = auth()->id();

        return view('livewire.guru.dashboard', [
            'stats' => [
                'Modul Saya' => Module::where('created_by', $teacherId)->count(),
                'Jawaban Aktivitas' => ActivityAnswer::count(),
                'Attempt Asesmen' => AssessmentAttempt::count(),
                'Proyek Murid' => Project::count(),
            ],
        ]);
    }
}
