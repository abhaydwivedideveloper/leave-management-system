<?php

namespace App\Policies;

use App\Enums\LeaveStatus;
use App\Enums\UserRole;
use App\Models\LeaveApplication;
use App\Models\User;

class LeaveApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveApplication $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $application->user->manager_id === $user->id;
        }

        return $application->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Employee;
    }

    public function cancel(User $user, LeaveApplication $application): bool
    {
        return $application->user_id === $user->id
            && $application->status === LeaveStatus::Pending;
    }

    public function review(User $user, LeaveApplication $application): bool
    {
        return $user->isManager()
            && $application->user->manager_id === $user->id
            && $application->status === LeaveStatus::Pending;
    }

    public function overrideStatus(User $user): bool
    {
        return $user->isAdmin();
    }
}
