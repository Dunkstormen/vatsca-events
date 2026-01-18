<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Development Login - Vatsca Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            <!-- Warning Banner -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Development Mode Only
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700">
                            This login method is only available in local development environment.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Development Login</h1>

                @if($users->isEmpty())
                    <div class="text-center py-8">
                        <p class="text-gray-600 mb-4">No users found. Create test users first.</p>
                        <div class="bg-gray-50 p-4 rounded-md text-left">
                            <p class="text-sm font-medium text-gray-900 mb-2">Run in tinker:</p>
                            <pre class="text-xs text-gray-800 overflow-x-auto">php artisan tinker

$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'vatsim_cid' => '1000001',
]);
$admin->assignRole('admin');</pre>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($users as $user)
                            <x-login-link 
                                :email="$user->email" 
                                :label="$user->name . ' (' . $user->email . ') - ' . ($user->roles->pluck('name')->join(', ') ?: 'No role')"
                            />
                        @endforeach
                    </div>
                @endif

                <!-- Instructions -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Create Test Users</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Use tinker to create test users with different roles:
                    </p>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <pre class="text-xs text-gray-800 overflow-x-auto">php artisan tinker

// Create admin user
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'vatsim_cid' => '1000001',
]);
$admin->assignRole('admin');

// Create moderator user
$mod = User::create([
    'name' => 'Moderator User',
    'email' => 'mod@example.com',
    'vatsim_cid' => '1000002',
]);
$mod->assignRole('moderator');

// Create regular user
$user = User::create([
    'name' => 'Regular User',
    'email' => 'user@example.com',
    'vatsim_cid' => '1000003',
]);
$user->assignRole('user');</pre>
                    </div>
                </div>

                <!-- Back Link -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <a href="/" class="text-sm text-indigo-600 hover:text-indigo-500">
                        ← Back to application
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
