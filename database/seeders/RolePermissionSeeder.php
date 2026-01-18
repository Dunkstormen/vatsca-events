<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Calendar permissions (admin only via policy)
            'create-calendars',
            'edit-calendars',
            'delete-calendars',
            'view-private-calendars',

            // Event permissions (admin and moderator via policy)
            'create-events',
            'edit-events',
            'delete-events',

            // Staffing permissions
            'manage-staffings',

            // User management
            'manage-users',
            'manage-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - has all permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Moderator role - can manage events and staffings, but NOT calendars
        $moderator = Role::create(['name' => 'moderator']);
        $moderator->givePermissionTo([
            'create-events',
            'edit-events',
            'delete-events',
            'manage-staffings',
        ]);

        // User role - basic permissions (no specific permissions needed - booking happens through Discord)
        $user = Role::create(['name' => 'user']);

        $this->command->info('Roles and permissions created successfully!');
    }
}
