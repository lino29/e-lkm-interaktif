<?php

namespace App\Livewire\Guru;

use App\Models\LearningUnitGrade;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class ReviewNotificationBadge extends Component
{
    #[On('kb-notification-read')]
    public function render()
    {
        return view('livewire.guru.review-notification-badge', [
            'unreadCount' => LearningUnitGrade::query()
                ->whereNull('score')
                ->whereHas('learningUnit.module', fn (Builder $query) => $query->where('created_by', auth()->id()))
                ->count(),
        ]);
    }
}
