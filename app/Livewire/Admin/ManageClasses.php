<?php

namespace App\Livewire\Admin;

use App\Models\ClassRoom;
use Livewire\Component;

class ManageClasses extends Component
{
    public string $name = '';

    public string $code = '';

    public ?string $description = null;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:class_rooms,code'],
            'description' => ['nullable', 'string'],
        ]);

        ClassRoom::create($validated);
        $this->reset(['name', 'code', 'description']);
        session()->flash('status', 'Kelas berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.admin.manage-classes', [
            'classes' => ClassRoom::withCount('users')->latest()->get(),
        ]);
    }
}
