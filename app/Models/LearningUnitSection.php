<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningUnitSection extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'is_visible' => 'boolean',
            'is_required' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function learningUnit(): BelongsTo
    {
        return $this->belongsTo(LearningUnit::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function linkedModel(): ?Model
    {
        if (! $this->linked_model_type || ! $this->linked_model_id || ! class_exists($this->linked_model_type)) {
            return null;
        }

        $model = new $this->linked_model_type;

        if (! $model instanceof Model) {
            return null;
        }

        return $model->newQuery()->find($this->linked_model_id);
    }
}
