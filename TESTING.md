# Test Suite Documentation

## Overview

The test suite is fully configured to prevent external API calls and webhook spam during test execution.

## Authorization Model

### Production Behavior

In production, all authenticated users automatically receive the `user` role (see `AuthController::handleProviderCallback`):

```php
// Assign default role if user doesn't have any
if (!$user->hasAnyRole(['admin', 'moderator', 'user'])) {
    $user->assignRole('user');
}
```

### Roles and Permissions

1. **Admin** - Full system access
   - Can create, edit, and delete calendars
   - Can create, edit, and delete events
   - Can manage staffings
   - Can unbook any staffing position
   - Can manage users and roles

2. **Moderator** - Event and staffing management
   - **Cannot** create, edit, or delete calendars
   - **Can** create, edit, and delete events in existing calendars
   - Can manage staffings
   - Can unbook any staffing position

3. **User** - Basic access
   - **Cannot** create, edit, or delete calendars
   - **Cannot** create, edit, or delete events
   - Can view public calendars and events
   - **Cannot** book positions through the frontend (booking happens through Discord bot only)
   - **Cannot** unbook positions

### Authorization Rules

**Calendars:**
- ✅ Only **admins** can create calendars
- ✅ Only **admins** can edit calendars
- ✅ Only **admins** can delete calendars
- ✅ Public calendars are visible to everyone
- ✅ Private calendars are only visible to admins and the creator

**Events:**
- ✅ Only **admins and moderators** can create events
- ✅ Only **admins and moderators** can edit events
- ✅ Only **admins and moderators** can delete events
- ✅ Events in public calendars are visible to everyone

**Staffings:**
- ✅ Only **admins and moderators** can manage staffings (create sections, add/edit/delete positions)
- ✅ **Booking happens exclusively through the Discord bot** (no frontend booking)
- ✅ Only **admins and moderators** can unbook positions through the frontend
- ✅ Users can view staffing positions and their booking status

### Design Philosophy

This permission model prevents calendar proliferation and ensures controlled booking workflows:

1. **Admins organize** - Only admins can create and manage calendars, ensuring a clean organizational structure
2. **Moderators schedule** - Moderators can create events in existing calendars without calendar management overhead
3. **Users participate** - Regular users can view events and book positions through the Discord bot
4. **Bot-only booking** - All bookings must go through the Discord bot to maintain consistency and enable bot features
5. **Staff unbooking** - Moderators and admins can unbook any position to handle exceptional cases

### Test Convention

All tests should assign the appropriate role to users to match production behavior:

```php
$user = User::factory()->create();
$user->assignRole('user'); // Match production: all authenticated users get 'user' role
```

## Webhook and External API Protection

All test files have been configured with HTTP and Queue faking to prevent:

1. **Discord Webhook Calls** - All webhook URLs are intercepted and mocked
2. **Control Center API Calls** - All API requests to the Control Center are mocked
3. **Queue Job Execution** - All queued jobs (like Discord notifications) are faked and not actually executed

## Test Configuration

Each test file's `setUp()` method includes:

```php
// Fake HTTP requests (webhooks and external APIs)
Http::fake([
    '*/webhooks/*' => Http::response(['success' => true], 200),
    '*/api/bookings/*' => Http::response(['booking' => ['id' => 12345]], 200),
]);

// Fake queue jobs
Queue::fake();
```

## Permissions Setup

Tests that require specific permissions create them in the `setUp()` method:

```php
// Create permissions
$permissions = ['create-events', 'edit-events', 'delete-events'];
foreach ($permissions as $permission) {
    Permission::create(['name' => $permission]);
}

// Create roles and assign permissions
$adminRole = Role::create(['name' => 'admin']);
$adminRole->givePermissionTo($permissions);
```

## Running Tests

To run all tests:

```bash
php artisan test
```

To run specific test files:

```bash
php artisan test --filter=EventTest
php artisan test --filter=StaffingTest
```

To run a specific test:

```bash
php artisan test --filter=test_user_can_create_event
```

## Test Coverage

Current test coverage includes:

- **Calendar Management** - CRUD operations, permissions, visibility
- **Event Management** - Creation, editing, deletion, recurring events
- **Staffing Management** - Sections, positions, booking/unbooking
- **Discord Bookings** - CID-based bookings, scope methods
- **Recurring Events** - Instance generation, RRule validation
- **Staffing Resets** - Manual and automatic resets
- **Permissions** - Role-based access control

## Important Notes

- All HTTP requests during tests are mocked - no actual external API calls are made
- Queue jobs are not executed during tests - they are captured by Queue::fake()
- Database is reset between each test using RefreshDatabase trait
- Tests create their own permissions and roles to ensure isolation
