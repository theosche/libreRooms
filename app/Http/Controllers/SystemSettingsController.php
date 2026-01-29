<?php

namespace App\Http\Controllers;

use App\Models\SystemSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class SystemSettingsController extends Controller
{
    /**
     * Show the form for editing system settings.
     */
    public function edit(): View
    {
        $settings = app(SystemSettings::class);

        return view('system-settings.edit', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $settings = app(SystemSettings::class);

        $rules = [
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail' => 'required|string',
            'mail_pass' => $settings?->mail_pass ? 'nullable|string' : 'required|string',
            'dav_url' => 'nullable|string',
            'dav_user' => 'nullable|string',
            'dav_pass' => 'nullable|string',
            'webdav_user' => 'nullable|string',
            'webdav_pass' => 'nullable|string',
            'webdav_endpoint' => 'nullable|string',
            'webdav_save_path' => 'nullable|string',
            'timezone' => 'required|string',
            'currency' => 'required|string',
            'locale' => 'required|string',
        ];

        $validated = $request->validate($rules);

        // Remove empty password fields to keep existing values
        $passwordFields = ['mail_pass', 'dav_pass', 'webdav_pass'];
        foreach ($passwordFields as $field) {
            if (empty($validated[$field])) {
                unset($validated[$field]);
            }
        }

        if (!$settings) {
            $settings = SystemSettings::create($validated);
        } else {
            $settings->update($validated);
        }

        return redirect()->route('system-settings.edit')->with('success', 'Réglages système mis à jour avec succès.');
    }
}
