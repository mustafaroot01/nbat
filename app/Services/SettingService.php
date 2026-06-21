<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function getAll()
    {
        return Setting::pluck('value', 'key')->toArray();
    }

    public function update(array $settings)
    {
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
        return true;
    }

    public function getAppSettings()
    {
        return [
            'maintenance_mode' => Setting::getValue('maintenance_mode', 'false') === 'true',
            'plant_approval_required' => Setting::getValue('plant_approval_required', 'true') === 'true',
            'map_provider' => Setting::getValue('map_provider', 'osm'),
            'map_api_key' => $this->getMapApiKey(),
        ];
    }

    private function getMapApiKey()
    {
        $provider = Setting::getValue('map_provider', 'osm');
        return match ($provider) {
            'google' => Setting::getValue('google_maps_api_key'),
            'mapbox' => Setting::getValue('mapbox_api_key'),
            default => null,
        };
    }
}
