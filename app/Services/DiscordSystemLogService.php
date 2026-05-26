<?php

namespace App\Services;

use App\Jobs\SendDiscordChannelMessageJob;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class DiscordSystemLogService
{
    public static function eventOptions(): array
    {
        return [
            'applications' => 'Postulaciones nuevas y canceladas',
            'status' => 'Cambios de estado',
            'interviews' => 'Entrevistas',
            'categories' => 'Categorias y preguntas',
            'settings' => 'Configuracion',
            'users' => 'Usuarios y roles',
            'selected' => 'Seleccionados',
            'auth' => 'Inicios y cierres de sesion',
            'discord' => 'Envios y anuncios de Discord',
        ];
    }

    public function queue(
        string $event,
        string $title,
        string $description,
        array $fields = [],
        string $tone = 'info',
        ?User $actor = null,
        ?Request $request = null,
    ): void {
        if (! $this->shouldLog($event)) {
            return;
        }

        $channelIds = $this->channelIds();

        if ($channelIds === []) {
            return;
        }

        $embed = [
            'color' => $this->color($tone),
            'author' => [
                'name' => config('app.name', 'MineVida Network').' - Logs del sistema',
                'icon_url' => $this->embedIconUrl(),
            ],
            'title' => $title,
            'description' => str($description)->limit(1400)->toString(),
            'fields' => $this->fields($fields, $actor, $request),
            'footer' => [
                'text' => 'Evento: '.$event,
                'icon_url' => $this->embedIconUrl(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        foreach ($channelIds as $channelId) {
            try {
                SendDiscordChannelMessageJob::dispatch($channelId, '', ['embeds' => [$embed]]);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    public function logApplicationEvent(
        string $event,
        string $title,
        string $description,
        \App\Models\Application $application,
        string $tone = 'info',
        ?User $actor = null,
        ?Request $request = null,
        array $extraFields = [],
    ): void {
        $this->queue($event, $title, $description, [
            'Usuario' => ($application->user?->discord_username ?? 'Sin usuario').' ('.$application->user?->discord_id.')',
            'Minecraft' => $application->minecraft_nick ?: '-',
            'Categoria' => $application->typeLabel(),
            'Estado' => $application->status->label(),
            'Panel' => route('admin.applications.show', $application, absolute: true),
            ...$extraFields,
        ], $tone, $actor, $request);
    }

    private function shouldLog(string $event): bool
    {
        if (! Setting::bool('discord_system_logs_enabled', (bool) config('services.lumoryx_bot.system_logs_enabled', false))) {
            return false;
        }

        $events = $this->enabledEvents();

        return in_array('*', $events, true) || in_array($event, $events, true);
    }

    private function enabledEvents(): array
    {
        $raw = (string) Setting::value(
            'discord_system_log_events',
            (string) config('services.lumoryx_bot.system_log_events', ''),
        );

        return collect(preg_split('/[\s,;]+/', $raw) ?: [])
            ->map(fn (string $event) => trim($event))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function channelIds(): array
    {
        $raw = trim((string) Setting::value('discord_system_log_channel_id', ''));

        if ($raw === '') {
            $raw = trim((string) config('services.lumoryx_bot.system_log_channel_id', ''));
        }

        if ($raw === '') {
            return [];
        }

        return collect(preg_split('/[\s,;]+/', $raw) ?: [])
            ->map(fn (string $channelId) => preg_replace('/\D+/', '', $channelId))
            ->filter(fn (?string $channelId) => $channelId !== null && preg_match('/^\d{16,25}$/', $channelId))
            ->unique()
            ->values()
            ->all();
    }

    private function fields(array $fields, ?User $actor, ?Request $request): array
    {
        $normalized = collect($fields)
            ->map(function ($value, $name) {
                if (is_array($value)) {
                    $value = collect($value)->filter()->implode("\n");
                }

                return [
                    'name' => str((string) $name)->limit(250)->toString(),
                    'value' => str((string) ($value ?: '-'))->limit(1000)->toString(),
                    'inline' => mb_strlen((string) $value) < 80,
                ];
            })
            ->values();

        if ($actor) {
            $normalized->push([
                'name' => 'Ejecutado por',
                'value' => $actor->name.' ('.$actor->role->label().')',
                'inline' => true,
            ]);
        }

        if ($request) {
            $normalized->push([
                'name' => 'IP',
                'value' => (string) $request->ip(),
                'inline' => true,
            ]);
        }

        return $normalized->take(25)->all();
    }

    private function color(string $tone): int
    {
        return match ($tone) {
            'success' => 0x22c55e,
            'warning' => 0xfacc15,
            'danger' => 0xef4444,
            'discord' => 0x5865f2,
            default => 0x38bdf8,
        };
    }

    private function embedIconUrl(): string
    {
        $configuredUrl = trim((string) config('services.lumoryx_bot.embed_icon_url', ''));

        return $configuredUrl !== '' ? $configuredUrl : 'attachment://minevida-logo.png';
    }
}
