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

    public string $newSectionType = 'custom_content';

    public bool $showMediaModal = false;

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

    public function openAddSectionModal(?int $parentId = null): void
    {
        if ($parentId !== null) {
            $this->sectionForTeacher($parentId);
        }

        $this->selectedParentId = $parentId;
        $this->newSectionType = 'custom_content';
        $this->showCreateModal = true;
    }

    public function closeAddSectionModal(): void
    {
        $this->showCreateModal = false;
        $this->selectedParentId = null;
        $this->newSectionType = 'custom_content';
    }

    public function createSectionFromModal(): void
    {
        $validated = $this->validate([
            'selectedParentId' => ['nullable', Rule::in($this->currentLearningUnit->sections->pluck('id')->all())],
            'newSectionType' => ['required', Rule::in(array_keys($this->sectionTypeChoices()))],
        ]);

        $sectionType = $validated['newSectionType'];
        $section = app(DynamicOutlineService::class)->createSection($this->currentLearningUnit, [
            'parent_id' => $validated['selectedParentId'],
            'title' => $this->defaultTitleFor($sectionType),
            'section_type' => $sectionType,
            'editor_type' => DynamicOutlineService::DEFAULT_EDITORS[$sectionType],
        ]);

        $this->closeAddSectionModal();
        $this->loadTree();
        $this->selectSection($section->id);

        session()->flash('status', 'Bagian baru berhasil ditambahkan.');
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

        $data = $this->withFriendlyLinkType($validated['form']);
        $data['content_json'] = $this->contentJsonForSave($data['editor_type']);
        $data['settings'] = $this->decodedJsonOrFormArray($this->settingsText, $this->form['settings'] ?? null, 'settingsText');

        $this->selectedSection = app(DynamicOutlineService::class)->updateSection($this->selectedSection, $data);
        $this->loadTree();
        $this->selectSection($this->selectedSection->id);

        session()->flash('status', 'Section outline berhasil disimpan.');
    }

    public function updatedFormSectionType(string $sectionType): void
    {
        if (isset(DynamicOutlineService::DEFAULT_EDITORS[$sectionType])) {
            $this->form['editor_type'] = DynamicOutlineService::DEFAULT_EDITORS[$sectionType];
        }
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

    public function openMediaModal(): void
    {
        abort_unless($this->selectedSection, 404);

        $this->resetMediaForm();
        $this->showMediaModal = true;
    }

    public function closeMediaModal(): void
    {
        $this->showMediaModal = false;
        $this->resetMediaForm();
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

        $this->closeMediaModal();
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
            'sectionTypeChoices' => $this->sectionTypeChoices(),
            'selectedSectionLabel' => $this->selectedSection ? $this->humanSectionLabel($this->selectedSection->section_type) : null,
            'editorView' => $this->editorView(),
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
            'label' => $this->humanSectionLabel($section->section_type),
            'section_type' => $section->section_type,
            'is_visible' => $section->is_visible,
            'is_required' => $section->is_required,
            'is_locked' => $section->is_locked,
            'children' => $section->children->map(fn (LearningUnitSection $child): array => $this->treeNode($child))->all(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sectionTypeChoices(): array
    {
        return [
            'learning_objective' => 'Tujuan Pembelajaran',
            'key_points' => 'Pokok-Pokok Materi',
            'material_group' => 'Uraian Materi',
            'material_item' => 'Submateri',
            'activity_group' => 'Aktivitas Pembelajaran',
            'activity_item' => 'Aktivitas',
            'forum' => 'Forum Diskusi/Refleksi',
            'assessment_group' => 'Asesmen Formatif',
            'media_gallery' => 'Galeri Media',
            'custom_content' => 'Konten Bebas',
        ];
    }

    private function humanSectionLabel(?string $sectionType): string
    {
        return $this->sectionTypeChoices()[$sectionType] ?? 'Bagian';
    }

    private function defaultTitleFor(string $sectionType): string
    {
        return $this->humanSectionLabel($sectionType);
    }

    private function editorView(): string
    {
        if (($this->form['editor_type'] ?? null) === 'custom_json') {
            return 'livewire.guru.outline.editors.custom-json';
        }

        return match ($this->form['section_type'] ?? null) {
            'learning_objective' => 'livewire.guru.outline.editors.rich-text',
            'key_points' => 'livewire.guru.outline.editors.key-points',
            'material_group' => 'livewire.guru.outline.editors.material',
            'material_item' => 'livewire.guru.outline.editors.material',
            'activity_group' => 'livewire.guru.outline.editors.activity',
            'activity_item' => 'livewire.guru.outline.editors.activity',
            'forum' => 'livewire.guru.outline.editors.forum',
            'assessment_group' => 'livewire.guru.outline.editors.assessment',
            'question_group' => 'livewire.guru.outline.editors.question-group',
            'media_gallery' => 'livewire.guru.outline.editors.media-gallery',
            default => 'livewire.guru.outline.editors.rich-text',
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withFriendlyLinkType(array $data): array
    {
        if (blank($data['linked_model_id'] ?? null)) {
            return $data;
        }

        $data['linked_model_type'] = match ($data['section_type'] ?? null) {
            'material_item' => Material::class,
            'activity_item', 'forum' => Activity::class,
            'assessment_group' => Assessment::class,
            default => $data['linked_model_type'] ?? null,
        };

        return $data;
    }

    private function contentJsonForSave(string $editorType): ?array
    {
        if (in_array($editorType, ['key_points_table', 'forum_link', 'question_group'], true)) {
            return is_array($this->form['content_json'] ?? null) ? $this->form['content_json'] : null;
        }

        return $this->decodedJsonOrFormArray($this->contentJsonText, $this->form['content_json'] ?? null, 'contentJsonText');
    }

    private function resetMediaForm(): void
    {
        $this->reset(['mediaTitle', 'mediaUrl', 'mediaEmbedCode', 'mediaFile']);
        $this->mediaType = 'image';
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
