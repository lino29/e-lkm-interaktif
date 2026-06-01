<?php

namespace App\Livewire\Guru;

use App\Models\Module;
use App\Models\ModuleSection;
use App\Services\Learning\DynamicOutlineService;
use App\Services\Learning\ModuleOutlineService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManageModuleOutline extends Component
{
    public Module $currentModule;

    public ?int $editingSectionId = null;

    public string $section_type = 'introduction';

    public string $title = '';

    public string $slug = '';

    public ?string $content = null;

    public int $order = 1;

    public bool $is_visible = true;

    public function mount(string|int $module): void
    {
        $this->currentModule = Module::query()
            ->when(! auth()->user()->hasRole('admin'), fn ($query) => $query->where('created_by', auth()->id()))
            ->findOrFail($module);
        app(ModuleOutlineService::class)->ensureDefaultSections($this->currentModule);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'section_type' => ['required', Rule::in(['introduction', 'closing'])],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'is_visible' => ['boolean'],
        ]);

        $section = $this->editingSectionId
            ? $this->currentModule->sections()->findOrFail($this->editingSectionId)
            : new ModuleSection(['module_id' => $this->currentModule->id]);

        $section->fill([
            ...$validated,
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'content' => app(DynamicOutlineService::class)->sanitizeContent($validated['content']),
        ])->save();

        $this->resetForm();
        session()->flash('status', 'Outline modul berhasil disimpan.');
    }

    public function edit(int $sectionId): void
    {
        $section = $this->currentModule->sections()->findOrFail($sectionId);

        $this->editingSectionId = $section->id;
        $this->section_type = $section->section_type;
        $this->title = $section->title;
        $this->slug = $section->slug;
        $this->content = $section->content;
        $this->order = $section->order;
        $this->is_visible = $section->is_visible;
    }

    public function delete(int $sectionId): void
    {
        $this->currentModule->sections()->findOrFail($sectionId)->delete();
        $this->resetForm();
        session()->flash('status', 'Outline modul berhasil dihapus.');
    }

    public function toggleVisibility(int $sectionId): void
    {
        $section = $this->currentModule->sections()->findOrFail($sectionId);
        $section->update(['is_visible' => ! $section->is_visible]);
    }

    public function move(int $sectionId, string $direction): void
    {
        $section = $this->currentModule->sections()->findOrFail($sectionId);
        $sibling = $this->currentModule->sections()
            ->where('section_type', $section->section_type)
            ->where('order', $direction === 'up' ? '<' : '>', $section->order)
            ->orderBy('order', $direction === 'up' ? 'desc' : 'asc')
            ->first();

        if (! $sibling) {
            return;
        }

        $currentOrder = $section->order;
        $section->update(['order' => $sibling->order]);
        $sibling->update(['order' => $currentOrder]);
    }

    public function generateTemplate(): void
    {
        app(ModuleOutlineService::class)->ensureDefaultSections($this->currentModule);
        session()->flash('status', 'Template outline modul berhasil disinkronkan.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $this->currentModule = $this->currentModule->fresh(['sections']);

        return view('livewire.guru.manage-module-outline', [
            'module' => $this->currentModule,
            'sections' => $this->currentModule->sections,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingSectionId', 'title', 'slug', 'content']);
        $this->section_type = 'introduction';
        $this->order = 1;
        $this->is_visible = true;
    }
}
