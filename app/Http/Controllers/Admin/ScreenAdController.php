<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScreenAdResource;
use App\Models\ScreenAd;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScreenAdController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $ads = ScreenAd::orderBy('sort_order')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الإعلانات',
            'data' => ScreenAdResource::collection($ads),
            'meta' => [
                'current_page' => $ads->currentPage(),
                'last_page' => $ads->lastPage(),
                'per_page' => $ads->perPage(),
                'total' => $ads->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store('screen_ads', 'public');

        $ad = ScreenAd::create([
            'image' => $path,
        ]);

        return $this->success(new ScreenAdResource($ad), 'تم إضافة الإعلان', 201);
    }

    public function update(Request $request, ScreenAd $screenAd)
    {
        $request->validate([
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            if ($screenAd->image) {
                Storage::disk('public')->delete($screenAd->image);
            }
            $path = $request->file('image')->store('screen_ads', 'public');
            $screenAd->image = $path;
            $screenAd->save();
        }

        return $this->success(new ScreenAdResource($screenAd), 'تم تعديل الإعلان');
    }

    public function destroy(ScreenAd $screenAd)
    {
        if ($screenAd->image) {
            Storage::disk('public')->delete($screenAd->image);
        }

        $screenAd->delete();

        return $this->success(null, 'تم حذف الإعلان');
    }

    public function toggle(ScreenAd $screenAd)
    {
        $screenAd->update(['is_active' => !$screenAd->is_active]);

        return $this->success(null, $screenAd->is_active ? 'تم تفعيل الإعلان' : 'تم إيقاف الإعلان');
    }
}
