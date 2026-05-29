<?php

namespace App\Livewire\Guru;

use App\Models\Project;
use Livewire\Component;

class ManageProjects extends Component
{
    public function render()
    {
        return view('livewire.guru.manage-projects', [
            'projects' => Project::with('module', 'user')->latest()->get(),
        ]);
    }
}
