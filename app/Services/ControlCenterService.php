<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ControlCenterService
{
    protected const CACHE_TTL = 3600; // 1 hour
    protected const CACHE_KEY = 'control_center_positions';

    protected string $apiUrl;
    protected ?string $apiToken;

    public function __construct()
    {
        $this->apiUrl = config('services.control_center.api_url') ?? 'https://cc.vatsim-scandinavia.org/api';
        $this->apiToken = config('services.control_center.api_token');
    }

    /**
     * Get all available positions from Control Center API
     */
    public function getPositions(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchPositionsFromApi();
        });
    }

    /**
     * Fetch positions from the Control Center API
     */
    protected function fetchPositionsFromApi(): array
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl . '/positions');

            if ($response->successful()) {
                $data = $response->json();
                
                // The API returns {data: [...positions]}
                if (isset($data['data']) && is_array($data['data'])) {
                    Log::info('Control Center API fetched successfully', [
                        'count' => count($data['data'])
                    ]);
                    return $data['data'];
                }
                
                Log::warning('Control Center API returned unexpected format', [
                    'data' => $data
                ]);
                return $this->getPlaceholderPositions();
            }

            Log::warning('Control Center API request failed', [
                'status' => $response->status(),
                'url' => $this->apiUrl . '/positions',
            ]);

            return $this->getPlaceholderPositions();
        } catch (\Exception $e) {
            Log::warning('Control Center API exception', [
                'message' => $e->getMessage(),
                'url' => $this->apiUrl . '/positions',
            ]);

            return $this->getPlaceholderPositions();
        }
    }

    /**
     * Get placeholder positions for testing
     */
    protected function getPlaceholderPositions(): array
    {
        return [
            [
                'id' => 'ENGM_ATIS',
                'name' => 'Oslo ATIS',
                'frequency' => '128.025',
                'type' => 'ATIS',
            ],
            [
                'id' => 'ENGM_DEL',
                'name' => 'Oslo Delivery',
                'frequency' => '121.905',
                'type' => 'DEL',
            ],
            [
                'id' => 'ENGM_GND',
                'name' => 'Oslo Ground',
                'frequency' => '121.700',
                'type' => 'GND',
            ],
            [
                'id' => 'ENGM_TWR',
                'name' => 'Oslo Tower',
                'frequency' => '118.300',
                'type' => 'TWR',
            ],
            [
                'id' => 'ENGM_APP',
                'name' => 'Oslo Approach',
                'frequency' => '119.100',
                'type' => 'APP',
            ],
            [
                'id' => 'ENOR_CTR',
                'name' => 'Norway Control',
                'frequency' => '133.700',
                'type' => 'CTR',
            ],
        ];
    }

    /**
     * Validate if a position ID exists
     */
    public function validatePositionId(string $positionId): bool
    {
        $positions = $this->getPositions();

        foreach ($positions as $position) {
            if ($position['id'] === $positionId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get position details by ID
     */
    public function getPositionById(string $positionId): ?array
    {
        $positions = $this->getPositions();

        foreach ($positions as $position) {
            if ($position['id'] === $positionId) {
                return $position;
            }
        }

        return null;
    }

    /**
     * Clear the positions cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Create a booking in Control Center
     * 
     * @param array $bookingData Booking data containing: cid, date, position, start_at, end_at, tag, source
     * @return int|null Booking ID if successful, null otherwise
     */
    public function createBooking(array $bookingData): ?int
    {
        if (!$this->apiToken) {
            Log::warning('Control Center API token not configured, skipping booking creation');
            return null;
        }

        try {
            Log::info('Creating booking in Control Center', ['data' => $bookingData]);

            $response = Http::timeout(10)
                ->withToken($this->apiToken)
                ->post($this->apiUrl . '/bookings/create', $bookingData);

            if ($response->successful()) {
                $responseData = $response->json();
                $bookingId = $responseData['booking']['id'] ?? $responseData['id'] ?? $responseData['data']['id'] ?? null;
                
                Log::info('Control Center booking created successfully', [
                    'cid' => $bookingData['cid'],
                    'position' => $bookingData['position'],
                    'booking_id' => $bookingId,
                    'response' => $responseData
                ]);
                
                return $bookingId;
            }

            Log::error('Control Center booking creation failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $bookingData
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Control Center booking exception', [
                'message' => $e->getMessage(),
                'data' => $bookingData
            ]);
            return null;
        }
    }

    /**
     * Delete a booking from Control Center
     * 
     * @param int $bookingId Control Center booking ID
     * @return bool True if successful, false otherwise
     */
    public function deleteBooking(int $bookingId): bool
    {
        if (!$this->apiToken) {
            Log::warning('Control Center API token not configured, skipping booking deletion');
            return false;
        }

        try {
            Log::info('Deleting booking from Control Center', [
                'booking_id' => $bookingId
            ]);

            $response = Http::timeout(10)
                ->withToken($this->apiToken)
                ->delete($this->apiUrl . '/bookings/' . $bookingId);

            if ($response->successful()) {
                Log::info('Control Center booking deleted successfully', [
                    'booking_id' => $bookingId
                ]);
                return true;
            }

            Log::error('Control Center booking deletion failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'booking_id' => $bookingId
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Control Center booking deletion exception', [
                'message' => $e->getMessage(),
                'booking_id' => $bookingId
            ]);
            return false;
        }
    }
}
