<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'kktp' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function learningUnits(): HasMany
    {
        return $this->hasMany(LearningUnit::class)->orderBy('order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->orderBy('order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function glossaries(): HasMany
    {
        return $this->hasMany(Glossary::class)->orderBy('order');
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class)->orderBy('order');
    }
}
