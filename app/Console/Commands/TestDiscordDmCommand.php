<?php

namespace App\Console\Commands;

use App\Services\DiscordBotClient;
use Illuminate\Console\Command;
use Throwable;

class TestDiscordDmCommand extends Command
{
    protected $signature = 'lumoryx:test-discord-dm {discord_id} {--message=Prueba de DM desde MineVida Network.}';

    protected $description = 'Envia un DM de prueba mediante la API interna del bot.';

    public function handle(DiscordBotClient $client): int
    {
        try {
            $result = $client->sendDm(
                (string) $this->argument('discord_id'),
                'test',
                (string) $this->option('message'),
                null,
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('DM enviado correctamente.');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
