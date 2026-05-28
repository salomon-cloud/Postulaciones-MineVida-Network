<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        return $application->user_id === $user->id || $user->isAtLeast(UserRole::Reviewer);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function cancel(User $user, Application $application): bool
    {
        return $application->user_id === $user->id
            && in_array($application->status, [ApplicationStatus::Pending, ApplicationStatus::InReview], true);
    }

    public function viewAdmin(User $user): bool
    {
        return $user->isAtLeast(UserRole::Reviewer);
    }

    public function note(User $user): bool
    {
        return $user->isAtLeast(UserRole::Reviewer);
    }

    public function updateStatus(User $user): bool
    {
        return $user->isAtLeast(UserRole::Admin);
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->isAtLeast(UserRole::Admin);
    }

    public function manageSettings(User $user): bool
    {
        return $user->isAtLeast(UserRole::Owner);
    }
}
