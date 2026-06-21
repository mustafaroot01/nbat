<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function loginUser(string $phone, string $password): ?array
    {
        $user = User::where('phone', $phone)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            return null;
        }

        $token = $user->createToken('user-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user->load('governorate'),
        ];
    }

    public function registerUser(array $data): array
    {
        if (isset($data['profile_photo'])) {
            $data['profile_photo'] = $data['profile_photo']->store('profiles', 'public');
        }

        $user = User::create($data);
        $token = $user->createToken('user-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user->load('governorate'),
        ];
    }

    public function loginAdmin(string $email, string $password): ?array
    {
        $admin = Admin::with('roles')->where('email', $email)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            return null;
        }

        if (!$admin->is_active) {
            return null;
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        $adminData = $admin->toArray();
        $roleName = $admin->roles->first()?->name ?? 'admin';
        // Materio frontend strictly expects 'role' property to be 'admin' or 'superadmin' to allow access
        // We bypass the strict check in frontend now, but keep this for backward compatibility
        $adminData['role'] = $roleName;

        $abilities = [];
        if ($admin->hasRole('superadmin')) {
            $abilities[] = ['action' => 'manage', 'subject' => 'all'];
        } else {
            $permissions = $admin->getAllPermissions();
            foreach ($permissions as $permission) {
                // permission name is like 'read_users'
                $parts = explode('_', $permission->name, 2);
                if (count($parts) === 2) {
                    $abilities[] = [
                        'action' => $parts[0], // e.g. read, write, create
                        'subject' => $parts[1] // e.g. users, plants
                    ];
                }
            }
            // Add basic read auth ability to allow seeing the dashboard home
            $abilities[] = ['action' => 'read', 'subject' => 'Auth'];
        }

        return [
            'token' => $token,
            'admin' => $adminData,
            'abilities' => $abilities,
        ];
    }
}
