<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRubricScore extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'score' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
