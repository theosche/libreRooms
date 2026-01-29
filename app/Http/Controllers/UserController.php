<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Enums\OwnerUserRoles;
use App\Services\Settings\SettingsService;

class UserController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Get enabled identity providers for OIDC login
        $identityProviders = \App\Models\IdentityProvider::where('enabled', true)->get();

        return view('auth.login', [
            'identityProviders' => $identityProviders,
        ]);
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/')->with('success', 'Connexion réussie !');
        }

        return back()->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Configure mailer with system settings and send email verification
        app(SettingsService::class)->configureMailer();
        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')->with('success', 'Compte créé avec succès ! Veuillez vérifier votre email pour activer votre compte.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Vous êtes maintenant déconnecté.');
    }

    // ============================================
    // Admin User Management Methods
    // ============================================

    /**
     * Display a listing of users (global admin only)
     */
    public function index(Request $request): View
    {
        $query = User::with(['contacts', 'owners']);

        // Filter by owner
        if ($request->filled('owner_id')) {
            if ($request->input('owner_id') == "admin") {
                $query->where(function ($q) {
                    $q->has('owners')
                        ->orWhere('is_global_admin', true);
                });
            } elseif ($request->input('owner_id') == "not_admin") {
                $query->whereDoesntHave('owners')
                    ->where('is_global_admin', false);
            } elseif ($request->input('owner_id') == "global_admin") {
                $query->where('is_global_admin', true);
            } else {
                $query->whereHas('owners', function ($q) use ($request) {
                    $q->where('owners.id', $request->input('owner_id'));
                });
            }
        }

        $users = $query->orderBy('name', 'asc')->paginate(15)->appends($request->except('page'));

        // Get all owners for filter dropdown
        $owners = Owner::with('contact')->orderBy('id')->get();

        return view('users.index', [
            'users' => $users,
            'owners' => $owners,
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create(): View
    {
        $owners = Owner::with('contact')->orderBy('id')->get();

        return view('users.form', [
            'user' => null,
            'owners' => $owners,
            'ownerRoles' => OwnerUserRoles::cases(),
        ]);
    }

    /**
     * Store a newly created user in storage (admin creation)
     */
    public function store(Request $request): RedirectResponse
    {
        $roleValues = array_column(OwnerUserRoles::cases(), 'value');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(12)],
            'is_global_admin' => ['boolean'],
            'owners' => ['array'],
            'owners.*.id' => ['exists:owners,id'],
            'owners.*.role' => ['in:' . implode(',', $roleValues)],
        ]);

        // Create user with email verified (admin created)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_global_admin' => $request->boolean('is_global_admin'),
            'email_verified_at' => now(),
        ]);

        // Sync owners with roles
        $ownerSync = [];
        if (!empty($validated['owners'])) {
            foreach ($validated['owners'] as $ownerData) {
                $ownerSync[$ownerData['id']] = ['role' => $ownerData['role']];
            }
        }
        $user->owners()->sync($ownerSync);

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été créé avec succès.');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user): View
    {
        $user->load(['owners']);
        $owners = Owner::with('contact')->orderBy('id')->get();

        return view('users.form', [
            'user' => $user,
            'owners' => $owners,
            'ownerRoles' => OwnerUserRoles::cases(),
        ]);
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $roleValues = array_column(OwnerUserRoles::cases(), 'value');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::min(12)],
            'is_global_admin' => ['boolean'],
            'owners' => ['array'],
            'owners.*.id' => ['exists:owners,id'],
            'owners.*.role' => ['in:' . implode(',', $roleValues)],
        ]);

        // Update user
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Empêcher un utilisateur de retirer son propre statut de global_admin
        if ($user->id === auth()->id() && $user->is_global_admin && !$request->boolean('is_global_admin')) {
            return redirect()->route('users.edit', $user)
                ->with('error', 'Vous ne pouvez pas retirer votre propre statut d\'administrateur global.');
        }

        $user->is_global_admin = $request->boolean('is_global_admin');

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync owners with roles (ajoute, met à jour et supprime selon les changements)
        $ownerSync = [];
        if (!empty($validated['owners'])) {
            foreach ($validated['owners'] as $ownerData) {
                $ownerSync[$ownerData['id']] = ['role' => $ownerData['role']];
            }
        }
        $user->owners()->sync($ownerSync);

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été mis à jour avec succès.');
    }

    /**
     * Remove the specified user from storage
     * Used by admins only
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été supprimé avec succès.');
    }

    // ============================================
    // User Profile Management
    // ============================================

    /**
     * Show user's own profile
     */
    public function profile(): View
    {
        $user = auth()->user();
        if (! $user->is_global_admin) {
            return view('users.profile', ['user' => $user]);
        } else {
            $user->load(['owners']);
            $owners = Owner::with('contact')->orderBy('id')->get();

            return view('users.form', [
                'user' => $user,
                'owners' => $owners,
                'ownerRoles' => OwnerUserRoles::cases(),
            ]);
        }
    }

    /**
     * Update user's own profile
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);

        // Check if email changed
        $emailChanged = $user->email !== $validated['email'];

        // Update user
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // If email changed, mark as unverified and send new verification email
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Send email verification if email changed
        if ($emailChanged) {
            app(SettingsService::class)->configureMailer();
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('success', 'Profil mis à jour ! Un email de vérification a été envoyé à votre nouvelle adresse.');
        }

        return redirect()->route('profile')
            ->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Show the password change form (requires recent authentication)
     */
    public function showPasswordForm(): View
    {
        return view('users.password', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update user's password and invalidate other sessions
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Invalidate all other sessions for this user
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        return redirect()->route('profile')
            ->with('success', 'Mot de passe modifié avec succès. Vos autres sessions ont été déconnectées.');
    }

    /**
     * Delete user's own account
     */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Prevent global admin from deleting themselves (security)
        if ($user->is_global_admin) {
            return redirect()->route('profile')
                ->with('error', 'Les administrateurs globaux ne peuvent pas supprimer leur propre compte. Contactez un autre administrateur.');
        }

        // Logout and delete
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect('/')->with('success', 'Votre compte a été supprimé avec succès.');
    }
}
