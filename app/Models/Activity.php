<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'order' => 'integer',
            'answer_schema' => 'array',
        ];
    }

    public function learningUnit(): BelongsTo
    {
        return $this->belongsTo(LearningUnit::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ActivityAnswer::class);
    }
}
