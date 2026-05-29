<?php

namespace App\Livewire\Admin;

use App\Models\ClassRoom;
use App\Models\Module;
use App\Models\Subject;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats' => [
                'Pengguna' => User::count(),
                'Guru' => User::role('guru')->count(),
                'Murid' => User::role('murid')->count(),
                'Kelas' => ClassRoom::count(),
                'Mata Pelajaran' => Subject::count(),
                'Modul' => Module::count(),
            ],
        ]);
    }
}
