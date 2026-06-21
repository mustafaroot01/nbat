<?php

namespace App\Services;

use App\Models\AppVersion;

class AppVersionService
{
    public function getAll()
    {
        return AppVersion::latest()->get();
    }

    public function store(array $data)
    {
        return AppVersion::create($data);
    }

    public function update($appVersion, array $data)
    {
        $appVersion->update($data);
        return $appVersion;
    }

    public function destroy($appVersion)
    {
        $appVersion->delete();
        return true;
    }
}
