<?php

namespace App\Livewire\Guru;

use App\Models\Discussion;
use App\Models\Module;
use Livewire\Component;

class ManageDiscussions extends Component
{
    public function render()
    {
        $moduleIds = Module::where('created_by', auth()->id())->pluck('id');

        return view('livewire.guru.manage-discussions', [
            'discussions' => Discussion::with('learningUnit.module', 'user')
                ->whereHas('learningUnit', fn ($query) => $query->whereIn('module_id', $moduleIds))
                ->latest()
                ->get(),
        ]);
    }
}
