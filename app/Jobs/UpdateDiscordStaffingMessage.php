<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\DiscordBotNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateDiscordStaffingMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $eventId,
        public string $action = 'updated'
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(DiscordBotNotificationService $service): void
    {
        $event = Event::find($this->eventId);
        
        if ($event) {
            $service->notifyStaffingChanged($event, $this->action);
        }
    }
}
