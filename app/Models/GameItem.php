<?php

namespace App\Models;

use Database\Factories\GameItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameItem extends Model
{
    /** @use HasFactory<GameItemFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'score' => 0,
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'correct_answer' => 'array',
            'score' => 'decimal:2',
            'time_limit_seconds' => 'integer',
            'sort_order' => 'integer',
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(EducationalGame::class, 'educational_game_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameAttemptAnswer::class);
    }
}
