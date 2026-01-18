<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $permissions = [
            'create-events', 'edit-events', 'delete-events',
            'create-calendars', 'edit-calendars', 'delete-calendars',
        ];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }
        
        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);
        Role::create(['name' => 'moderator']);
        Role::create(['name' => 'user']);
        
        // Prevent actual Discord webhook calls
        Http::fake([
            '*/webhooks/*' => Http::response(['success' => true], 200),
            '*/api/bookings/*' => Http::response(['booking' => ['id' => 12345]], 200),
        ]);
        
        // Fake queue to prevent jobs from running
        Queue::fake();
    }

    public function test_user_can_create_event()
    {
        $user = User::factory()->create();
        $user->assignRole('moderator'); // Moderators can create events
        $calendar = Calendar::factory()->create();

        $response = $this->actingAs($user)->post('/events', [
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'calendar_id' => $calendar->id,
        ]);
    }

    public function test_regular_user_cannot_create_event()
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // Regular users cannot create events
        $calendar = Calendar::factory()->create();

        $response = $this->actingAs($user)->post('/events', [
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(403);
    }

    public function test_event_with_banner_upload()
    {
        Storage::fake('local');
        
        $user = User::factory()->create();
        $user->assignRole('moderator'); // Moderators can create events
        $calendar = Calendar::factory()->create();

        // Create 16:9 image
        $file = UploadedFile::fake()->image('banner.jpg', 1920, 1080);

        $response = $this->actingAs($user)->post('/events', [
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'banner' => $file,
        ]);

        $response->assertRedirect();
        $event = Event::where('title', 'Test Event')->first();
        $this->assertNotNull($event->banner_path);
    }

    public function test_recurring_event_creation()
    {
        $user = User::factory()->create();
        $user->assignRole('moderator'); // Moderators can create events
        $calendar = Calendar::factory()->create();

        $response = $this->actingAs($user)->post('/events', [
            'calendar_id' => $calendar->id,
            'title' => 'Recurring Event',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'start_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'recurrence_rule' => 'FREQ=WEEKLY;INTERVAL=1;COUNT=5',
        ]);

        $response->assertRedirect();
        $event = Event::where('title', 'Recurring Event')->first();
        $this->assertNotNull($event->recurrence_rule);
        $this->assertTrue($event->isRecurring());
    }

    public function test_user_can_delete_their_event()
    {
        $user = User::factory()->create();
        $user->assignRole('moderator'); // Moderators can delete events
        $calendar = Calendar::factory()->create();
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/events/{$event->id}");

        $response->assertRedirect('/events');
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    public function test_regular_user_cannot_delete_event()
    {
        $user = User::factory()->create();
        $user->assignRole('user'); // Regular users cannot delete events
        $calendar = Calendar::factory()->create();
        $event = Event::factory()->create([
            'calendar_id' => $calendar->id,
        ]);

        $response = $this->actingAs($user)->delete("/events/{$event->id}");

        $response->assertStatus(403);
    }
}
