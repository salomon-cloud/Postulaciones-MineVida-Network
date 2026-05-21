<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\DiscordNotificationService;
use Illuminate\Console\Command;

class OpenApplicationsCommand extends Command
{
    protected $signature = 'lumoryx:open-applications';

    protected $description = 'Abre globalmente las postulaciones.';

    public function handle(DiscordNotificationService $discord): int
    {
        $wasOpen = Setting::bool('applications_open', true);

        Setting::putValue('applications_open', true);

        if (! $wasOpen) {
            $discord->queueApplicationsWindowAnnouncement(true);
        }

        $this->info('Las postulaciones estan abiertas.');

        return self::SUCCESS;
    }
}
