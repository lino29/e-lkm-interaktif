<?php

namespace App\Livewire\Murid;

use App\Models\AssessmentAttempt;
use Livewire\Component;

class MyScores extends Component
{
    public function render()
    {
        return view('livewire.murid.my-scores', [
            'attempts' => AssessmentAttempt::with('assessment.module')
                ->where('student_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }
}
