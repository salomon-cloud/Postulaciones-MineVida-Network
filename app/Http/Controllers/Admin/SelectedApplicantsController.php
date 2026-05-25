<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Setting;
use App\Services\DiscordNotificationService;
use App\Services\DiscordSystemLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SelectedApplicantsController extends Controller
{
    public function index(): View
    {
        $this->authorize('updateStatus', Application::class);

        $pending = Application::query()
            ->with('user')
            ->where('status', ApplicationStatus::Accepted->value)
            ->whereNull('selected_announced_at')
            ->latest('reviewed_at')
            ->latest()
            ->get();

        $announced = Application::query()
            ->with('user')
            ->where('status', ApplicationStatus::Accepted->value)
            ->whereNotNull('selected_announced_at')
            ->latest('selected_announced_at')
            ->limit(12)
            ->get();

        return view('admin.selected.index', [
            'pending' => $pending,
            'announced' => $announced,
            'selectedChannels' => $this->channelIds('discord_selected_channel_id')
                ?: $this->channelIds('discord_announcement_channel_id'),
        ]);
    }

    public function publish(
        Request $request,
        DiscordNotificationService $discord,
        DiscordSystemLogService $systemLogs,
    ): RedirectResponse
    {
        $this->authorize('updateStatus', Application::class);

        $validated = $request->validate([
            'applications' => ['required', 'array', 'min:1'],
            'applications.*' => [
                'integer',
                Rule::exists('applications', 'id')->where('status', ApplicationStatus::Accepted->value),
            ],
        ], [
            'applications.required' => 'Selecciona al menos una persona para anunciar.',
            'applications.min' => 'Selecciona al menos una persona para anunciar.',
        ]);

        if (($this->channelIds('discord_selected_channel_id') ?: $this->channelIds('discord_announcement_channel_id')) === []) {
            return back()->withErrors([
                'applications' => 'Configura un canal de seleccionados o un canal de anuncios en Ajustes.',
            ]);
        }

        $applications = Application::query()
            ->with('user')
            ->whereIn('id', $validated['applications'])
            ->where('status', ApplicationStatus::Accepted->value)
            ->whereNull('selected_announced_at')
            ->orderBy('type')
            ->orderBy('minecraft_nick')
            ->get();

        if ($applications->isEmpty()) {
            return back()->withErrors([
                'applications' => 'No hay seleccionados pendientes para anunciar con esa seleccion.',
            ]);
        }

        DB::transaction(function () use ($applications, $request) {
            $now = now();

            Application::query()
                ->whereIn('id', $applications->pluck('id'))
                ->update(['selected_announced_at' => $now]);

            foreach ($applications as $application) {
                $application->logs()->create([
                    'admin_id' => $request->user()->id,
                    'action' => 'selected_announced',
                    'old_status' => $application->status->value,
                    'new_status' => $application->status->value,
                    'description' => 'Seleccionado anunciado en Discord.',
                    'ip_address' => $request->ip(),
                    'user_agent' => str((string) $request->userAgent())->limit(512)->toString(),
                ]);
            }
        });

        $discord->queueSelectedApplicantsAnnouncement($applications);
        $systemLogs->queue(
            'selected',
            'Seleccionados anunciados',
            'Se publico el anuncio de personas seleccionadas en Discord.',
            [
                'Cantidad' => $applications->count(),
                'Usuarios' => $applications
                    ->map(fn (Application $application) => $application->minecraft_nick.' - '.$application->user?->discord_username)
                    ->take(15)
                    ->implode("\n"),
            ],
            'success',
            $request->user(),
            $request,
        );

        return back()->with('success', 'Seleccionados anunciados correctamente.');
    }

    private function channelIds(string $settingKey): array
    {
        return collect(preg_split('/[\s,;]+/', (string) Setting::value($settingKey, '')) ?: [])
            ->map(fn (string $channelId) => preg_replace('/\D+/', '', $channelId))
            ->filter(fn (?string $channelId) => $channelId !== null && preg_match('/^\d{16,25}$/', $channelId))
            ->unique()
            ->values()
            ->all();
    }
}
