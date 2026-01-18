<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Staffing;
use App\Models\StaffingPosition;
use App\Models\User;
use App\Services\ControlCenterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscordBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::create(['name' => 'admin']);
        Permission::create(['name' => 'manage-staffings']);
        
        // Mock Control Center API and Discord webhooks
        Http::fake([
            '*/api/bookings/create' => Http::response(['booking' => ['id' => 12345]], 200),
            '*/api/bookings/*' => Http::response([], 200),
            '*/webhooks/*' => Http::response(['success' => true], 200),
        ]);
        
        \Illuminate\Support\Facades\Queue::fake();
    }

    public function test_position_can_be_booked_via_discord_with_cid()
    {
        $calendar = Calendar::factory()->create();
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
            'discord_staffing_message_id' => '999888777',
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);
        $position = StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'position_id' => 'ENGM_TWR',
            'booked_by_user_id' => null,
        ]);

        // Simulate Discord bot booking
        $response = $this->postJson('/api/staffing', [
            'position' => 'ENGM_TWR',
            'cid' => 1234567,
            'discord_user_id' => '987654321',
            'message_id' => '999888777',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('staffing_positions', [
            'id' => $position->id,
            'vatsim_cid' => 1234567,
            'discord_user_id' => '987654321',
        ]);
    }

    public function test_position_is_marked_as_booked_with_cid()
    {
        $position = StaffingPosition::factory()->create([
            'booked_by_user_id' => null,
            'vatsim_cid' => 1234567,
        ]);

        $this->assertTrue($position->isBooked());
    }

    public function test_position_is_marked_as_booked_with_user_id()
    {
        $user = User::factory()->create();
        $position = StaffingPosition::factory()->create([
            'booked_by_user_id' => $user->id,
            'vatsim_cid' => null,
        ]);

        $this->assertTrue($position->isBooked());
    }

    public function test_available_scope_excludes_discord_bookings()
    {
        $staffing = Staffing::factory()->create();
        
        // Available position
        StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'booked_by_user_id' => null,
            'vatsim_cid' => null,
        ]);
        
        // Booked via Discord
        StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'booked_by_user_id' => null,
            'vatsim_cid' => 1234567,
        ]);

        $available = StaffingPosition::available()->get();
        
        $this->assertCount(1, $available);
    }

    public function test_booked_scope_includes_discord_bookings()
    {
        $staffing = Staffing::factory()->create();
        $user = User::factory()->create();
        
        // Booked by user
        StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'booked_by_user_id' => $user->id,
            'vatsim_cid' => null,
        ]);
        
        // Booked via Discord
        StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'booked_by_user_id' => null,
            'vatsim_cid' => 1234567,
        ]);

        $booked = StaffingPosition::booked()->get();
        
        $this->assertCount(2, $booked);
    }

    public function test_position_cannot_be_double_booked()
    {
        $calendar = Calendar::factory()->create();
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'recurrence_rule' => 'FREQ=WEEKLY;COUNT=5',
            'discord_staffing_message_id' => '999888777',
        ]);
        $staffing = Staffing::factory()->create(['event_id' => $event->id]);
        $position = StaffingPosition::factory()->create([
            'staffing_id' => $staffing->id,
            'position_id' => 'ENGM_TWR',
            'vatsim_cid' => 1111111, // Already booked
        ]);

        // Try to book again
        $response = $this->postJson('/api/staffing', [
            'position' => 'ENGM_TWR',
            'cid' => 2222222,
            'discord_user_id' => '123456789',
            'message_id' => '999888777',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Position already booked']);
    }
}
