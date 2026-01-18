<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\ControlCenterService;
use App\Services\DiscordBotNotificationService;
use App\Services\RecurringEventService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ResetStaffingForCompletedEvents implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(
        RecurringEventService $recurringService,
        ControlCenterService $controlCenterService,
        DiscordBotNotificationService $discordBotService
    ): void
    {
        Log::info('Starting automatic staffing reset for completed events');

        // Get all recurring events with staffing
        $recurringEvents = Event::whereNotNull('recurrence_rule')
            ->whereHas('staffings')
            ->get();

        foreach ($recurringEvents as $event) {
            try {
                // Calculate the next occurrence
                $instances = $recurringService->generateInstances(
                    $event->recurrence_rule,
                    $event->start_datetime,
                    now()->addMonths(3),
                    10,
                    $event->cancelled_occurrences ?? []
                );

                if (empty($instances)) {
                    continue;
                }

                // Get the duration of the event
                $duration = $event->start_datetime->diffInMinutes($event->end_datetime);

                // Find the most recent past occurrence
                $lastCompletedOccurrence = null;
                $nextOccurrence = null;

                foreach ($instances as $instance) {
                    $occurrenceEnd = $instance['start']->copy()->addMinutes($duration);
                    
                    if ($occurrenceEnd->isPast()) {
                        $lastCompletedOccurrence = [
                            'start' => $instance['start'],
                            'end' => $occurrenceEnd
                        ];
                    } else {
                        if (!$nextOccurrence) {
                            $nextOccurrence = [
                                'start' => $instance['start'],
                                'end' => $occurrenceEnd
                            ];
                        }
                        break;
                    }
                }

                // If there was a completed occurrence and there's a next occurrence
                if ($lastCompletedOccurrence && $nextOccurrence) {
                    $timeSinceCompletion = now()->diffInHours($lastCompletedOccurrence['end']);
                    
                    // Reset if the event ended within the last 24 hours but at least 1 hour ago
                    if ($timeSinceCompletion >= 1 && $timeSinceCompletion <= 24) {
                        $this->resetEventStaffing($event, $controlCenterService, $discordBotService);
                        
                        Log::info('Reset staffing for completed event occurrence', [
                            'event_id' => $event->id,
                            'event_title' => $event->title,
                            'completed_at' => $lastCompletedOccurrence['end']->toDateTimeString(),
                            'next_occurrence' => $nextOccurrence['start']->toDateTimeString(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to process event for staffing reset', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Completed automatic staffing reset job');
    }

    /**
     * Reset all bookings for an event
     */
    private function resetEventStaffing(
        Event $event,
        ControlCenterService $controlCenterService,
        DiscordBotNotificationService $discordBotService
    ): void
    {
        // Get all booked positions
        $bookedPositions = $event->staffings()
            ->with('positions')
            ->get()
            ->flatMap(function ($staffing) {
                return $staffing->positions;
            })
            ->filter(function ($position) {
                return $position->isBooked();
            });

        // Delete Control Center bookings and clear position bookings
        foreach ($bookedPositions as $position) {
            // Delete from Control Center if there's a booking ID
            if ($position->control_center_booking_id) {
                try {
                    $controlCenterService->deleteBooking($position->control_center_booking_id);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete Control Center booking', [
                        'booking_id' => $position->control_center_booking_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Clear all booking fields
            $position->update([
                'booked_by_user_id' => null,
                'discord_user_id' => null,
                'vatsim_cid' => null,
                'control_center_booking_id' => null,
            ]);
        }

        // Update Discord message to reflect the reset
        try {
            $discordBotService->notifyStaffingChanged($event, 'updated');
        } catch (\Exception $e) {
            Log::warning('Failed to update Discord message after reset', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
