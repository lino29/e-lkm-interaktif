<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningUnit extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('order');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->orderBy('order');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }
}
