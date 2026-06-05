<?php

namespace App\Models;

use Database\Factories\GameAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameAttempt extends Model
{
    /** @use HasFactory<GameAttemptFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'status' => 'started',
        'attempt_number' => 1,
        'score' => 0,
        'max_score' => 0,
        'duration_seconds' => 0,
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'duration_seconds' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(EducationalGame::class, 'educational_game_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GameAttemptAnswer::class);
    }
}
