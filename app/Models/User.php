<?php

namespace App\Models;

use App\Enums\OwnerUserRoles;
use App\Enums\RoomUserRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_global_admin',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_global_admin' => 'boolean',
        ];
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class)
            ->withPivot('role');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }

    public function authProviders(): HasMany
    {
        return $this->hasMany(UserAuthProvider::class);
    }

    /**
     * Rooms this user has direct access to (via room_user pivot).
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the user's role for a specific owner.
     */
    public function getRoleForOwner(Owner $owner): ?OwnerUserRoles
    {
        $pivot = $this->owners()
            ->where('owners.id', $owner->id)
            ->first()
            ?->pivot;

        if (! $pivot) {
            return null;
        }

        return OwnerUserRoles::tryFrom($pivot->role);
    }

    /**
     * Get the user's direct role for a specific room.
     */
    public function getRoleForRoom(Room $room): ?RoomUserRoles
    {
        $pivot = $this->rooms()
            ->where('rooms.id', $room->id)
            ->first()
            ?->pivot;

        if (! $pivot) {
            return null;
        }

        return RoomUserRoles::tryFrom($pivot->role);
    }

    /**
     * Check if user has at least the given role on an owner.
     */
    public function hasOwnerRole(Owner $owner, OwnerUserRoles $minRole): bool
    {
        if ($this->is_global_admin) {
            return true;
        }

        $role = $this->getRoleForOwner($owner);

        return $role !== null && $role->hasAtLeast($minRole);
    }

    /**
     * Check if user is admin of a specific owner.
     */
    public function isAdminOf(Owner $owner): bool
    {
        return $this->hasOwnerRole($owner, OwnerUserRoles::ADMIN);
    }
    public function canManageOwner(Owner $owner): bool
    {
        return $this->hasOwnerRole($owner, OwnerUserRoles::MODERATOR);
    }
    public function canViewOwner(Owner $owner): bool
    {
        return $this->hasOwnerRole($owner, OwnerUserRoles::VIEWER);
    }

    /**
     * Check if user can manage reservations for a room (moderator or admin of owner).
     */
    public function canManageReservationsFor(Room $room): bool
    {
        return $this->hasOwnerRole($room->owner, OwnerUserRoles::MODERATOR);
    }

    /**
     * Check if user has admin rights for a reservation.
     */
    public function hasAdminRightsFor(Reservation $reservation): bool
    {
        return $this->isAdminOf($reservation->room->owner);
    }

    /**
     * Get owner IDs where user has at least the given role.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function getOwnerIdsWithMinRole(OwnerUserRoles $minRole): \Illuminate\Support\Collection
    {
        if ($this->is_global_admin) {
            return Owner::pluck('id');
        }

        return $this->owners()
            ->get()
            ->filter(fn ($owner) => OwnerUserRoles::tryFrom($owner->pivot->role)?->hasAtLeast($minRole))
            ->pluck('id');
    }

    /**
     * Get owner IDs where user has any role.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function getOwnerIdsWithAnyRole(): \Illuminate\Support\Collection
    {
        if ($this->is_global_admin) {
            return Owner::pluck('id');
        }

        return $this->owners()->pluck('owners.id');
    }

    /**
     * Check if user can manage at least one owner (has moderator+ role).
     */
    public function canManageAnyOwner(): bool
    {
        if ($this->is_global_admin) {
            return true;
        }

        return $this->owners()
            ->get()
            ->contains(fn ($owner) => OwnerUserRoles::tryFrom($owner->pivot->role)?->hasAtLeast(OwnerUserRoles::MODERATOR));
    }
}

