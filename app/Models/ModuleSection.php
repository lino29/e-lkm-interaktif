<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleSection extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'is_visible' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
