<?php

namespace App\Livewire\Murid;

use App\Models\Module;
use Livewire\Component;

class MyModules extends Component
{
    public function render()
    {
        return view('livewire.murid.my-modules', [
            'modules' => Module::with('subject', 'learningUnits', 'assessments')
                ->where('status', 'published')
                ->latest()
                ->get(),
        ]);
    }
}
