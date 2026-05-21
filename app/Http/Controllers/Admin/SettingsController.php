<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\DiscordNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $this->authorize('manageSettings', \App\Models\Application::class);

        return view('admin.settings.edit', [
            'settings' => [
                'applications_open' => Setting::bool('applications_open', true),
                'minimum_age' => Setting::integer('minimum_age', 15),
                'reapply_cooldown_days' => Setting::integer('reapply_cooldown_days', 14),
                'require_discord_guild' => Setting::bool('require_discord_guild', false),
                'discord_announce_applications_window' => Setting::bool('discord_announce_applications_window', false),
                'discord_announcement_channel_id' => Setting::value('discord_announcement_channel_id', ''),
                'discord_announcement_role_id' => Setting::value('discord_announcement_role_id', ''),
                'discord_selected_channel_id' => Setting::value('discord_selected_channel_id', ''),
                'discord_selected_role_id' => Setting::value('discord_selected_role_id', ''),
                'discord_selected_message' => Setting::value('discord_selected_message', ''),
                'discord_open_message' => Setting::value('discord_open_message', ''),
                'discord_closed_message' => Setting::value('discord_closed_message', ''),
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request, DiscordNotificationService $discord): RedirectResponse
    {
        $wasOpen = Setting::bool('applications_open', true);
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Setting::putValue($key, $value);
        }

        if ($wasOpen !== (bool) $validated['applications_open']) {
            $discord->queueApplicationsWindowAnnouncement((bool) $validated['applications_open']);
        }

        return back()->with('success', 'Configuracion actualizada.');
    }
}
