# Getting Started with Dev Login

The fastest way to get started with development without OAuth configuration.

## Why Dev Login?

- **No OAuth setup needed** during development
- **Instant login** as any user role (admin, moderator, user)
- **Fast role testing** - switch between users easily
- **100% safe** - only works in local environment

## Quick Start

### Step 1: Create Test Users

```bash
php artisan tinker
```

Paste this entire block:

```php
// Create Admin User
$admin = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'vatsim_cid' => '1000001',
    'vatsim_rating' => 'S3',
]);
$admin->assignRole('admin');
echo "✅ Admin user created: admin@example.com\n";

// Create Moderator User
$mod = App\Models\User::create([
    'name' => 'Moderator User',
    'email' => 'mod@example.com',
    'vatsim_cid' => '1000002',
    'vatsim_rating' => 'C1',
]);
$mod->assignRole('moderator');
echo "✅ Moderator user created: mod@example.com\n";

// Create Regular User
$user = App\Models\User::create([
    'name' => 'Regular User',
    'email' => 'user@example.com',
    'vatsim_cid' => '1000003',
    'vatsim_rating' => 'S1',
]);
$user->assignRole('user');
echo "✅ Regular user created: user@example.com\n";

echo "\n🎉 All test users created! You can now use Dev Login\n";
```

Type `exit` to leave tinker.

### Step 2: Start Development Server

```bash
composer run dev
```

This starts:
- Laravel server (port 8000)
- Queue worker
- Log watcher
- Vite dev server

### Step 3: Use Dev Login

1. Open browser: http://localhost:8000
2. Click **"Dev Login"** in navbar (only visible in dev mode)
3. Select a user from dropdown
4. Click **"Generate Login Link"**
5. Click **"Click to Login"** button

You're in! 🎉

## Role Testing

### As Admin

Select `admin@example.com` in dev login:
- ✅ Create/edit/delete calendars
- ✅ Create/edit/delete events
- ✅ Manage staffings
- ✅ Unbook any position

### As Moderator

Use dev login with `mod@example.com`:
- ❌ Cannot manage calendars
- ✅ Create/edit/delete events
- ✅ Manage staffings
- ✅ Unbook any position

### As User

Use dev login with `user@example.com`:
- ❌ Cannot manage calendars
- ❌ Cannot manage events
- ❌ Cannot manage staffings
- ❌ Cannot unbook positions (bookings happen through Discord bot)
- ✅ Can view public calendars and events

## Alternative: Direct Login Link

Generate a login URL directly in tinker:

```bash
php artisan tinker
```

```php
$admin = App\Models\User::where('email', 'admin@example.com')->first();
echo $admin->createLoginLink() . "\n";
```

Copy the URL and paste in your browser for instant login!

## Troubleshooting

### "No users found" on dev login page

Run the create users commands in tinker (Step 1 above).

### Dev login not showing in navbar

- Check `APP_ENV=local` in `.env`
- Make sure you're running `npm run dev` (not `npm run build`)
- Hard refresh the page (Ctrl+Shift+R or Cmd+Shift+R)

### "404 Not Found" on /dev/login

Verify `APP_ENV=local` in your `.env` file.

### Link expired

Login links expire after 60 minutes. Generate a new one.

### Permission errors after login

Make sure roles were seeded:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Then assign role to your user:

```bash
php artisan tinker
```

```php
$user = User::where('email', 'admin@example.com')->first();
$user->assignRole('admin');
```

## Important Notes

- **Dev login only works in local environment** (`APP_ENV=local`)
- **Production uses Handover OAuth** exclusively
- **Completely safe** - production remains secure
- **Login links expire** after 60 minutes

## Ready to Develop!

You can now:
1. Test different user roles without multiple OAuth accounts
2. Quickly switch between users for testing
3. Develop features without waiting for OAuth callbacks
4. Test permissions and authorization logic easily

Happy coding! 🚀
