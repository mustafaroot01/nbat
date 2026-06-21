<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScreenAdResource;
use App\Models\ScreenAd;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ScreenAdController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $ads = ScreenAd::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب إعلانات الشاشة',
            'data' => ScreenAdResource::collection($ads),
        ]);
    }
}
