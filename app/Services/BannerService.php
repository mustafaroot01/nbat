<?php

namespace App\Services;

use App\Repositories\BannerRepository;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    public function __construct(private BannerRepository $bannerRepository)
    {
    }

    public function getAll()
    {
        return $this->bannerRepository->getAll();
    }

    public function getActive()
    {
        return $this->bannerRepository->getActive();
    }

    public function store(array $data)
    {
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store('banners', 'public');
        }

        return $this->bannerRepository->create($data);
    }

    public function update($banner, array $data)
    {
        if (isset($data['image'])) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $data['image']->store('banners', 'public');
        }

        return $this->bannerRepository->update($banner, $data);
    }

    public function destroy($banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        return $this->bannerRepository->delete($banner);
    }

    public function toggle($banner)
    {
        return $this->bannerRepository->update($banner, ['is_active' => !$banner->is_active]);
    }
}
