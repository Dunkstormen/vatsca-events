<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use App\Services\RecurringEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecurringEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::create(['name' => 'admin']);
        
        // Fake HTTP and Queue to prevent external calls
        Http::fake([
            '*/webhooks/*' => Http::response(['success' => true], 200),
        ]);
        
        Queue::fake();
    }

    public function test_recurring_event_generates_instances()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);

        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHours(2),
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=4',
        ]);

        $service = app(RecurringEventService::class);
        $instances = $service->generateInstances(
            $event->recurrence_rule,
            $event->start_datetime,
            now()->addMonths(2),
            10
        );

        $this->assertCount(4, $instances);
        $this->assertTrue($event->isRecurring());
    }

    public function test_recurring_event_calculates_next_occurrence()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);

        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'start_datetime' => now()->subWeek(),
            'end_datetime' => now()->subWeek()->addHours(2),
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=10',
        ]);

        $service = app(RecurringEventService::class);
        $instances = $service->generateInstances(
            $event->recurrence_rule,
            $event->start_datetime,
            now()->addMonths(2),
            10
        );

        // Should have future occurrences
        $futureInstances = array_filter($instances, fn($instance) => $instance['start']->isFuture());
        $this->assertNotEmpty($futureInstances);
    }

    public function test_recurring_event_validates_rrule()
    {
        $service = app(RecurringEventService::class);
        
        $this->assertTrue($service->validateRRule('FREQ=DAILY;COUNT=5'));
        $this->assertTrue($service->validateRRule('FREQ=WEEKLY;INTERVAL=2;COUNT=10'));
        $this->assertFalse($service->validateRRule('INVALID_RULE'));
        $this->assertFalse($service->validateRRule(''));
    }
}
