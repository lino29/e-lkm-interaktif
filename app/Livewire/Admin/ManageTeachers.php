<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class ManageTeachers extends Component
{
    public function render()
    {
        return view('livewire.admin.manage-teachers', [
            'teachers' => User::role('guru')->withCount('modules')->latest()->get(),
        ]);
    }
}
