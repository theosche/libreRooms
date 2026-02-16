<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Concerns\RedirectsBack;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerUserController extends Controller
{
    use RedirectsBack;

    /**
     * Display the list of users with access to the owner.
     */
    public function index(Owner $owner): View
    {
        $this->authorize('manageUsers', $owner);

        $owner->load(['users', 'contact']);
        $currentUser = auth()->user();

        return view('owners.users.index', [
            'owner' => $owner,
            'roles' => UserRole::cases(),
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Add a user to the owner or update their role.
     */
    public function store(Request $request, Owner $owner): RedirectResponse
    {
        $this->authorize('manageUsers', $owner);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(UserRole::cases(), 'value'))],
        ], [
            'email.exists' => __('No user found with this email.'),
        ]);

        // Check if user can add this role
        $this->authorize('addOwnerUser', [$owner, $validated['role']]);

        $user = User::where('email', $validated['email'])->first();

        // Check if user already has access
        $existingRole = $user->getOwnerRole($owner);
        if ($existingRole !== null) {
            // Check if current user can modify this target user's role (via policy)
            $this->authorize('removeOwnerUser', [$owner, $user]);

            // Update the role
            $user->owners()->updateExistingPivot($owner->id, ['role' => $validated['role']]);

            return $this->redirectBack('owners.users.index', ['owner' => $owner])
                ->with('success', __('User role updated.'));
        }

        // Attach user to owner
        $user->owners()->attach($owner->id, ['role' => $validated['role']]);

        return $this->redirectBack('owners.users.index', ['owner' => $owner])
            ->with('success', __('User added successfully.'));
    }

    /**
     * Remove a user from the owner.
     */
    public function destroy(Owner $owner, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $owner);

        // Check if user has access to this owner
        if (! $owner->users()->where('users.id', $user->id)->exists()) {
            return $this->redirectBack('owners.users.index', ['owner' => $owner])
                ->with('error', __('This user does not have access to this owner.'));
        }

        // Check if can remove this specific user
        $this->authorize('removeOwnerUser', [$owner, $user]);

        // Detach user from owner
        $owner->users()->detach($user->id);

        return $this->redirectBack('owners.users.index', ['owner' => $owner])
            ->with('success', __('User removed successfully.'));
    }
}
