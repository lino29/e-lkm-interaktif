<?php

namespace App\Models;

use Database\Factories\EducationalGameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalGame extends Model
{
    /** @use HasFactory<EducationalGameFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GameItem::class)->orderBy('sort_order');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(GameAttempt::class);
    }
}
