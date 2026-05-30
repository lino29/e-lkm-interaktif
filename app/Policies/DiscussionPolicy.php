<?php

namespace App\Policies;

use App\Models\Discussion;
use App\Models\User;

class DiscussionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $discussion->learningUnit->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && $discussion->learningUnit->module->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function update(User $user, Discussion $discussion): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $discussion->learningUnit->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && (int) $discussion->user_id === (int) $user->id;
    }

    public function delete(User $user, Discussion $discussion): bool
    {
        return $this->update($user, $discussion);
    }

    public function restore(User $user, Discussion $discussion): bool
    {
        return false;
    }

    public function forceDelete(User $user, Discussion $discussion): bool
    {
        return false;
    }
}
