<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'maintenance_mode' => 'false',
            'plant_approval_required' => 'true',
            'map_provider' => 'osm',
            'feature_otp_verification' => 'true',
            'otp_max_attempts' => '5',
            'otp_cooldown_seconds' => '25',
            'otp_expiry_minutes' => '5',
            'otp_api_key' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }
    }
}
