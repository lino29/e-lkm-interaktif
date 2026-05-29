<?php

namespace App\Livewire\Admin;

use App\Models\Subject;
use Livewire\Component;

class ManageSubjects extends Component
{
    public string $name = '';

    public string $code = '';

    public ?string $description = null;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:subjects,code'],
            'description' => ['nullable', 'string'],
        ]);

        Subject::create($validated);
        $this->reset(['name', 'code', 'description']);
        session()->flash('status', 'Mata pelajaran berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.admin.manage-subjects', [
            'subjects' => Subject::withCount('modules')->latest()->get(),
        ]);
    }
}
