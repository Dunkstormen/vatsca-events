<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffingRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $adminRole = Role::create(['name' => 'admin']);
        Permission::create(['name' => 'manage-staffings']);
        $adminRole->givePermissionTo('manage-staffings');
        
        // Fake HTTP and Queue to prevent external calls
        Http::fake([
            '*/webhooks/*' => Http::response(['success' => true], 200),
            '*/api/bookings/*' => Http::response(['booking' => ['id' => 12345]], 200),
        ]);
        
        Queue::fake();
    }

    public function test_staffing_page_only_accessible_for_recurring_events()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => null, // Non-recurring
        ]);

        $response = $this->actingAs($user)->get("/events/{$event->id}/staffings");

        $response->assertRedirect("/events/{$event->id}");
        $response->assertSessionHas('error');
    }

    public function test_staffing_page_accessible_for_recurring_events()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
        ]);

        $response = $this->actingAs($user)->get("/events/{$event->id}/staffings");

        $response->assertOk();
    }

    public function test_staffing_creation_only_allowed_for_recurring_events()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => null, // Non-recurring
        ]);

        $response = $this->actingAs($user)->post("/events/{$event->id}/staffings", [
            'name' => 'Test Staffing',
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('staffings', [
            'event_id' => $event->id,
            'name' => 'Test Staffing',
        ]);
    }

    public function test_staffing_creation_allowed_for_recurring_events()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
        ]);

        $response = $this->actingAs($user)->post("/events/{$event->id}/staffings", [
            'name' => 'Test Staffing',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('staffings', [
            'event_id' => $event->id,
            'name' => 'Test Staffing',
        ]);
    }
}
