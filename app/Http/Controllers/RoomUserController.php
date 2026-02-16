<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsBack;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomUserController extends Controller
{
    use RedirectsBack;

    /**
     * Display the list of users with access to the room.
     */
    public function index(Room $room): View
    {
        $this->authorize('manageUsers', $room);

        $room->load(['users', 'owner.contact']);

        return view('rooms.users.index', [
            'room' => $room,
            'roles' => UserRole::cases(),
            'currentUser' => auth()->user(),
        ]);
    }

    /**
     * Add a user to the room or update their role.
     */
    public function store(Request $request, Room $room): RedirectResponse
    {
        $this->authorize('manageUsers', $room);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
        ], [
            'email.exists' => __('No user found with this email.'),
        ]);

        // Check if user can add this role
        $this->authorize('addRoomUser', [$room, $validated['role']]);

        $user = User::where('email', $validated['email'])->first();

        // Check if user already has access
        $existingRole = $user->getDirectRoomRole($room);
        if ($existingRole !== null) {
            // Check if current user can modify this target user's role (via policy)
            $this->authorize('removeRoomUser', [$room, $user]);

            // Update the role
            $room->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

            return $this->redirectBack('rooms.users.index', ['room' => $room])
                ->with('success', __('User role updated.'));
        }

        // Attach user to room
        $room->users()->attach($user->id, ['role' => $validated['role']]);

        return $this->redirectBack('rooms.users.index', ['room' => $room])
            ->with('success', __('User added successfully.'));
    }

    /**
     * Remove a user from the room.
     */
    public function destroy(Room $room, User $user): RedirectResponse
    {
        $this->authorize('removeRoomUser', [$room, $user]);

        // Check if user has access to this room
        if (! $room->users()->where('users.id', $user->id)->exists()) {
            return $this->redirectBack('rooms.users.index', ['room' => $room])
                ->with('error', __('This user does not have direct access to this room.'));
        }

        // Detach user from room
        $room->users()->detach($user->id);

        return $this->redirectBack('rooms.users.index', ['room' => $room])
            ->with('success', __('User removed successfully.'));
    }
}
