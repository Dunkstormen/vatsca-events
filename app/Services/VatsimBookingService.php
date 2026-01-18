<?php

namespace App\Services;

use App\Jobs\SyncStaffingToVatsimJob;
use App\Models\Staffing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VatsimBookingService
{
    /**
     * Queue a staffing for syncing to VATSIM
     */
    public function queueSync(Staffing $staffing): void
    {
        SyncStaffingToVatsimJob::dispatch($staffing);
    }

    /**
     * Sync staffing to VATSIM booking API
     */
    public function syncStaffing(Staffing $staffing): bool
    {
        $apiUrl = config('services.vatsim_booking.api_url');
        $apiKey = config('services.vatsim_booking.api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            Log::warning('VATSIM Booking API not configured', [
                'staffing_id' => $staffing->id,
            ]);
            return false;
        }

        try {
            $event = $staffing->event;
            $positions = $staffing->positions;

            $bookingData = [
                'event' => [
                    'title' => $event->title,
                    'description' => $event->short_description,
                    'start_time' => $event->start_datetime->toIso8601String(),
                    'end_time' => $event->end_datetime->toIso8601String(),
                ],
                'positions' => $positions->map(function ($position) use ($event) {
                    return [
                        'position' => $position->position_id,
                        'name' => $position->position_name,
                        'start_time' => $event->start_datetime->toIso8601String(),
                        'end_time' => $event->end_datetime->toIso8601String(),
                    ];
                })->toArray(),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($apiUrl . '/bookings', $bookingData);

            if ($response->successful()) {
                $staffing->update([
                    'synced_to_vatsim' => true,
                    'synced_at' => now(),
                ]);

                Log::info('Staffing synced to VATSIM successfully', [
                    'staffing_id' => $staffing->id,
                    'event_id' => $event->id,
                ]);

                return true;
            }

            Log::error('VATSIM Booking API request failed', [
                'staffing_id' => $staffing->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('VATSIM Booking sync exception', [
                'staffing_id' => $staffing->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update existing VATSIM booking
     */
    public function updateBooking(Staffing $staffing): bool
    {
        // For now, we'll use the same sync method
        // In production, you might want a separate PUT/PATCH endpoint
        return $this->syncStaffing($staffing);
    }

    /**
     * Delete VATSIM booking
     */
    public function deleteBooking(Staffing $staffing): bool
    {
        $apiUrl = config('services.vatsim_booking.api_url');
        $apiKey = config('services.vatsim_booking.api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
            ->timeout(30)
            ->delete($apiUrl . '/bookings/' . $staffing->id);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('VATSIM Booking delete exception', [
                'staffing_id' => $staffing->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
