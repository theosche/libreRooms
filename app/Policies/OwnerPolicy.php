<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Owner;
use App\Models\User;

class OwnerPolicy
{
    /**
     * Determine whether the user can view any owners.
     * Requires moderator+ role on at least one owner.
     */
    public function viewAny(User $user): bool
    {
        return $user->canModerateAnyOwner();
    }

    /**
     * Determine whether the user can view the owner.
     * Requires any role on the owner.
     */
    public function view(User $user, Owner $owner): bool
    {
        return $user->canViewOwner($owner);
    }

    /**
     * Determine whether the user can create owners.
     * Only global admins (handled by Gate::before).
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the owner.
     * Requires admin role on the owner.
     */
    public function update(User $user, Owner $owner): bool
    {
        return $user->canAdminOwner($owner);
    }

    /**
     * Determine whether the user can delete the owner.
     * Requires admin role on the owner.
     */
    public function delete(User $user, Owner $owner): bool
    {
        return $user->canAdminOwner($owner);
    }

    /**
     * Determine whether the user can share the owner with other users.
     * Requires admin role on the owner.
     */
    public function share(User $user, Owner $owner): bool
    {
        return $user->canAdminOwner($owner);
    }

    /**
     * Determine whether the user can manage rooms for the owner.
     * Requires admin role on the owner.
     */
    public function manageRooms(User $user, Owner $owner): bool
    {
        return $user->canAdminOwner($owner);
    }

    /**
     * Determine whether the user can manage users for the owner.
     * Requires moderator or admin role on the owner.
     */
    public function manageUsers(User $user, Owner $owner): bool
    {
        return $user->canModerateOwner($owner);
    }

    /**
     * Determine whether the user can add a user with the given role.
     * Admins can add any role, moderators can only add viewers.
     */
    public function addOwnerUser(User $user, Owner $owner, string $role): bool
    {
        if ($user->canAdminOwner($owner)) {
            return true;
        }

        // Moderators can only add viewers
        if ($user->canModerateOwner($owner)) {
            return $role === UserRole::VIEWER->value;
        }

        return false;
    }

    /**
     * Determine whether the user can remove another user from the owner.
     * Admins can remove anyone except themselves.
     * Moderators can only remove viewers.
     */
    public function removeOwnerUser(User $user, Owner $owner, User $targetUser): bool
    {
        // Can't remove yourself
        if ($user->id === $targetUser->id) {
            return false;
        }

        if ($user->canAdminOwner($owner)) {
            return true;
        }

        // Moderators can only remove viewers
        if ($user->canModerateOwner($owner)) {
            $targetRole = $targetUser->getOwnerRole($owner);

            return $targetRole === UserRole::VIEWER;
        }

        return false;
    }
}
