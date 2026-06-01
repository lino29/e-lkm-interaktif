<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Models\Material;
use App\Models\Media;
use App\Services\Learning\DynamicOutlineService;
use App\Services\Learning\LearningUnitOutlineService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageLearningUnitOutline extends Component
{
    use WithFileUploads;

    public LearningUnit $currentLearningUnit;

    public ?LearningUnitSection $selectedSection = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $tree = [];

    public bool $showCreateModal = false;

    public ?int $selectedParentId = null;

    public string $contentJsonText = '';

    public string $settingsText = '';

    public mixed $mediaFile = null;

    public string $mediaTitle = '';

    public string $mediaType = 'image';

    public ?string $mediaUrl = null;

    public ?string $mediaEmbedCode = null;

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::query()
            ->when(! auth()->user()->hasRole('admin'), fn ($query) => $query->whereHas('module', fn ($moduleQuery) => $moduleQuery->where('created_by', auth()->id())))
            ->findOrFail($learningUnit);

        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
        $this->loadTree();
        $this->selectSection($this->currentLearningUnit->rootSections->first()?->id);
    }

    public function loadTree(): void
    {
        $this->currentLearningUnit = $this->currentLearningUnit->fresh([
            'module',
            'rootSections.children.children',
            'sections.media',
            'materials.media',
            'activities',
            'assessments.questions',
        ]);

        $this->tree = $this->currentLearningUnit->rootSections
            ->map(fn (LearningUnitSection $section): array => $this->treeNode($section))
            ->all();
    }

    public function selectSection(?int $sectionId): void
    {
        if (! $sectionId) {
            $this->selectedSection = null;
            $this->resetForm();

            return;
        }

        $this->selectedSection = $this->sectionForTeacher($sectionId)->load(['children', 'media']);
        $this->fillFormFromSection($this->selectedSection);
    }

    public function createRootSection(): void
    {
        $section = app(DynamicOutlineService::class)->createSection($this->currentLearningUnit, [
            'title' => 'Section Baru',
            'section_type' => 'custom_content',
            'editor_type' => 'rich_text',
        ]);

        $this->loadTree();
        $this->selectSection($section->id);
        session()->flash('status', 'Root section berhasil dibuat.');
    }

    public function createChildSection(?int $parentId = null): void
    {
        $parentId ??= $this->selectedSection?->id;
        $parent = $this->sectionForTeacher((int) $parentId);

        $section = app(DynamicOutlineService::class)->createSection($this->currentLearningUnit, [
            'parent_id' => $parent->id,
            'title' => 'Subsection Baru',
            'section_type' => 'custom_content',
            'editor_type' => 'rich_text',
        ]);

        $this->loadTree();
        $this->selectSection($section->id);
        session()->flash('status', 'Child section berhasil dibuat.');
    }

    public function saveSection(): void
    {
        abort_unless($this->selectedSection, 404);

        $validated = $this->validate([
            'form.title' => ['required', 'string', 'max:255'],
            'form.slug' => ['nullable', 'string', 'max:255'],
            'form.parent_id' => ['nullable', Rule::in($this->currentLearningUnit->sections->pluck('id')->reject(fn (int $id): bool => $id === $this->selectedSection?->id)->all())],
            'form.section_type' => ['required', Rule::in(DynamicOutlineService::SECTION_TYPES)],
            'form.editor_type' => ['required', Rule::in(DynamicOutlineService::EDITOR_TYPES)],
            'form.order' => ['required', 'integer', 'min:1'],
            'form.is_visible' => ['boolean'],
            'form.is_required' => ['boolean'],
            'form.is_locked' => ['boolean'],
            'form.linked_model_type' => ['nullable', Rule::in(DynamicOutlineService::LINKABLE_MODELS)],
            'form.linked_model_id' => ['nullable', 'integer'],
            'form.content' => ['nullable', 'string'],
            'form.content_json' => ['nullable', 'array'],
            'form.settings' => ['nullable', 'array'],
            'contentJsonText' => ['nullable', 'string'],
            'settingsText' => ['nullable', 'string'],
        ]);

        $data = $validated['form'];
        $data['content_json'] = $this->decodedJsonOrFormArray($this->contentJsonText, $this->form['content_json'] ?? null, 'contentJsonText');
        $data['settings'] = $this->decodedJsonOrFormArray($this->settingsText, $this->form['settings'] ?? null, 'settingsText');

        $this->selectedSection = app(DynamicOutlineService::class)->updateSection($this->selectedSection, $data);
        $this->loadTree();
        $this->selectSection($this->selectedSection->id);

        session()->flash('status', 'Section outline berhasil disimpan.');
    }

    public function deleteSection(int $sectionId): void
    {
        app(DynamicOutlineService::class)->deleteSection($this->sectionForTeacher($sectionId));
        $this->loadTree();
        $this->selectSection($this->currentLearningUnit->rootSections->first()?->id);

        session()->flash('status', 'Section berhasil dihapus.');
    }

    public function duplicateSection(int $sectionId): void
    {
        $copy = app(DynamicOutlineService::class)->duplicateSection($this->sectionForTeacher($sectionId));
        $this->loadTree();
        $this->selectSection($copy->id);

        session()->flash('status', 'Section berhasil diduplikasi.');
    }

    public function moveUp(int $sectionId): void
    {
        app(DynamicOutlineService::class)->moveSectionUp($this->sectionForTeacher($sectionId));
        $this->loadTree();
    }

    public function moveDown(int $sectionId): void
    {
        app(DynamicOutlineService::class)->moveSectionDown($this->sectionForTeacher($sectionId));
        $this->loadTree();
    }

    public function toggleVisibility(int $sectionId): void
    {
        app(DynamicOutlineService::class)->toggleVisibility($this->sectionForTeacher($sectionId));
        $this->loadTree();

        if ($this->selectedSection?->id === $sectionId) {
            $this->selectSection($sectionId);
        }
    }

    public function toggleRequired(int $sectionId): void
    {
        app(DynamicOutlineService::class)->toggleRequired($this->sectionForTeacher($sectionId));
        $this->loadTree();

        if ($this->selectedSection?->id === $sectionId) {
            $this->selectSection($sectionId);
        }
    }

    public function generateOitlineTemplate(): void
    {
        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
        $this->loadTree();

        session()->flash('status', 'Template OITLINE berhasil disinkronkan tanpa menghapus section custom.');
    }

    public function regenerate(): void
    {
        $this->generateOitlineTemplate();
    }

    public function previewAsStudent()
    {
        return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.learning-units.preview' : 'guru.learning-units.preview', $this->currentLearningUnit);
    }

    public function addMedia(): void
    {
        abort_unless($this->selectedSection, 404);

        $validated = $this->validate([
            'mediaTitle' => ['required', 'string', 'max:255'],
            'mediaType' => ['required', Rule::in(['image', 'video_file', 'video', 'youtube', 'simulation', 'file', 'link', 'embed'])],
            'mediaUrl' => ['nullable', 'url', 'max:255'],
            'mediaEmbedCode' => ['nullable', 'string'],
            'mediaFile' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,pdf,doc,docx,ppt,pptx', 'max:5120'],
        ]);

        $filePath = $this->mediaFile
            ? $this->mediaFile->store('section-media', 'public')
            : null;

        Media::create([
            'learning_unit_id' => $this->currentLearningUnit->id,
            'learning_unit_section_id' => $this->selectedSection->id,
            'title' => $validated['mediaTitle'],
            'type' => $validated['mediaType'] === 'video_file' ? 'video' : $validated['mediaType'],
            'url' => $validated['mediaUrl'],
            'file_path' => $filePath,
            'embed_code' => app(DynamicOutlineService::class)->sanitizeContent($validated['mediaEmbedCode'] ?? null),
            'order' => $this->selectedSection->media()->max('order') + 1,
        ]);

        $this->reset(['mediaTitle', 'mediaUrl', 'mediaEmbedCode', 'mediaFile']);
        $this->mediaType = 'image';
        $this->selectSection($this->selectedSection->id);

        session()->flash('status', 'Media section berhasil ditambahkan.');
    }

    public function deleteMedia(int $mediaId): void
    {
        abort_unless($this->selectedSection, 404);

        $media = $this->selectedSection->media()->findOrFail($mediaId);

        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();
        $this->selectSection($this->selectedSection->id);
    }

    public function render()
    {
        return view('livewire.guru.manage-learning-unit-outline', [
            'learningUnit' => $this->currentLearningUnit,
            'materials' => $this->currentLearningUnit->materials,
            'activities' => $this->currentLearningUnit->activities,
            'assessments' => $this->currentLearningUnit->assessments,
            'mediaItems' => $this->currentLearningUnit->media,
            'sectionTypes' => DynamicOutlineService::SECTION_TYPES,
            'editorTypes' => DynamicOutlineService::EDITOR_TYPES,
            'linkableModels' => [
                Material::class => 'Materi',
                Activity::class => 'Aktivitas',
                Assessment::class => 'Asesmen',
                Media::class => 'Media',
            ],
        ]);
    }

    private function sectionForTeacher(int $sectionId): LearningUnitSection
    {
        return $this->currentLearningUnit->sections()->findOrFail($sectionId);
    }

    private function fillFormFromSection(LearningUnitSection $section): void
    {
        $this->form = [
            'title' => $section->title,
            'slug' => $section->slug,
            'parent_id' => $section->parent_id,
            'section_type' => $section->section_type,
            'editor_type' => $section->editor_type ?: DynamicOutlineService::DEFAULT_EDITORS[$section->section_type] ?? 'rich_text',
            'order' => $section->order,
            'is_visible' => $section->is_visible,
            'is_required' => $section->is_required,
            'is_locked' => $section->is_locked,
            'linked_model_type' => $section->linked_model_type,
            'linked_model_id' => $section->linked_model_id,
            'content' => $section->content,
            'content_json' => $section->content_json ?? [],
            'settings' => $section->settings ?? [],
        ];

        $this->contentJsonText = $section->content_json ? json_encode($section->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
        $this->settingsText = $section->settings ? json_encode($section->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
    }

    private function resetForm(): void
    {
        $this->form = [
            'title' => '',
            'slug' => '',
            'parent_id' => null,
            'section_type' => 'custom_content',
            'editor_type' => 'rich_text',
            'order' => 1,
            'is_visible' => true,
            'is_required' => false,
            'is_locked' => false,
            'linked_model_type' => null,
            'linked_model_id' => null,
            'content' => null,
            'content_json' => [],
            'settings' => [],
        ];
        $this->contentJsonText = '';
        $this->settingsText = '';
    }

    private function treeNode(LearningUnitSection $section): array
    {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'section_type' => $section->section_type,
            'is_visible' => $section->is_visible,
            'is_required' => $section->is_required,
            'is_locked' => $section->is_locked,
            'children' => $section->children->map(fn (LearningUnitSection $child): array => $this->treeNode($child))->all(),
        ];
    }

    private function decodedJsonOrFormArray(string $json, mixed $fallback, string $errorKey): ?array
    {
        if (blank($json)) {
            return is_array($fallback) ? $fallback : null;
        }

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw ValidationException::withMessages([
                $errorKey => 'Format JSON tidak valid.',
            ]);
        }

        return $decoded;
    }
}
