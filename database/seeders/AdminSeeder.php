<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Admin::create([
            'name' => 'المشرف العام',
            'email' => 'admin@izra3.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $admin->assignRole('superadmin');
    }
}
