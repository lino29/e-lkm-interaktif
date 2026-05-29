<?php

namespace App\Livewire\Guru;

use App\Models\Discussion;
use Livewire\Component;

class ManageDiscussions extends Component
{
    public function render()
    {
        return view('livewire.guru.manage-discussions', [
            'discussions' => Discussion::with('learningUnit.module', 'user')->latest()->get(),
        ]);
    }
}
