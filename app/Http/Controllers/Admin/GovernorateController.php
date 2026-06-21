<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GovernorateResource;
use App\Models\Governorate;
use App\Traits\ApiResponse;

class GovernorateController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $governorates = Governorate::withCount('plants')->get();

        return $this->success(GovernorateResource::collection($governorates));
    }

    public function toggleCoverage(Governorate $governorate)
    {
        $governorate->update([
            'is_covered' => !$governorate->is_covered,
        ]);

        $status = $governorate->is_covered ? 'مغطاة' : 'خارج التغطية';

        return $this->success(
            new GovernorateResource($governorate->loadCount('plants')),
            "تم تحديث حالة {$governorate->name_ar} — {$status}"
        );
    }
}
