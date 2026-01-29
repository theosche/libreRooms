<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Auth;


class PasswordResetController extends Controller
{
    /**
     * Affiche le formulaire de demande de réinitialisation
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoie le lien de réinitialisation par email
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Configure le mailer avec les paramètres système
        app(SettingsService::class)->configureMailer();

        // Envoie le lien de réinitialisation
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Mail de réinitialisation envoyé')
            : back()->with('error', 'Email inexistant ou erreur');
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Réinitialise le mot de passe
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(12)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->email_verified_at = $user->email_verified_at ?? now();
                $user->save();

                event(new PasswordReset($user));

                // Connexion automatique de l'utilisateur
                Auth::login($user);
                $request->session()->regenerate();
            }
        );

        return $status === Password::PASSWORD_RESET ?
            redirect()->route('rooms.index')->with('success', 'Mot de passe mis à jour avec succès. Vous êtes maintenant connecté.')
            : back()->with('error', 'Impossible de mettre à jour le mot de passe');
    }
}
