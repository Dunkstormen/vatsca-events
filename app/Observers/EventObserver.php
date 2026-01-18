<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\DiscordNotificationService;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    public function __construct(
        protected DiscordNotificationService $discordService
    ) {}

    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        // Queue the Discord notification
        dispatch(function () use ($event) {
            // Refresh event with relationships
            $event->load(['calendar', 'creator']);
            $this->discordService->sendEventNotification($event, 'created');
        })->afterResponse();
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        // Only send notification if significant fields changed
        $significantFields = [
            'title',
            'short_description',
            'start_datetime',
            'end_datetime',
            'banner_path',
        ];

        $changed = false;
        foreach ($significantFields as $field) {
            if ($event->wasChanged($field)) {
                $changed = true;
                break;
            }
        }

        if ($changed) {
            dispatch(function () use ($event) {
                // Refresh event with relationships
                $event->load(['calendar', 'creator']);
                //$this->discordService->sendEventNotification($event, 'updated');
            })->afterResponse();
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        // Optionally send deletion notification
        // dispatch(function () use ($event) {
        //     $this->discordService->sendEventNotification($event, 'deleted');
        // })->afterResponse();
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}
