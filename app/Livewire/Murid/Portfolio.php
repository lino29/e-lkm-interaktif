<?php

namespace App\Livewire\Murid;

use App\Models\ActivityAnswer;
use App\Models\Project;
use Livewire\Component;

class Portfolio extends Component
{
    public function render()
    {
        return view('livewire.murid.portfolio', [
            'activityAnswers' => ActivityAnswer::with('activity.learningUnit.module')
                ->where('user_id', auth()->id())
                ->latest()
                ->get(),
            'projects' => Project::with('module')->where('user_id', auth()->id())->latest()->get(),
        ]);
    }
}
