<?php

namespace App\Http\Controllers;

use App\Models\IdentityProvider;
use App\Models\User;
use App\Models\UserAuthProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Socialite;

class OidcController extends Controller
{
    public function redirect(Request $request, IdentityProvider $provider)
    {
        abort_unless($provider->enabled, 404);

        $this->injectConfig($provider);

        return Socialite::driver($provider->driver)
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request, ?IdentityProvider $provider): RedirectResponse
    {
        $this->injectConfig($provider);
        $oidcUser = Socialite::driver($provider->driver)->user();

        // Check if this is a reauthentication flow
        if (session('reauthenticate.pending')) {
            return $this->handleReauthCallback($oidcUser, $provider);
        }

        // Regular login flow
        $email = $oidcUser->getEmail();
        $user_auth_provider = UserAuthProvider::where('provider_id', $provider->id)
            ->where('provider_sub', $oidcUser->getId())->first();
        if ($user_auth_provider) {
            $user = $user_auth_provider->user;
        } else {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $oidcUser->getName() ?? $oidcUser->getNickname(),
                'email' => $email,
                'password' => null,
                'email_verified_at' => now(),
            ]);
        } else {
            if ($user->email_verified_at === null) {
                return redirect()->route('login')
                    ->with('error', 'Un compte existe déjà avec cet email mais il n\'est pas vérifié. Si vous ne pouvez pas vérifier l\'adresse mail, réinitialisez le mot de passe.');
            }
            // Fusion avec compte existant si même email et vérifié
        }

        $user->authProviders()->updateOrCreate(
            [
                'provider_id' => $provider->id,
                'provider_sub' => $oidcUser->getId(),
            ],
            [
                'access_token' => encrypt($oidcUser->token),
                'refresh_token' => encrypt($oidcUser->refreshToken),
            ]
        );

        // Capture intended URL before session regeneration
        $intendedUrl = session('url.intended');

        Auth::login($user);
        $request->session()->regenerate();

        // Determine redirect URL
        $redirectUrl = $intendedUrl ?: route('rooms.index');

        // Use query parameter for flash message (survives session regeneration)
        return redirect($redirectUrl.(str_contains($redirectUrl, '?') ? '&' : '?').'login_success=1');
    }

    /**
     * Handle OIDC callback for re-authentication flow.
     */
    private function handleReauthCallback($oidcUser, IdentityProvider $provider): RedirectResponse
    {
        $expectedUserId = session()->pull('reauthenticate.user_id');
        $expectedProviderId = session()->pull('reauthenticate.provider_id');
        session()->forget('reauthenticate.pending');

        // Verify the provider matches
        if ($expectedProviderId !== $provider->id) {
            return redirect()->route('reauthenticate')
                ->with('error', 'Fournisseur d\'identité invalide.');
        }

        // Verify user is still logged in
        if (! auth()->check() || auth()->id() !== $expectedUserId) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        // Verify the OIDC sub matches the current user's linked provider
        $user = auth()->user();
        $authProvider = $user->authProviders()
            ->where('provider_id', $provider->id)
            ->where('provider_sub', $oidcUser->getId())
            ->first();

        if (! $authProvider) {
            return redirect()->route('reauthenticate')
                ->with('error', 'L\'identité OIDC ne correspond pas à votre compte.');
        }

        // Update tokens
        $authProvider->update([
            'access_token' => encrypt($oidcUser->token),
            'refresh_token' => encrypt($oidcUser->refreshToken),
        ]);

        // Re-authentication successful
        session(['reauthenticated_at' => now()]);

        $intended = session()->pull('reauthenticate.intended', route('profile'));

        return redirect($intended);
    }

    private function injectConfig(IdentityProvider $provider): void
    {
        Config::set('services.'.$provider->driver, [
            'client_id' => $provider->client_id,
            'client_secret' => $provider->client_secret,
            'redirect' => route('auth.oidc.callback', $provider),
            'instance_uri' => $provider->issuer_url,
        ]);
    }
}
