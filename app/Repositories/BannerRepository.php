<?php

namespace App\Repositories;

use App\Models\Banner;

class BannerRepository
{
    public function getAll()
    {
        return Banner::orderBy('sort_order')->get();
    }

    public function getActive()
    {
        return Banner::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function create(array $data)
    {
        return Banner::create($data);
    }

    public function update(Banner $banner, array $data)
    {
        $banner->update($data);
        return $banner;
    }

    public function delete(Banner $banner)
    {
        $banner->delete();
        return true;
    }
}
