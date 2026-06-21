<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Models\Setting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return $this->success($settings);
    }

    public function update(Request $request)
    {
        // Keys to skip — these are HTTP method spoofing fields, not real settings
        $skipKeys = ['_method', '_token'];

        foreach ($request->all() as $key => $value) {
            if (in_array($key, $skipKeys)) {
                continue;
            }

            if ($request->hasFile($key)) {
                // Store credentials or files securely in local storage, not public
                $path = $request->file($key)->store('settings', 'local');
                Setting::setValue($key, $path);
            } else {
                Setting::setValue($key, $value);
            }
        }

        return $this->success(null, 'تم تحديث الإعدادات');
    }

    public function updateCoverage(Request $request)
    {
        $request->validate([
            'coverage_mode' => 'required|in:all,custom',
            'governorate_ids' => 'nullable|array',
            'governorate_ids.*' => 'exists:governorates,id',
        ]);

        Setting::setValue('coverage_mode', $request->coverage_mode);

        if ($request->coverage_mode === 'custom' && $request->has('governorate_ids')) {
            // Set all to not covered first
            Governorate::query()->update(['is_covered' => false]);

            // Then enable selected ones
            Governorate::whereIn('id', $request->governorate_ids)
                ->update(['is_covered' => true]);
        } elseif ($request->coverage_mode === 'all') {
            // Enable all governorates
            Governorate::query()->update(['is_covered' => true]);
        }

        return $this->success(null, 'تم تحديث إعدادات التغطية');
    }
}
