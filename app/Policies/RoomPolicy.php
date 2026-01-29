<?php

namespace App\Policies;

use App\Enums\OwnerUserRoles;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    /**
     * Determine whether the user can view any rooms.
     * Always true - filtering is done in the query.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the "mine" tab (manageable rooms).
     * Requires moderator+ role on at least one owner.
     */
    public function viewMine(User $user): bool
    {
        return $user->canManageAnyOwner();
    }

    /**
     * Determine whether the user can view the room.
     * Public rooms: everyone can view.
     * Private rooms: global_admin, owner role, or direct room access.
     */
    public function view(?User $user, Room $room): bool
    {
        return $room->isAccessibleBy($user);
    }

    /**
     * Determine whether the user can create rooms.
     * Requires admin role on at least one owner.
     */
    public function create(User $user): bool
    {
        if ($user->is_global_admin) {
            return true;
        }

        return $user->owners()
            ->wherePivot('role', OwnerUserRoles::ADMIN->value)
            ->exists();
    }

    /**
     * Determine whether the user can update the room.
     * Requires admin role on the room's owner.
     */
    public function update(User $user, Room $room): bool
    {
        return $user->isAdminOf($room->owner);
    }

    /**
     * Determine whether the user can delete the room.
     * Requires admin role on the room's owner.
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->isAdminOf($room->owner);
    }

    /**
     * Determine whether the user can make a reservation on the room.
     * Requires view access and room must be active.
     */
    public function reserve(?User $user, Room $room): bool
    {
        if (! $room->active) {
            return false;
        }

        return $room->isAccessibleBy($user);
    }

    /**
     * Determine whether the user can manage users for the room.
     * Requires moderator or admin role on the room's owner.
     */
    public function manageUsers(User $user, Room $room): bool
    {
        return $user->hasOwnerRole($room->owner, OwnerUserRoles::MODERATOR);
    }

    /**
     * Determine whether the user can manage reservations for the room.
     * Requires moderator or admin role on the room's owner.
     */
    public function manageReservations(User $user, Room $room): bool
    {
        return $user->hasOwnerRole($room->owner, OwnerUserRoles::MODERATOR);
    }

    /**
     * Determine whether the user can add any role to room users.
     * Admins can add any role, moderators can only add viewers.
     */
    public function addRoomUser(User $user, Room $room, string $role): bool
    {
        if ($user->isAdminOf($room->owner)) {
            return true;
        }

        // Moderators can only add viewers
        if ($user->hasOwnerRole($room->owner, OwnerUserRoles::MODERATOR)) {
            return $role === 'viewer';
        }

        return false;
    }
}
