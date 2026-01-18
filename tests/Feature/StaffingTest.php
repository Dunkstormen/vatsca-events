<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Staffing;
use App\Models\StaffingPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $moderatorRole = Role::create(['name' => 'moderator']);
        $userRole = Role::create(['name' => 'user']);
        
        Permission::create(['name' => 'manage-staffings']);
        
        $adminRole->givePermissionTo(['manage-staffings']);
        $moderatorRole->givePermissionTo(['manage-staffings']);
        
        // Fake HTTP and Queue to prevent external calls
        Http::fake([
            '*/webhooks/*' => Http::response(['success' => true], 200),
            '*/api/bookings/*' => Http::response(['booking' => ['id' => 12345]], 200),
        ]);
        
        \Illuminate\Support\Facades\Queue::fake();
    }

    public function test_admin_can_create_staffing()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5', // Must be recurring
        ]);

        $response = $this->actingAs($user)->post("/events/{$event->id}/staffings", [
            'name' => 'Early Shift',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('staffings', [
            'event_id' => $event->id,
            'name' => 'Early Shift',
        ]);
    }

    public function test_admin_can_add_position_to_staffing()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        $calendar = Calendar::factory()->create(['created_by' => $user->id]);
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5', // Must be recurring
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);

        $response = $this->actingAs($user)->post("/staffings/{$staffing->id}/positions", [
            'position_id' => 'ENGM_TWR',
            'position_name' => 'Oslo Tower',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('staffing_positions', [
            'staffing_id' => $staffing->id,
            'position_id' => 'ENGM_TWR',
        ]);
    }

    public function test_moderator_can_unbook_any_position()
    {
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');
        
        $calendar = Calendar::factory()->create();
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);
        $position = StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'vatsim_cid' => 1234567, // Booked via Discord
            'discord_user_id' => '987654321',
        ]);

        $response = $this->actingAs($moderator)->delete("/positions/{$position->id}/book");

        $response->assertRedirect();
        $this->assertDatabaseHas('staffing_positions', [
            'id' => $position->id,
            'vatsim_cid' => null,
            'discord_user_id' => null,
        ]);
    }

    public function test_regular_user_cannot_unbook_position()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        
        $calendar = Calendar::factory()->create();
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);
        $position = StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'vatsim_cid' => 1234567,
        ]);

        $response = $this->actingAs($user)->delete("/positions/{$position->id}/book");

        $response->assertStatus(403);
    }
}
