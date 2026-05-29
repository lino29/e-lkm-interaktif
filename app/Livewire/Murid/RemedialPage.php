<?php

namespace App\Livewire\Murid;

use App\Models\AssessmentAttempt;
use Livewire\Component;

class RemedialPage extends Component
{
    public function render()
    {
        return view('livewire.murid.remedial-page', [
            'attempts' => AssessmentAttempt::with('assessment.module')
                ->where('student_id', auth()->id())
                ->where('status', 'remedial')
                ->latest()
                ->get(),
        ]);
    }
}
