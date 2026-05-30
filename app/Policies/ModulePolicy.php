<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, Module $module): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && $module->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasRole('guru');
    }

    public function update(User $user, Module $module): bool
    {
        return $user->hasRole('guru') && (int) $module->created_by === (int) $user->id;
    }

    public function delete(User $user, Module $module): bool
    {
        return $this->update($user, $module);
    }

    public function restore(User $user, Module $module): bool
    {
        return $this->update($user, $module);
    }

    public function forceDelete(User $user, Module $module): bool
    {
        return $this->delete($user, $module);
    }
}
