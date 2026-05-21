<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class CreateOwnerCommand extends Command
{
    protected $signature = 'lumoryx:create-owner
        {--discord-id= : Discord ID del owner}
        {--name= : Nombre visible}
        {--email= : Email opcional}';

    protected $description = 'Crea o promueve el owner inicial del sistema.';

    public function handle(): int
    {
        $discordId = trim((string) ($this->option('discord-id') ?: $this->ask('Discord ID del owner')));
        $name = trim((string) ($this->option('name') ?: $this->ask('Nombre visible', 'MineVida Owner')));
        $email = trim((string) ($this->option('email') ?: $this->ask('Email opcional', '')));

        if ($discordId === '' || ! ctype_digit($discordId)) {
            $this->error('Debes indicar un Discord ID numerico valido.');

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['discord_id' => $discordId],
            [
                'name' => $name,
                'email' => $email !== '' ? $email : null,
                'discord_username' => $name,
                'role' => UserRole::Owner,
            ],
        );

        $this->info("Owner listo: {$user->name} ({$user->discord_id}).");

        return self::SUCCESS;
    }
}
