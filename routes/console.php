<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic staffing reset for completed recurring events
// Runs every hour to check if any event occurrences have completed
Schedule::job(new \App\Jobs\ResetStaffingForCompletedEvents())
    ->hourly()
    ->name('reset-completed-staffing')
    ->withoutOverlapping();

// Schedule pre-event reminders (2 hours before events)
// Runs every 5 minutes to catch events starting soon
Schedule::job(new \App\Jobs\SendPreEventReminders())
    ->everyFiveMinutes()
    ->name('send-pre-event-reminders')
    ->withoutOverlapping();
