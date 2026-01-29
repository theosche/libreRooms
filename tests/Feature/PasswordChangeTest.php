<?php

use App\Models\IdentityProvider;
use App\Models\User;
use App\Models\UserAuthProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a global admin to satisfy the setup middleware
    User::factory()->globalAdmin()->create();
});

it('redirects to reauthenticate when accessing password form without recent auth', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile.password'));

    $response->assertRedirect(route('reauthenticate'));
    expect(session('reauthenticate.intended'))->toBe(route('profile.password'));
});

it('allows access to password form after recent authentication', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['reauthenticated_at' => now()])
        ->get(route('profile.password'));

    $response->assertSuccessful();
    $response->assertViewIs('users.password');
});

it('denies access to password form if reauth is expired', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['reauthenticated_at' => now()->subMinutes(15)])
        ->get(route('profile.password'));

    $response->assertRedirect(route('reauthenticate'));
});

it('shows password form on reauthenticate page for user with password', function () {
    $user = User::factory()->create(['password' => Hash::make('password123456')]);

    $response = $this->actingAs($user)->get(route('reauthenticate'));

    $response->assertSuccessful();
    $response->assertSee('Confirmer votre identité');
    $response->assertSee('name="password"', false);
});

it('verifies password correctly and sets session', function () {
    $user = User::factory()->create(['password' => Hash::make('correctpassword')]);

    session(['reauthenticate.intended' => route('profile.password')]);

    $response = $this->actingAs($user)->post(route('reauthenticate.password'), [
        'password' => 'correctpassword',
    ]);

    $response->assertRedirect(route('profile.password'));
    expect(session('reauthenticated_at'))->not->toBeNull();
});

it('rejects incorrect password during reauthentication', function () {
    $user = User::factory()->create(['password' => Hash::make('correctpassword')]);

    $response = $this->actingAs($user)->post(route('reauthenticate.password'), [
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('password');
    expect(session('reauthenticated_at'))->toBeNull();
});

it('updates password successfully and invalidates other sessions', function () {
    $user = User::factory()->create(['password' => Hash::make('oldpassword12')]);

    // Simulate another session for this user
    $otherSessionId = 'other-session-id';
    DB::table('sessions')->insert([
        'id' => $otherSessionId,
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test',
        'payload' => base64_encode(serialize([])),
        'last_activity' => time(),
    ]);

    $response = $this->actingAs($user)
        ->withSession(['reauthenticated_at' => now()])
        ->put(route('profile.password.update'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertRedirect(route('profile'));
    $response->assertSessionHas('success');

    // Verify password was updated
    $user->refresh();
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();

    // Verify other session was deleted
    expect(DB::table('sessions')->where('id', $otherSessionId)->exists())->toBeFalse();
});

it('validates password minimum length on update', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['reauthenticated_at' => now()])
        ->put(route('profile.password.update'), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

    $response->assertSessionHasErrors('password');
});

it('validates password confirmation on update', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['reauthenticated_at' => now()])
        ->put(route('profile.password.update'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

    $response->assertSessionHasErrors('password');
});

it('shows oidc buttons on reauthenticate page for oidc user', function () {
    $user = User::factory()->create(['password' => null]);

    $provider = IdentityProvider::create([
        'name' => 'Test Provider',
        'slug' => 'test-provider',
        'driver' => 'keycloak',
        'issuer_url' => 'https://example.com',
        'client_id' => 'test-client',
        'client_secret' => 'test-secret',
        'enabled' => true,
    ]);

    UserAuthProvider::create([
        'user_id' => $user->id,
        'provider_id' => $provider->id,
        'provider_sub' => 'test-sub-123',
        'access_token' => encrypt('test-access-token'),
        'refresh_token' => encrypt('test-refresh-token'),
    ]);

    $response = $this->actingAs($user)->get(route('reauthenticate'));

    $response->assertSuccessful();
    $response->assertSee('Se reconnecter avec Test Provider');
});

it('shows both password form and oidc buttons for user with both auth methods', function () {
    $user = User::factory()->create(['password' => Hash::make('password123456')]);

    $provider = IdentityProvider::create([
        'name' => 'Test Provider',
        'slug' => 'test-provider',
        'driver' => 'keycloak',
        'issuer_url' => 'https://example.com',
        'client_id' => 'test-client',
        'client_secret' => 'test-secret',
        'enabled' => true,
    ]);

    UserAuthProvider::create([
        'user_id' => $user->id,
        'provider_id' => $provider->id,
        'provider_sub' => 'test-sub-123',
        'access_token' => encrypt('test-access-token'),
        'refresh_token' => encrypt('test-refresh-token'),
    ]);

    $response = $this->actingAs($user)->get(route('reauthenticate'));

    $response->assertSuccessful();
    $response->assertSee('name="password"', false);
    $response->assertSee('Se reconnecter avec Test Provider');
    $response->assertSee('OU');
});

it('shows password change link on profile page', function () {
    $user = User::factory()->create(['password' => Hash::make('password123456')]);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertSuccessful();
    $response->assertSee('Sécurité');
    $response->assertSee('Modifier le mot de passe');
    $response->assertSee(route('profile.password'));
});

it('shows define password button for oidc-only user', function () {
    $user = User::factory()->create(['password' => null]);

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertSuccessful();
    $response->assertSee('Définir un mot de passe');
});

it('does not show password fields in profile form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile'));

    $response->assertSuccessful();
    // Password fields should not appear in the profile form
    $response->assertDontSee('Modifier le mot de passe (optionnel)');
});

it('redirects to intended url after successful reauthentication', function () {
    $user = User::factory()->create(['password' => Hash::make('password123456')]);

    // First, try to access password form (should redirect to reauth)
    $this->actingAs($user)->get(route('profile.password'));

    // Now reauthenticate
    $response = $this->actingAs($user)->post(route('reauthenticate.password'), [
        'password' => 'password123456',
    ]);

    $response->assertRedirect(route('profile.password'));
});
