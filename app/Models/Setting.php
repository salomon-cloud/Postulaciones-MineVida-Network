<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function defaults(): array
    {
        return [
            'applications_open' => '1',
            'minimum_age' => '15',
            'reapply_cooldown_days' => '14',
            'require_discord_guild' => '0',
            'discord_announce_applications_window' => '0',
            'discord_announcement_channel_id' => '',
            'discord_announcement_role_id' => '',
            'discord_selected_channel_id' => '',
            'discord_selected_role_id' => '',
            'discord_selected_message' => '',
            'discord_open_message' => '',
            'discord_closed_message' => '',
        ];
    }

    public static function value(string $key, ?string $default = null): ?string
    {
        return Cache::remember("setting:{$key}", 60, function () use ($key, $default) {
            return self::query()->where('key', $key)->value('value')
                ?? self::defaults()[$key]
                ?? $default;
        });
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return filter_var(self::value($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function integer(string $key, int $default = 0): int
    {
        return (int) self::value($key, (string) $default);
    }

    public static function putValue(string $key, string|int|bool|null $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]);
        Cache::forget("setting:{$key}");
    }
}
