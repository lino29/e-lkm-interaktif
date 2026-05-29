<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'answer_json' => 'array',
            'score' => 'decimal:2',
            'rubric_score' => 'decimal:2',
            'keyword_score' => 'decimal:2',
            'similarity_score' => 'decimal:2',
        ];
    }

    public function assessmentAttempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
