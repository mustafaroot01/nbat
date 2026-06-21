<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $banners = Banner::orderBy('sort_order')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات',
            'data' => BannerResource::collection($banners),
            'meta' => [
                'current_page' => $banners->currentPage(),
                'last_page' => $banners->lastPage(),
                'per_page' => $banners->perPage(),
                'total' => $banners->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'link' => 'nullable|url',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'image' => $path,
            'link' => $request->link,
        ]);

        return $this->success(new BannerResource($banner), 'تم إضافة البنر', 201);
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image' => 'nullable|image|max:5120',
            'link' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $banner->image = $path;
        }

        if ($request->has('link')) {
            $banner->link = $request->link;
        }

        $banner->save();

        return $this->success(new BannerResource($banner), 'تم تعديل البنر');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return $this->success(null, 'تم حذف البنر');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);

        return $this->success(null, $banner->is_active ? 'تم تفعيل البنر' : 'تم إيقاف البنر');
    }
}
