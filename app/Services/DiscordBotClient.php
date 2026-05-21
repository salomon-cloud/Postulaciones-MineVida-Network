<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DiscordBotClient
{
    public function sendDm(string $discordId, string $messageType, string $content, int|string|null $applicationId = null, array $message = []): array
    {
        return $this->post('/send-dm', [
            'discord_id' => $discordId,
            'type' => $messageType,
            'content' => $content,
            'application_id' => $applicationId,
        ] + $message);
    }

    public function sendStaffChannelMessage(string $content, int|string|null $applicationId = null, array $message = []): array
    {
        return $this->post('/send-staff-channel-message', [
            'content' => $content,
            'application_id' => $applicationId,
        ] + $message);
    }

    public function sendChannelMessage(string $channelId, string $content = '', array $message = []): array
    {
        return $this->post('/send-channel-message', [
            'channel_id' => $channelId,
            'content' => $content,
        ] + $message);
    }

    private function post(string $endpoint, array $payload): array
    {
        $baseUrl = rtrim((string) config('services.lumoryx_bot.api_url'), '/');
        $token = (string) config('services.lumoryx_bot.internal_token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('La API interna del bot no esta configurada.');
        }

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->asJson()
                ->withToken($token)
                ->post($baseUrl.$endpoint, $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se pudo conectar con el bot de Discord.', previous: $exception);
        }

        if ($response->failed()) {
            $message = $response->json('error') ?: 'El bot respondio con HTTP '.$response->status();
            throw new RuntimeException($message);
        }

        $json = $response->json() ?? ['success' => true];

        if (($json['success'] ?? true) === false) {
            throw new RuntimeException((string) ($json['error'] ?? 'El bot no pudo enviar el mensaje.'));
        }

        return $json;
    }
}
