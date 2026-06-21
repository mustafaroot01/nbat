<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $versions = AppVersion::latest()->paginate(15);

        return $this->paginated($versions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:android,ios',
            'version_number' => 'required|string',
            'version_code' => 'required|integer',
            'update_type' => 'required|in:optional,forced',
            'store_url' => 'required|url',
            'release_notes' => 'nullable|string',
        ]);

        $version = AppVersion::create($request->all());

        return $this->success($version, 'تم إضافة الإصدار', 201);
    }

    public function update(Request $request, AppVersion $appVersion)
    {
        $request->validate([
            'platform' => 'in:android,ios',
            'version_number' => 'string',
            'version_code' => 'integer',
            'update_type' => 'in:optional,forced',
            'store_url' => 'url',
            'release_notes' => 'nullable|string',
        ]);

        $appVersion->update($request->all());

        return $this->success($appVersion, 'تم تعديل الإصدار');
    }

    public function destroy(AppVersion $appVersion)
    {
        $appVersion->delete();

        return $this->success(null, 'تم حذف الإصدار');
    }
}
