<?php

namespace App\Http\Controllers;

use App\Enums\IdentityProviderDrivers;
use App\Models\IdentityProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IdentityProviderController extends Controller
{
    /**
     * Display a listing of identity providers.
     */
    public function index(): View
    {
        $providers = IdentityProvider::orderBy('name')->get();

        return view('identity-providers.index', [
            'providers' => $providers,
        ]);
    }

    /**
     * Show the form for creating a new identity provider.
     */
    public function create(): View
    {
        return view('identity-providers.form', [
            'provider' => null,
        ]);
    }

    /**
     * Store a newly created identity provider.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|string|in:' . implode(',', array_column(IdentityProviderDrivers::cases(), 'value')),
            'issuer_url' => 'required|url',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'enabled' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['enabled'] = $request->boolean('enabled');

        // Ensure slug is unique
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (IdentityProvider::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter;
            $counter++;
        }

        IdentityProvider::create($validated);

        return redirect()->route('identity-providers.index')->with('success', 'Fournisseur d\'identité créé avec succès.');
    }

    /**
     * Show the form for editing the specified identity provider.
     */
    public function edit(IdentityProvider $identityProvider): View
    {
        return view('identity-providers.form', [
            'provider' => $identityProvider,
        ]);
    }

    /**
     * Update the specified identity provider.
     */
    public function update(Request $request, IdentityProvider $identityProvider): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'driver' => 'required|string|in:' . implode(',', array_column(IdentityProviderDrivers::cases(), 'value')),
            'issuer_url' => 'required|url',
            'client_id' => 'required|string',
            'client_secret' => 'nullable|string',
            'enabled' => 'boolean',
        ]);

        $validated['enabled'] = $request->boolean('enabled');

        // Keep existing client_secret if not provided
        if (empty($validated['client_secret'])) {
            unset($validated['client_secret']);
        }

        // Update slug if name changed
        if ($validated['name'] !== $identityProvider->name) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure slug is unique (excluding current provider)
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (IdentityProvider::where('slug', $validated['slug'])->where('id', '!=', $identityProvider->id)->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        $identityProvider->update($validated);

        return redirect()->route('identity-providers.index')->with('success', 'Fournisseur d\'identité mis à jour avec succès.');
    }

    /**
     * Remove the specified identity provider.
     */
    public function destroy(IdentityProvider $identityProvider): RedirectResponse
    {
        $identityProvider->delete();

        return redirect()->route('identity-providers.index')->with('success', 'Fournisseur d\'identité supprimé avec succès.');
    }
}