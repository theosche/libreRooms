<?php

namespace App\Http\Controllers;

use App\Models\IdentityProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Socialite\Socialite;

class ReauthController extends Controller
{
    /**
     * Show the re-authentication page.
     */
    public function show(): View
    {
        $user = auth()->user();

        // Get available OIDC providers for this user
        $oidcProviders = $user->authProviders()
            ->with('provider')
            ->get()
            ->filter(fn ($authProvider) => $authProvider->provider && $authProvider->provider->enabled)
            ->pluck('provider');

        return view('auth.reauthenticate', [
            'hasPassword' => $user->password !== null,
            'oidcProviders' => $oidcProviders,
        ]);
    }

    /**
     * Verify password for re-authentication.
     */
    public function verifyPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors([
                'password' => 'Le mot de passe est incorrect.',
            ]);
        }

        session(['reauthenticated_at' => now()]);

        $intended = session()->pull('reauthenticate.intended', route('profile'));

        return redirect($intended);
    }

    /**
     * Redirect to OIDC provider for re-authentication.
     * Uses the same callback URL as regular login to avoid needing a second redirect URI.
     */
    public function oidcRedirect(IdentityProvider $provider): RedirectResponse
    {
        abort_unless($provider->enabled, 404);

        // Verify user has this provider linked
        $user = auth()->user();
        $hasProvider = $user->authProviders()
            ->where('provider_id', $provider->id)
            ->exists();

        abort_unless($hasProvider, 403, 'Ce fournisseur d\'identité n\'est pas lié à votre compte.');

        // Mark this as a reauthentication flow in session
        // The OidcController callback will check this flag
        session([
            'reauthenticate.pending' => true,
            'reauthenticate.user_id' => $user->id,
            'reauthenticate.provider_id' => $provider->id,
        ]);

        // Use the standard callback URL (same as login)
        Config::set('services.'.$provider->driver, [
            'client_id' => $provider->client_id,
            'client_secret' => $provider->client_secret,
            'redirect' => route('auth.oidc.callback', $provider),
            'instance_uri' => $provider->issuer_url,
        ]);

        return Socialite::driver($provider->driver)
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }
}
