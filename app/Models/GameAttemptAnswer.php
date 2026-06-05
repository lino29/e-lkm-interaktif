<?php

namespace App\Models;

use Database\Factories\GameAttemptAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAttemptAnswer extends Model
{
    /** @use HasFactory<GameAttemptAnswerFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'is_correct' => false,
        'score' => 0,
        'hint_used' => false,
    ];

    protected function casts(): array
    {
        return [
            'answer' => 'array',
            'is_correct' => 'boolean',
            'score' => 'decimal:2',
            'time_spent_seconds' => 'integer',
            'hint_used' => 'boolean',
            'answered_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(GameAttempt::class, 'game_attempt_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(GameItem::class, 'game_item_id');
    }
}
