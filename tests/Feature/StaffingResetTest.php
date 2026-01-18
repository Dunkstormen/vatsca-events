<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Staffing;
use App\Models\StaffingPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffingResetTest extends TestCase
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

    public function test_admin_can_reset_staffing_for_recurring_event()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);
        $position = StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'booked_by_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/events/{$event->id}/staffings/reset");

        $response->assertRedirect();
        $this->assertDatabaseHas('staffing_positions', [
            'id' => $position->id,
            'booked_by_user_id' => null,
        ]);
    }

    public function test_cannot_reset_staffing_for_non_recurring_event()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => null, // Non-recurring
        ]);

        $response = $this->actingAs($user)->post("/events/{$event->id}/staffings/reset");

        $response->assertSessionHasErrors();
    }

    public function test_reset_clears_discord_bookings()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);
        $position = StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'vatsim_cid' => 1234567,
            'discord_user_id' => '123456789',
        ]);

        $response = $this->actingAs($user)->post("/events/{$event->id}/staffings/reset");

        $response->assertRedirect();
        $this->assertDatabaseHas('staffing_positions', [
            'id' => $position->id,
            'vatsim_cid' => null,
            'discord_user_id' => null,
        ]);
    }
}
