<?php

namespace App\Jobs;

use App\Models\DiscordNotification;
use App\Services\DiscordBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendDiscordNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public int $notificationId,
        public string $content,
        public array $message = [],
    ) {
    }

    public function handle(DiscordBotClient $client): void
    {
        $notification = DiscordNotification::query()
            ->with(['application', 'user'])
            ->findOrFail($this->notificationId);

        try {
            if ($notification->type === 'staff_new_application') {
                $client->sendStaffChannelMessage($this->content, $notification->application_id, $this->message);
            } else {
                $client->sendDm($notification->discord_id, $notification->type, $this->content, $notification->application_id, $this->message);
            }

            $notification->update([
                'status' => 'sent',
                'error_message' => null,
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $notification->update([
                'status' => 'failed',
                'error_message' => str($exception->getMessage())->limit(1000)->toString(),
            ]);

            throw $exception;
        }
    }
}
