<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, Assessment $assessment): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $assessment->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid')
            && $assessment->is_published
            && $assessment->module->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru');
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->hasRole('guru') && (int) $assessment->module->created_by === (int) $user->id;
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }

    public function restore(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }

    public function forceDelete(User $user, Assessment $assessment): bool
    {
        return $this->delete($user, $assessment);
    }
}
