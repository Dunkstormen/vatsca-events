<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $permissions = [
            'create-calendars', 'edit-calendars', 'delete-calendars',
            'manage-calendars', 'manage-events',
        ];
        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }
        
        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);
        $moderatorRole = Role::create(['name' => 'moderator']);
        $moderatorRole->givePermissionTo($permissions);
        Role::create(['name' => 'user']);
        
        // Fake HTTP to prevent Discord webhooks
        Http::fake([
            '*/webhooks/*' => Http::response(['success' => true], 200),
        ]);
        
        Queue::fake();
    }

    public function test_moderator_can_view_calendars_page()
    {
        $user = User::factory()->create();
        $user->assignRole('moderator');
        Calendar::factory()->create(['is_public' => true]);

        $response = $this->actingAs($user)->get('/calendars');

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_calendars_management_page()
    {
        Calendar::factory()->create(['is_public' => true]);

        $response = $this->get('/calendars');

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_create_calendar()
    {
        $user = User::factory()->create();
        $user->assignRole('admin'); // Only admins can create calendars

        $response = $this->actingAs($user)->post('/calendars', [
            'name' => 'Test Calendar',
            'description' => 'Test Description',
            'is_public' => true,
        ]);

        $response->assertRedirect('/calendars');
        $this->assertDatabaseHas('calendars', [
            'name' => 'Test Calendar',
            'created_by' => $user->id,
        ]);
    }

    public function test_moderator_cannot_create_calendar()
    {
        $user = User::factory()->create();
        $user->assignRole('moderator');

        $response = $this->actingAs($user)->post('/calendars', [
            'name' => 'Test Calendar',
            'description' => 'Test Description',
            'is_public' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_edit_any_calendar()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $otherUser = User::factory()->create();
        $otherUser->assignRole('admin');
        $calendar = Calendar::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($admin)->put("/calendars/{$calendar->id}", [
            'name' => 'Updated Calendar',
            'description' => 'Updated Description',
            'is_public' => false,
        ]);

        $response->assertRedirect('/calendars');
        $this->assertDatabaseHas('calendars', [
            'id' => $calendar->id,
            'name' => 'Updated Calendar',
        ]);
    }

    public function test_moderator_cannot_edit_calendar()
    {
        $moderator = User::factory()->create();
        $moderator->assignRole('moderator');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $calendar = Calendar::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($moderator)->put("/calendars/{$calendar->id}", [
            'name' => 'Hacked Calendar',
            'description' => 'Test',
            'is_public' => true,
        ]);

        $response->assertForbidden();
    }

    public function test_private_calendar_not_visible_to_other_users()
    {
        $user1 = User::factory()->create();
        $user1->assignRole('user');
        $user2 = User::factory()->create();
        $user2->assignRole('user');
        $calendar = Calendar::factory()->create([
            'created_by' => $user1->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($user2)->get("/calendars/{$calendar->id}");

        $response->assertForbidden();
    }
}
