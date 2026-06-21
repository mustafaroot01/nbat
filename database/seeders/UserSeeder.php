<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'علي حسن',
                'phone' => '+9647701234567',
                'password' => Hash::make('password123'),
                'governorate_id' => 1, // بغداد
                'is_active' => true,
            ],
            [
                'name' => 'محمد قاسم',
                'phone' => '+9647801234567',
                'password' => Hash::make('password123'),
                'governorate_id' => 2, // البصرة
                'is_active' => true,
            ],
            [
                'name' => 'فاطمة أحمد',
                'phone' => '+9647501234567',
                'password' => Hash::make('password123'),
                'governorate_id' => 4, // أربيل
                'is_active' => true,
            ],
            [
                'name' => 'عمر سعد',
                'phone' => '+9647901234567',
                'password' => Hash::make('password123'),
                'governorate_id' => 3, // نينوى
                'is_active' => true,
            ],
            [
                'name' => 'زينب محمود',
                'phone' => '+9647711234567',
                'password' => Hash::make('password123'),
                'governorate_id' => 5, // بابل
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(['phone' => $user['phone']], $user);
        }
    }
}
