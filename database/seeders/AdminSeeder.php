<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'المشرف العام',
            'email' => 'admin@izra3.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'is_active' => true,
        ]);
    }
}
