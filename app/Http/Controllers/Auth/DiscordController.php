<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class DiscordController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $provider = Socialite::driver('discord');
        $scopes = ['identify'];

        if (Setting::bool('require_discord_guild', false)) {
            $scopes[] = 'guilds.join';
        }

        $provider->scopes($scopes);

        if (method_exists($provider, 'withConsent')) {
            $provider->withConsent();
        }

        return $provider->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('home')->with('error', 'El login con Discord fue cancelado o rechazado.');
        }

        try {
            $discordUser = Socialite::driver('discord')->user();
        } catch (Throwable $exception) {
            Log::warning('Discord OAuth callback failed', [
                'message' => $exception->getMessage(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('home')->with('error', 'No se pudo iniciar sesion con Discord. Intentalo de nuevo.');
        }

        if (Setting::bool('require_discord_guild', false) && ! $this->ensureConfiguredGuildMember($discordUser->getId(), $discordUser->token)) {
            return redirect()->route('home')->with('error', 'No pudimos agregarte al servidor de Discord. Revisa que hayas autorizado el permiso y vuelve a intentarlo.');
        }

        $raw = $discordUser->getRaw();

        $user = User::query()->updateOrCreate(
            ['discord_id' => $discordUser->getId()],
            [
                'name' => $raw['global_name'] ?? $discordUser->getNickname() ?? $discordUser->getName(),
                'discord_username' => $raw['username'] ?? $discordUser->getName(),
                'discord_global_name' => $raw['global_name'] ?? null,
                'discord_avatar' => $raw['avatar'] ?? null,
                'discord_access_token' => $discordUser->token,
                'discord_refresh_token' => $discordUser->refreshToken,
                'last_login_at' => now(),
            ],
        );

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Sesion iniciada con Discord.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Sesion cerrada correctamente.');
    }

    private function ensureConfiguredGuildMember(string $discordUserId, string $accessToken): bool
    {
        $guildId = (string) config('services.discord.guild_id');
        $botToken = (string) config('services.discord.bot_token');

        if ($guildId === '' || $botToken === '') {
            Log::warning('Discord guild auto join is not configured', [
                'guild_id_configured' => $guildId !== '',
                'bot_token_configured' => $botToken !== '',
            ]);

            return false;
        }

        try {
            $response = Http::timeout(8)
                ->withToken($botToken, 'Bot')
                ->acceptJson()
                ->put("https://discord.com/api/v10/guilds/{$guildId}/members/{$discordUserId}", [
                    'access_token' => $accessToken,
                ]);
        } catch (Throwable $exception) {
            Log::warning('Discord guild auto join failed', ['message' => $exception->getMessage()]);

            return false;
        }

        if (! in_array($response->status(), [201, 204], true)) {
            Log::warning('Discord guild auto join returned error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return false;
        }

        return true;
    }
}
