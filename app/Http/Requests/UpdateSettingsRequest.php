<?php

namespace App\Http\Requests;

use App\Services\DiscordSystemLogService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    public function rules(): array
    {
        return [
            'applications_open' => ['boolean'],
            'require_discord_guild' => ['boolean'],
            'discord_announce_applications_window' => ['boolean'],
            'discord_announcement_channel_id' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->boolean('discord_announce_applications_window') && blank($value)) {
                        $fail('Indica al menos un canal de anuncios para activar los avisos en Discord.');

                        return;
                    }

                    $this->validateSnowflakeList($value, $fail, 'canales de anuncios');
                },
            ],
            'discord_announcement_role_id' => ['nullable', 'regex:/^\d{16,25}$/'],
            'discord_selected_channel_id' => [
                'nullable',
                fn (string $attribute, mixed $value, \Closure $fail) => $this->validateSnowflakeList($value, $fail, 'canales de seleccionados'),
            ],
            'discord_selected_role_id' => ['nullable', 'regex:/^\d{16,25}$/'],
            'discord_selected_message' => ['nullable', 'string', 'max:1000'],
            'discord_open_message' => ['nullable', 'string', 'max:1000'],
            'discord_closed_message' => ['nullable', 'string', 'max:1000'],
            'discord_system_logs_enabled' => ['boolean'],
            'discord_system_log_channel_id' => [
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (
                        $this->boolean('discord_system_logs_enabled')
                        && blank($value)
                        && blank(config('services.lumoryx_bot.system_log_channel_id'))
                    ) {
                        $fail('Indica al menos un canal de logs o configura DISCORD_SYSTEM_LOG_CHANNEL_ID en el entorno.');

                        return;
                    }

                    $this->validateSnowflakeList($value, $fail, 'canales de logs');
                },
            ],
            'discord_system_log_events' => ['nullable', 'string', 'max:500'],
            'minimum_age' => ['required', 'integer', 'min:10', 'max:30'],
            'reapply_cooldown_days' => ['required', 'integer', 'min:0', 'max:365'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'applications_open' => $this->boolean('applications_open'),
            'require_discord_guild' => $this->boolean('require_discord_guild'),
            'discord_announce_applications_window' => $this->boolean('discord_announce_applications_window'),
            'discord_announcement_channel_id' => $this->cleanSnowflakeList('discord_announcement_channel_id'),
            'discord_announcement_role_id' => $this->cleanSnowflake('discord_announcement_role_id'),
            'discord_selected_channel_id' => $this->cleanSnowflakeList('discord_selected_channel_id'),
            'discord_selected_role_id' => $this->cleanSnowflake('discord_selected_role_id'),
            'discord_selected_message' => $this->cleanText('discord_selected_message'),
            'discord_open_message' => $this->cleanText('discord_open_message'),
            'discord_closed_message' => $this->cleanText('discord_closed_message'),
            'discord_system_logs_enabled' => $this->boolean('discord_system_logs_enabled'),
            'discord_system_log_channel_id' => $this->cleanSnowflakeList('discord_system_log_channel_id'),
            'discord_system_log_events' => $this->cleanEventList('discord_system_log_events'),
        ]);
    }

    public function messages(): array
    {
        return [
            'discord_announcement_role_id.regex' => 'El rol a mencionar debe ser un ID numerico de Discord.',
            'discord_selected_role_id.regex' => 'El rol de seleccionados debe ser un ID numerico de Discord.',
            'discord_selected_message.max' => 'El mensaje de seleccionados no puede superar los 1000 caracteres.',
            'discord_open_message.max' => 'El mensaje de apertura no puede superar los 1000 caracteres.',
            'discord_closed_message.max' => 'El mensaje de cierre no puede superar los 1000 caracteres.',
            'discord_system_log_events.max' => 'La lista de eventos de logs es demasiado larga.',
        ];
    }

    private function cleanSnowflake(string $key): ?string
    {
        $value = preg_replace('/\D+/', '', (string) $this->input($key, ''));

        return $value !== '' ? $value : null;
    }

    private function cleanSnowflakeList(string $key): ?string
    {
        $parts = preg_split('/[\s,;]+/', trim((string) $this->input($key, ''))) ?: [];
        $values = collect($parts)
            ->map(fn (string $value) => preg_replace('/\D+/', '', $value))
            ->filter()
            ->unique()
            ->values();

        return $values->isNotEmpty() ? $values->implode("\n") : null;
    }

    private function validateSnowflakeList(mixed $value, \Closure $fail, string $label): void
    {
        if (blank($value)) {
            return;
        }

        $parts = preg_split('/\R+/', trim((string) $value)) ?: [];

        foreach ($parts as $part) {
            if (! preg_match('/^\d{16,25}$/', $part)) {
                $fail('Los '.$label.' deben ser IDs numericos de Discord, uno por linea.');

                return;
            }
        }
    }

    private function cleanText(string $key): ?string
    {
        $value = trim(strip_tags((string) $this->input($key, '')));

        return $value !== '' ? $value : null;
    }

    private function cleanEventList(string $key): ?string
    {
        $allowed = array_keys(DiscordSystemLogService::eventOptions());
        $events = collect((array) $this->input($key, []))
            ->map(fn (string $event) => trim($event))
            ->filter(fn (string $event) => in_array($event, $allowed, true))
            ->unique()
            ->values();

        return $events->isNotEmpty() ? $events->implode("\n") : null;
    }
}
