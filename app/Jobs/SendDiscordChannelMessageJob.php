<?php

namespace App\Jobs;

use App\Services\DiscordBotClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDiscordChannelMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function __construct(
        public string $channelId,
        public string $content = '',
        public array $message = [],
    ) {
    }

    public function handle(DiscordBotClient $client): void
    {
        $client->sendChannelMessage($this->channelId, $this->content, $this->message);
    }
}
