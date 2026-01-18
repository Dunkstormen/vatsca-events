<?php

namespace App\Jobs;

use App\Models\Staffing;
use App\Services\VatsimBookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncStaffingToVatsimJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Staffing $staffing
    ) {}

    /**
     * Execute the job.
     */
    public function handle(VatsimBookingService $vatsimBookingService): void
    {
        Log::info('Starting VATSIM booking sync', [
            'staffing_id' => $this->staffing->id,
            'attempt' => $this->attempts(),
        ]);

        $success = $vatsimBookingService->syncStaffing($this->staffing);

        if (!$success && $this->attempts() >= $this->tries) {
            Log::error('VATSIM booking sync failed after all retries', [
                'staffing_id' => $this->staffing->id,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('VATSIM booking sync job failed', [
            'staffing_id' => $this->staffing->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
