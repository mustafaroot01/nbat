<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'users' => 'User Management',
            'plants' => 'Plants Management',
            'reports' => 'Reports Management',
            'banners' => 'Banners Management',
            'screen_ads' => 'Screen Ads',
            'campaigns' => 'Campaigns Management',
            'user_levels' => 'User Levels (Leaderboard)',
            'governorates' => 'Governorates',
            'settings' => 'System Settings',
            'notifications' => 'Notifications',
            'app_versions' => 'App Versions',
            'roles' => 'Roles & Permissions',
        ];

        $actions = ['read', 'write', 'create'];

        foreach ($modules as $moduleKey => $moduleLabel) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$moduleKey}", 'guard_name' => 'admin']);
            }
        }

        // Create roles and assign created permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $adminPermissions = [
            'read_users', 'write_users',
            'read_plants', 'write_plants', 'create_plants',
            'read_reports', 'write_reports',
        ];
        $adminRole->givePermissionTo($adminPermissions);

        // Assign superadmin role to existing admins
        $admins = Admin::all();
        foreach ($admins as $admin) {
            if (!$admin->hasRole('superadmin')) {
                $admin->assignRole('superadmin');
            }
        }
    }
}
