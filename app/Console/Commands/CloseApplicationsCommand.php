<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\DiscordNotificationService;
use Illuminate\Console\Command;

class CloseApplicationsCommand extends Command
{
    protected $signature = 'lumoryx:close-applications';

    protected $description = 'Cierra globalmente las postulaciones.';

    public function handle(DiscordNotificationService $discord): int
    {
        $wasOpen = Setting::bool('applications_open', true);

        Setting::putValue('applications_open', false);

        if ($wasOpen) {
            $discord->queueApplicationsWindowAnnouncement(false);
        }

        $this->info('Las postulaciones estan cerradas.');

        return self::SUCCESS;
    }
}
