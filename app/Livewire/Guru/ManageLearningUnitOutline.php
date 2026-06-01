<?php

namespace App\Livewire\Guru;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Models\Material;
use App\Services\Learning\LearningUnitOutlineService;
use Livewire\Component;

class ManageLearningUnitOutline extends Component
{
    public LearningUnit $currentLearningUnit;

    /**
     * @var array<int, string>
     */
    public array $titles = [];

    /**
     * @var array<int, ?string>
     */
    public array $contents = [];

    /**
     * @var array<int, string>
     */
    public array $contentJson = [];

    /**
     * @var array<int, int>
     */
    public array $orders = [];

    /**
     * @var array<int, string>
     */
    public array $linkTypes = [];

    /**
     * @var array<int, ?int>
     */
    public array $linkIds = [];

    public function mount(string|int $learningUnit): void
    {
        $this->currentLearningUnit = LearningUnit::whereHas('module', fn ($query) => $query->where('created_by', auth()->id()))
            ->findOrFail($learningUnit);

        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);

        $this->refreshLearningUnit();
    }

    public function regenerate(): void
    {
        app(LearningUnitOutlineService::class)->ensureDefaultOutline($this->currentLearningUnit);
        $this->refreshLearningUnit();

        session()->flash('status', 'Outline OITLINE berhasil digenerate ulang.');
    }

    public function saveSection(int $sectionId): void
    {
        $section = $this->sectionForTeacher($sectionId);
        $decoded = json_decode($this->contentJson[$sectionId] ?? 'null', true);

        if (($this->contentJson[$sectionId] ?? '') !== '' && json_last_error() !== JSON_ERROR_NONE) {
            $this->addError("contentJson.{$sectionId}", 'Format JSON tidak valid.');

            return;
        }

        $section->update([
            'title' => $this->titles[$sectionId] ?? $section->title,
            'content' => $this->contents[$sectionId] ?? $section->content,
            'content_json' => is_array($decoded) ? $decoded : $section->content_json,
            'order' => max(1, (int) ($this->orders[$sectionId] ?? $section->order)),
        ]);

        $this->refreshLearningUnit();
        session()->flash('status', 'Section outline berhasil disimpan.');
    }

    public function linkSection(int $sectionId): void
    {
        $section = $this->sectionForTeacher($sectionId);
        $type = $this->linkTypes[$sectionId] ?? '';
        $id = $this->linkIds[$sectionId] ?? null;

        $map = [
            'material' => Material::class,
            'activity' => Activity::class,
            'assessment' => Assessment::class,
        ];

        if (! isset($map[$type]) || ! $id) {
            $section->update([
                'linked_model_type' => null,
                'linked_model_id' => null,
            ]);

            $this->refreshLearningUnit();

            return;
        }

        $this->assertLinkTargetBelongsToUnit($map[$type], (int) $id);

        $section->update([
            'linked_model_type' => $map[$type],
            'linked_model_id' => (int) $id,
        ]);

        $this->refreshLearningUnit();
        session()->flash('status', 'Link section berhasil diperbarui.');
    }

    public function moveSection(int $sectionId, string $direction): void
    {
        $section = $this->sectionForTeacher($sectionId);
        $siblings = $this->currentLearningUnit
            ->sections()
            ->where('parent_id', $section->parent_id)
            ->orderBy('order')
            ->get();

        $index = $siblings->search(fn (LearningUnitSection $item) => $item->is($section));
        $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if (! $siblings->has($targetIndex)) {
            return;
        }

        $target = $siblings[$targetIndex];
        $currentOrder = $section->order;

        $section->update(['order' => $target->order]);
        $target->update(['order' => $currentOrder]);

        $this->refreshLearningUnit();
    }

    private function sectionForTeacher(int $sectionId): LearningUnitSection
    {
        return $this->currentLearningUnit->sections()->findOrFail($sectionId);
    }

    private function assertLinkTargetBelongsToUnit(string $modelClass, int $id): void
    {
        $query = $modelClass::query()->whereKey($id);

        if ($modelClass === Assessment::class) {
            $query->where('learning_unit_id', $this->currentLearningUnit->id);
        } else {
            $query->where('learning_unit_id', $this->currentLearningUnit->id);
        }

        $query->firstOrFail();
    }

    private function refreshLearningUnit(): void
    {
        $this->currentLearningUnit = $this->currentLearningUnit->fresh([
            'module',
            'rootSections.children',
            'sections',
            'materials',
            'activities',
            'assessments',
        ]);

        foreach ($this->currentLearningUnit->sections as $section) {
            $this->titles[$section->id] = $section->title;
            $this->contents[$section->id] = $section->content;
            $this->contentJson[$section->id] = $section->content_json ? json_encode($section->content_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
            $this->orders[$section->id] = $section->order;
            $this->linkTypes[$section->id] = match ($section->linked_model_type) {
                Material::class => 'material',
                Activity::class => 'activity',
                Assessment::class => 'assessment',
                default => '',
            };
            $this->linkIds[$section->id] = $section->linked_model_id;
        }
    }

    public function render()
    {
        return view('livewire.guru.manage-learning-unit-outline', [
            'learningUnit' => $this->currentLearningUnit,
            'rootSections' => $this->currentLearningUnit->rootSections,
            'materials' => $this->currentLearningUnit->materials,
            'activities' => $this->currentLearningUnit->activities,
            'assessments' => $this->currentLearningUnit->assessments,
        ]);
    }
}
