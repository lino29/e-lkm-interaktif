<?php

namespace App\Services\Learning;

use App\Models\Activity;
use App\Models\Assessment;
use App\Models\LearningUnit;
use App\Models\LearningUnitSection;
use App\Models\Material;
use App\Models\Media;
use DomainException;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DynamicOutlineService
{
    public const SECTION_TYPES = [
        'learning_objective',
        'key_points',
        'material_group',
        'material_item',
        'activity_group',
        'activity_item',
        'forum',
        'assessment_group',
        'question_group',
        'media_gallery',
        'custom_content',
    ];

    public const EDITOR_TYPES = [
        'rich_text',
        'key_points_table',
        'material_editor',
        'activity_link',
        'forum_link',
        'assessment_link',
        'question_group',
        'media_gallery',
        'custom_json',
        'group_only',
    ];

    public const LINKABLE_MODELS = [
        Material::class,
        Activity::class,
        Assessment::class,
        Media::class,
    ];

    public const DEFAULT_EDITORS = [
        'learning_objective' => 'rich_text',
        'key_points' => 'key_points_table',
        'material_group' => 'group_only',
        'material_item' => 'material_editor',
        'activity_group' => 'group_only',
        'activity_item' => 'activity_link',
        'forum' => 'forum_link',
        'assessment_group' => 'assessment_link',
        'question_group' => 'question_group',
        'media_gallery' => 'media_gallery',
        'custom_content' => 'rich_text',
    ];

    public function createSection(LearningUnit $unit, array $data): LearningUnitSection
    {
        return DB::transaction(function () use ($unit, $data): LearningUnitSection {
            $parentId = $this->normalizeNullableInteger($data['parent_id'] ?? null);
            $this->assertParentBelongsToUnit($unit, $parentId);

            $sectionType = $this->validatedSectionType((string) ($data['section_type'] ?? 'custom_content'));
            $editorType = $this->validatedEditorType((string) ($data['editor_type'] ?? self::DEFAULT_EDITORS[$sectionType]));
            $title = (string) ($data['title'] ?? 'Section Baru');

            $attributes = [
                'learning_unit_id' => $unit->id,
                'parent_id' => $parentId,
                'section_type' => $sectionType,
                'editor_type' => $editorType,
                'title' => $title,
                'slug' => $this->sectionSlug($data['slug'] ?? null, $title),
                'content' => $this->contentForEditor($editorType, $data['content'] ?? null),
                'content_json' => $this->arrayOrNull($data['content_json'] ?? null, 'content_json'),
                'settings' => $this->arrayOrNull($data['settings'] ?? null, 'settings'),
                'order' => max(1, (int) ($data['order'] ?? $this->nextOrder($unit, $parentId))),
                'is_visible' => (bool) ($data['is_visible'] ?? true),
                'is_required' => (bool) ($data['is_required'] ?? false),
                'is_locked' => (bool) ($data['is_locked'] ?? false),
            ];

            if (filled($data['linked_model_type'] ?? null) && filled($data['linked_model_id'] ?? null)) {
                $linkedModel = $this->validateLinkedModel($unit, (string) $data['linked_model_type'], (int) $data['linked_model_id']);
                $attributes['linked_model_type'] = $linkedModel::class;
                $attributes['linked_model_id'] = $linkedModel->getKey();
            }

            $section = LearningUnitSection::create($attributes);
            $this->normalizeOrder($unit, $parentId);

            return $section->fresh(['children', 'media']);
        });
    }

    public function updateSection(LearningUnitSection $section, array $data): LearningUnitSection
    {
        return DB::transaction(function () use ($section, $data): LearningUnitSection {
            $section->loadMissing('learningUnit');
            $unit = $section->learningUnit;
            $parentId = array_key_exists('parent_id', $data)
                ? $this->normalizeNullableInteger($data['parent_id'])
                : $section->parent_id;

            $this->assertParentBelongsToUnit($unit, $parentId);
            $this->assertNotSelfParent($section, $parentId);
            $this->assertNotCircularParent($section, $parentId);

            $editorType = array_key_exists('editor_type', $data)
                ? $this->validatedEditorType((string) $data['editor_type'])
                : (string) $section->editor_type;

            $attributes = [];

            if (array_key_exists('title', $data)) {
                $attributes['title'] = (string) $data['title'];
            }

            if (array_key_exists('slug', $data)) {
                $attributes['slug'] = $this->sectionSlug($data['slug'], (string) ($data['title'] ?? $section->title));
            }

            if (array_key_exists('section_type', $data)) {
                $attributes['section_type'] = $this->validatedSectionType((string) $data['section_type']);
            }

            if (array_key_exists('editor_type', $data)) {
                $attributes['editor_type'] = $editorType;
            }

            if (array_key_exists('parent_id', $data)) {
                $attributes['parent_id'] = $parentId;
            }

            if (array_key_exists('content', $data)) {
                $attributes['content'] = $this->contentForEditor($editorType, $data['content']);
            }

            if (array_key_exists('content_json', $data)) {
                $attributes['content_json'] = $this->arrayOrNull($data['content_json'], 'content_json');
            }

            if (array_key_exists('settings', $data)) {
                $attributes['settings'] = $this->arrayOrNull($data['settings'], 'settings');
            }

            foreach (['order', 'is_visible', 'is_required', 'is_locked'] as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $field === 'order' ? max(1, (int) $data[$field]) : (bool) $data[$field];
                }
            }

            if (array_key_exists('linked_model_type', $data) || array_key_exists('linked_model_id', $data)) {
                if (blank($data['linked_model_type'] ?? null) || blank($data['linked_model_id'] ?? null)) {
                    $attributes['linked_model_type'] = null;
                    $attributes['linked_model_id'] = null;
                } else {
                    $linkedModel = $this->validateLinkedModel($unit, (string) $data['linked_model_type'], (int) $data['linked_model_id']);
                    $attributes['linked_model_type'] = $linkedModel::class;
                    $attributes['linked_model_id'] = $linkedModel->getKey();
                }
            }

            $oldParentId = $section->parent_id;
            $section->update($attributes);
            $freshSection = $section->fresh();

            $this->normalizeOrder($unit, $oldParentId);
            $this->normalizeOrder($unit, $freshSection->parent_id);

            return $freshSection->load(['children', 'media']);
        });
    }

    public function deleteSection(LearningUnitSection $section): void
    {
        DB::transaction(function () use ($section): void {
            if ($section->is_locked) {
                throw new DomainException('Section terkunci dan tidak bisa dihapus.');
            }

            $section->loadMissing('learningUnit');
            $unit = $section->learningUnit;
            $parentId = $section->parent_id;

            $section->delete();
            $this->normalizeOrder($unit, $parentId);
        });
    }

    public function duplicateSection(LearningUnitSection $section): LearningUnitSection
    {
        return DB::transaction(function () use ($section): LearningUnitSection {
            $section->loadMissing(['children', 'learningUnit']);
            $copy = $this->copySection($section, $section->parent_id, true);
            $this->normalizeOrder($section->learningUnit, $section->parent_id);

            return $copy->fresh(['children', 'media']);
        });
    }

    public function moveSection(LearningUnitSection $section, int $newOrder, ?int $parentId = null): LearningUnitSection
    {
        return DB::transaction(function () use ($section, $newOrder, $parentId): LearningUnitSection {
            $section->loadMissing('learningUnit');
            $unit = $section->learningUnit;
            $oldParentId = $section->parent_id;

            $this->assertParentBelongsToUnit($unit, $parentId);
            $this->assertNotSelfParent($section, $parentId);
            $this->assertNotCircularParent($section, $parentId);

            $section->update([
                'parent_id' => $parentId,
                'order' => max(1, $newOrder),
            ]);

            $this->normalizeOrder($unit, $oldParentId);
            $this->normalizeOrder($unit, $parentId);

            return $section->fresh(['children', 'media']);
        });
    }

    public function moveSectionUp(LearningUnitSection $section): void
    {
        $sibling = $this->adjacentSibling($section, '<', 'desc');

        if (! $sibling) {
            return;
        }

        $this->swapOrder($section, $sibling);
    }

    public function moveSectionDown(LearningUnitSection $section): void
    {
        $sibling = $this->adjacentSibling($section, '>', 'asc');

        if (! $sibling) {
            return;
        }

        $this->swapOrder($section, $sibling);
    }

    public function toggleVisibility(LearningUnitSection $section): LearningUnitSection
    {
        $section->update(['is_visible' => ! $section->is_visible]);

        return $section->fresh(['children', 'media']);
    }

    public function toggleRequired(LearningUnitSection $section): LearningUnitSection
    {
        $section->update(['is_required' => ! $section->is_required]);

        return $section->fresh(['children', 'media']);
    }

    public function linkModel(LearningUnitSection $section, string $modelType, int $modelId): LearningUnitSection
    {
        $section->loadMissing('learningUnit');
        $linkedModel = $this->validateLinkedModel($section->learningUnit, $modelType, $modelId);

        $section->update([
            'linked_model_type' => $linkedModel::class,
            'linked_model_id' => $linkedModel->getKey(),
        ]);

        return $section->fresh(['children', 'media']);
    }

    public function unlinkModel(LearningUnitSection $section): LearningUnitSection
    {
        $section->update([
            'linked_model_type' => null,
            'linked_model_id' => null,
        ]);

        return $section->fresh(['children', 'media']);
    }

    public function sanitizeContent(?string $html): ?string
    {
        if (blank($html)) {
            return null;
        }

        $cachePath = storage_path('framework/cache/htmlpurifier');

        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('HTML.DefinitionID', 'e-lkm-learning-content');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https://(www\.youtube\.com/embed/|www\.youtube-nocookie\.com/embed/)%');
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
        ]);
        $config->set('HTML.AllowedElements', array_fill_keys([
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'p',
            'br',
            'span',
            'div',
            'figure',
            'figcaption',
            'strong',
            'em',
            'b',
            'i',
            'u',
            's',
            'sub',
            'sup',
            'ul',
            'ol',
            'li',
            'table',
            'thead',
            'tbody',
            'tfoot',
            'tr',
            'th',
            'td',
            'colgroup',
            'col',
            'blockquote',
            'a',
            'img',
            'iframe',
        ], true));
        $config->set('HTML.AllowedAttributes', array_fill_keys([
            '*.class',
            '*.style',
            'a.href',
            'a.title',
            'a.target',
            'a.rel',
            'img.src',
            'img.alt',
            'img.title',
            'img.width',
            'img.height',
            'iframe.src',
            'iframe.width',
            'iframe.height',
            'iframe.title',
            'iframe.frameborder',
            'table.width',
            'th.width',
            'th.height',
            'th.colspan',
            'th.rowspan',
            'td.width',
            'td.height',
            'td.colspan',
            'td.rowspan',
            'col.width',
        ], true));
        $config->set('CSS.AllowedProperties', [
            'background-color',
            'border',
            'border-bottom',
            'border-collapse',
            'border-color',
            'border-left',
            'border-right',
            'border-spacing',
            'border-style',
            'border-top',
            'border-width',
            'color',
            'font',
            'font-family',
            'font-size',
            'font-style',
            'font-weight',
            'height',
            'line-height',
            'margin',
            'margin-left',
            'margin-right',
            'padding',
            'padding-left',
            'text-align',
            'text-decoration',
            'vertical-align',
            'width',
        ]);
        $config->set('CSS.AllowImportant', false);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        if ($definition = $config->maybeGetRawHTMLDefinition()) {
            if (! isset($definition->info['figure'])) {
                $definition->addElement('figure', 'Block', 'Flow', 'Common');
            }

            if (! isset($definition->info['figcaption'])) {
                $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
            }
        }

        $purified = (new HTMLPurifier($config))->purify($html);

        return preg_replace('/<iframe\b(?![^>]*\bsrc=)[^>]*>\s*<\/iframe>/i', '', $purified) ?? $purified;
    }

    public function normalizeOrder(LearningUnit $unit, ?int $parentId = null): void
    {
        $unit->sections()
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->values()
            ->each(function (LearningUnitSection $section, int $index): void {
                $expectedOrder = $index + 1;

                if ($section->order !== $expectedOrder) {
                    $section->update(['order' => $expectedOrder]);
                }
            });
    }

    private function contentForEditor(string $editorType, mixed $content): ?string
    {
        $content = blank($content) ? null : (string) $content;

        return in_array($editorType, ['rich_text', 'material_editor'], true)
            ? $this->sanitizeContent($content)
            : $content;
    }

    private function validatedSectionType(string $sectionType): string
    {
        if (! in_array($sectionType, self::SECTION_TYPES, true)) {
            throw new InvalidArgumentException('Tipe section tidak valid.');
        }

        return $sectionType;
    }

    private function validatedEditorType(string $editorType): string
    {
        if (! in_array($editorType, self::EDITOR_TYPES, true)) {
            throw new InvalidArgumentException('Tipe editor tidak valid.');
        }

        return $editorType;
    }

    private function validateLinkedModel(LearningUnit $unit, string $modelType, int $modelId): Model
    {
        if (! in_array($modelType, self::LINKABLE_MODELS, true)) {
            throw new InvalidArgumentException('Model link tidak valid.');
        }

        $model = $modelType::query()->findOrFail($modelId);

        if ($model instanceof Media) {
            $belongsToUnit = $model->learning_unit_id === $unit->id || $model->material?->learning_unit_id === $unit->id;
        } else {
            $belongsToUnit = $model->learning_unit_id === $unit->id;
        }

        if (! $belongsToUnit) {
            throw new InvalidArgumentException('Target link tidak berada pada kegiatan belajar yang sama.');
        }

        return $model;
    }

    private function assertParentBelongsToUnit(LearningUnit $unit, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if (! $unit->sections()->whereKey($parentId)->exists()) {
            throw new InvalidArgumentException('Parent section tidak valid.');
        }
    }

    private function assertNotSelfParent(LearningUnitSection $section, ?int $parentId): void
    {
        if ($parentId !== null && $section->id === $parentId) {
            throw new InvalidArgumentException('Section tidak boleh menjadi parent dirinya sendiri.');
        }
    }

    private function assertNotCircularParent(LearningUnitSection $section, ?int $parentId): void
    {
        while ($parentId !== null) {
            $parent = LearningUnitSection::query()->find($parentId);

            if (! $parent) {
                return;
            }

            if ($parent->parent_id === $section->id) {
                throw new InvalidArgumentException('Parent section akan membuat struktur melingkar.');
            }

            $parentId = $parent->parent_id;
        }
    }

    private function nextOrder(LearningUnit $unit, ?int $parentId): int
    {
        return ((int) $unit->sections()->where('parent_id', $parentId)->max('order')) + 1;
    }

    private function sectionSlug(mixed $slug, string $title): string
    {
        return Str::slug(blank($slug) ? $title : (string) $slug);
    }

    private function normalizeNullableInteger(mixed $value): ?int
    {
        return blank($value) ? null : (int) $value;
    }

    private function arrayOrNull(mixed $value, string $field): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException("{$field} harus berupa array.");
        }

        return $value;
    }

    private function copySection(LearningUnitSection $section, ?int $parentId, bool $rootCopy = false): LearningUnitSection
    {
        $copy = $section->replicate([
            'created_at',
            'updated_at',
        ]);

        $copy->parent_id = $parentId;
        $copy->title = $rootCopy ? $section->title.' Copy' : $section->title;
        $copy->slug = Str::slug($copy->title).'-'.Str::lower(Str::random(5));
        $copy->order = $this->nextOrder($section->learningUnit, $parentId);
        $copy->is_locked = false;
        $copy->save();

        foreach ($section->children()->orderBy('order')->get() as $child) {
            $child->loadMissing('learningUnit');
            $this->copySection($child, $copy->id);
        }

        return $copy;
    }

    private function adjacentSibling(LearningUnitSection $section, string $operator, string $direction): ?LearningUnitSection
    {
        $section->loadMissing('learningUnit');

        return $section->learningUnit
            ->sections()
            ->where('parent_id', $section->parent_id)
            ->where('order', $operator, $section->order)
            ->orderBy('order', $direction)
            ->first();
    }

    private function swapOrder(LearningUnitSection $section, LearningUnitSection $sibling): void
    {
        DB::transaction(function () use ($section, $sibling): void {
            $currentOrder = $section->order;
            $section->update(['order' => $sibling->order]);
            $sibling->update(['order' => $currentOrder]);
            $this->normalizeOrder($section->learningUnit, $section->parent_id);
        });
    }
}
