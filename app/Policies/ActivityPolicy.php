<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $activity->learningUnit->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && $activity->learningUnit->module->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->hasRole('guru') && (int) $activity->learningUnit->module->created_by === (int) $user->id;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function restore(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function forceDelete(User $user, Activity $activity): bool
    {
        return $this->delete($user, $activity);
    }
}
