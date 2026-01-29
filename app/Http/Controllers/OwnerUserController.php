<?php

namespace App\Http\Controllers;

use App\Enums\OwnerUserRoles;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerUserController extends Controller
{
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
            'roles' => OwnerUserRoles::cases(),
            'isAdmin' => $currentUser->isAdminOf($owner),
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
            'role' => ['required', 'string', 'in:' . implode(',', array_column(OwnerUserRoles::cases(), 'value'))],
        ], [
            'email.exists' => 'Aucun utilisateur trouvé avec cette adresse email.',
        ]);

        // Check if user can add this role
        $this->authorize('addOwnerUser', [$owner, $validated['role']]);

        $user = User::where('email', $validated['email'])->first();
        $currentUser = auth()->user();

        // Check if it's the current user
        if ($user->id === $currentUser->id) {
            return redirect()->route('owners.users.index', $owner)
                ->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        // Check if user already has access
        $existingRole = $user->getRoleForOwner($owner);
        if ($existingRole !== null) {
            // User exists - update role if allowed
            $newRole = OwnerUserRoles::from($validated['role']);

            // Moderators can't overwrite a higher role with viewer
            if (! $currentUser->isAdminOf($owner)) {
                if ($existingRole->hasAtLeast(OwnerUserRoles::MODERATOR) && $newRole === OwnerUserRoles::VIEWER) {
                    return redirect()->route('owners.users.index', $owner)
                        ->with('error', 'Vous ne pouvez pas rétrograder un·e modérateur·ice ou admin en lecteur·ice.');
                }
            }

            // Update the role
            $user->owners()->updateExistingPivot($owner->id, ['role' => $validated['role']]);

            return redirect()->route('owners.users.index', $owner)
                ->with('success', 'Le rôle de l\'utilisateur a été mis à jour.');
        }

        // Attach user to owner
        $user->owners()->attach($owner->id, ['role' => $validated['role']]);

        return redirect()->route('owners.users.index', $owner)
            ->with('success', 'L\'utilisateur a été ajouté avec succès.');
    }

    /**
     * Remove a user from the owner.
     */
    public function destroy(Owner $owner, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $owner);

        // Check if user has access to this owner
        if (! $owner->users()->where('users.id', $user->id)->exists()) {
            return redirect()->route('owners.users.index', $owner)
                ->with('error', 'Cet utilisateur n\'a pas d\'accès à ce propriétaire.');
        }

        // Check if can remove this specific user
        $this->authorize('removeOwnerUser', [$owner, $user]);

        // Detach user from owner
        $owner->users()->detach($user->id);

        return redirect()->route('owners.users.index', $owner)
            ->with('success', 'L\'utilisateur a été retiré avec succès.');
    }
}
