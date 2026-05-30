<?php

namespace App\Policies;

use App\Models\LearningUnit;
use App\Models\User;

class LearningUnitPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, LearningUnit $learningUnit): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $learningUnit->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && $learningUnit->module->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru');
    }

    public function update(User $user, LearningUnit $learningUnit): bool
    {
        return $user->hasRole('guru') && (int) $learningUnit->module->created_by === (int) $user->id;
    }

    public function delete(User $user, LearningUnit $learningUnit): bool
    {
        return $this->update($user, $learningUnit);
    }

    public function restore(User $user, LearningUnit $learningUnit): bool
    {
        return $this->update($user, $learningUnit);
    }

    public function forceDelete(User $user, LearningUnit $learningUnit): bool
    {
        return $this->delete($user, $learningUnit);
    }
}
