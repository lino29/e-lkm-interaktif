<?php

namespace App\Livewire\Guru;

use App\Models\Module;
use App\Models\Subject;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageModules extends Component
{
    public ?int $subject_id = null;

    public string $title = '';

    public ?string $introduction = null;

    public ?string $learning_objectives = null;

    public string $status = 'draft';

    public int $kktp = 75;

    public int $max_attempts = 2;

    public function save(): void
    {
        $validated = $this->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'introduction' => ['nullable', 'string'],
            'learning_objectives' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'kktp' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        Module::create([
            ...$validated,
            'created_by' => auth()->id(),
            'slug' => Str::slug($validated['title']).'-'.Str::random(5),
        ]);

        $this->reset(['subject_id', 'title', 'introduction', 'learning_objectives']);
        $this->status = 'draft';
        $this->kktp = 75;
        $this->max_attempts = 2;
        session()->flash('status', 'Modul berhasil dibuat.');
    }

    public function render()
    {
        return view('livewire.guru.manage-modules', [
            'subjects' => Subject::orderBy('name')->get(),
            'modules' => Module::with('subject')->where('created_by', auth()->id())->latest()->get(),
        ]);
    }
}
