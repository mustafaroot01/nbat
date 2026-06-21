<?php

namespace App\Repositories;

use App\Models\Governorate;

class GovernorateRepository
{
    public function getAll()
    {
        return Governorate::all();
    }

    public function findById(int $id)
    {
        return Governorate::findOrFail($id);
    }

    public function update(Governorate $governorate, array $data)
    {
        $governorate->update($data);
        return $governorate;
    }
}
