<?php

namespace App\Policies;

use App\Models\StudentAnswer;
use App\Models\User;

class StudentAnswerPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['guru', 'murid']);
    }

    public function view(User $user, StudentAnswer $studentAnswer): bool
    {
        if ($user->hasRole('guru')) {
            return (int) $studentAnswer->question->assessment->module->created_by === (int) $user->id;
        }

        return $user->hasRole('murid') && (int) $studentAnswer->student_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('murid');
    }

    public function update(User $user, StudentAnswer $studentAnswer): bool
    {
        return $user->hasRole('guru') && (int) $studentAnswer->question->assessment->module->created_by === (int) $user->id;
    }

    public function delete(User $user, StudentAnswer $studentAnswer): bool
    {
        return false;
    }

    public function restore(User $user, StudentAnswer $studentAnswer): bool
    {
        return false;
    }

    public function forceDelete(User $user, StudentAnswer $studentAnswer): bool
    {
        return false;
    }
}
