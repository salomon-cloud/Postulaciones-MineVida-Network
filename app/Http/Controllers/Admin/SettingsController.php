<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\DiscordNotificationService;
use App\Services\DiscordSystemLogService;
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
                'discord_system_logs_enabled' => Setting::bool('discord_system_logs_enabled', (bool) config('services.lumoryx_bot.system_logs_enabled', false)),
                'discord_system_log_channel_id' => Setting::value('discord_system_log_channel_id', ''),
                'discord_system_log_events' => Setting::value('discord_system_log_events', (string) config('services.lumoryx_bot.system_log_events', '')),
            ],
            'systemLogEvents' => DiscordSystemLogService::eventOptions(),
        ]);
    }

    public function update(
        UpdateSettingsRequest $request,
        DiscordNotificationService $discord,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $wasOpen = Setting::bool('applications_open', true);
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Setting::putValue($key, $value);
        }

        if ($wasOpen !== (bool) $validated['applications_open']) {
            $discord->queueApplicationsWindowAnnouncement((bool) $validated['applications_open']);
        }

        $systemLogs->queue(
            'settings',
            'Configuracion actualizada',
            'Se guardaron cambios en la configuracion general del sistema.',
            [
                'Postulaciones' => (bool) $validated['applications_open'] ? 'Abiertas' : 'Cerradas',
                'Edad minima' => $validated['minimum_age'].' anos',
                'Cooldown' => $validated['reapply_cooldown_days'].' dias',
                'Logs Discord' => (bool) $validated['discord_system_logs_enabled'] ? 'Activos' : 'Inactivos',
            ],
            'warning',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Configuracion actualizada.');
    }
}
