<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class ManageStudents extends Component
{
    public function render()
    {
        return view('livewire.admin.manage-students', [
            'students' => User::role('murid')->with('classRoom')->latest()->get(),
        ]);
    }
}
