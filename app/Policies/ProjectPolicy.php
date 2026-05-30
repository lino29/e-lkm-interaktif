<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $project->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && (int) $project->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('murid');
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $project->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid')
            && (int) $project->user_id === (int) $user->id
            && $project->status !== 'reviewed';
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('murid')
            && (int) $project->user_id === (int) $user->id
            && $project->status === 'draft';
    }

    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
