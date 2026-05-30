<?php

namespace App\Policies;

use App\Models\Progress;
use App\Models\User;

class ProgressPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, Progress $progress): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $progress->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && (int) $progress->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('murid');
    }

    public function update(User $user, Progress $progress): bool
    {
        return $user->hasRole('guru') && (int) $progress->module->created_by === (int) $user->id;
    }

    public function delete(User $user, Progress $progress): bool
    {
        return false;
    }

    public function restore(User $user, Progress $progress): bool
    {
        return false;
    }

    public function forceDelete(User $user, Progress $progress): bool
    {
        return false;
    }
}
