<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Single flat settings screen — no per-row identifier, so no route-model binding like
 * Dining/Pages. KEYS is the only source of truth for which settings exist; validate()
 * already drops anything outside it, and the update loop only ever touches these five
 * keys regardless of what a client submits, so no arbitrary setting row can be created.
 */
class SettingsController extends Controller
{
    private const KEYS = ['phone', 'whatsapp', 'email', 'address', 'google_maps_url', 'expedia_url', 'booking_com_url', 'agoda_url'];

    public function edit(): View
    {
        $settings = collect(self::KEYS)->mapWithKeys(
            fn (string $key) => [$key => Setting::get($key, '')]
        );

        return view('admin.settings.edit', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
            'whatsapp' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'expedia_url' => ['nullable', 'url:https', 'max:500'],
            'booking_com_url' => ['nullable', 'url:https', 'max:500'],
            'agoda_url' => ['nullable', 'url:https', 'max:500'],
        ]);

        foreach (self::KEYS as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $data[$key] ?? '']);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Site settings updated.');
    }
}
