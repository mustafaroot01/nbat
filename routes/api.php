<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AppVersionController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\GovernorateController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PlantController as AdminPlantController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\App\PlantController;
use App\Http\Controllers\App\ProfileController;
use App\Models\Banner;
use App\Models\Governorate;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════
// 🔓 Public Routes (No Auth)
// ═══════════════════════════════════════════════

// Auth — App (Users)
Route::prefix('auth')->middleware(['maintenance', 'throttle:6,1'])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('forgot-password', [\App\Http\Controllers\Auth\OtpController::class, 'send']);
    Route::post('verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verify']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Auth — Admin
Route::middleware('throttle:6,1')->post('admin/auth/login', [AdminAuthController::class, 'login']);

// Public — Banners
Route::get('banners', function () {
    return Banner::where('is_active', true)->orderBy('sort_order')->get();
});

// Public — Governorates
Route::get('governorates', function () {
    return \App\Http\Resources\GovernorateResource::collection(Governorate::all());
});

// Public — Screen Ads (Splash)
Route::get('screen-ads', [\App\Http\Controllers\App\ScreenAdController::class, 'index']);

// Public — Leaderboard
Route::get('leaderboard', [\App\Http\Controllers\App\LeaderboardController::class, 'index']);

// Public — Campaigns
Route::get('campaigns', [\App\Http\Controllers\App\CampaignController::class, 'index']);

// Public — App Settings
Route::get('app-settings', function () {
    return response()->json([
        'success' => true,
        'data' => [
            'maintenance_mode' => Setting::getValue('maintenance_mode', 'false') === 'true',
            'plant_approval_required' => Setting::getValue('plant_approval_required', 'true') === 'true',
            'map_provider' => Setting::getValue('map_provider', 'osm'),
        ],
    ]);
});

// Public — App Version Check
Route::get('app-version/check', function (Request $request) {
    $request->validate([
        'platform' => 'required|in:android,ios',
        'version_code' => 'required|integer',
    ]);

    $latest = \App\Models\AppVersion::where('platform', $request->platform)
        ->where('is_active', true)
        ->latest()
        ->first();

    if (!$latest || $latest->version_code <= $request->version_code) {
        return response()->json(['success' => true, 'data' => ['needs_update' => false]]);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'needs_update' => true,
            'update_type' => $latest->update_type,
            'version_number' => $latest->version_number,
            'store_url' => $latest->store_url,
            'release_notes' => $latest->release_notes,
        ],
    ]);
});

// Public — Blog (Notifications)
Route::get('blog', [\App\Http\Controllers\App\BlogController::class, 'index']);

// ═══════════════════════════════════════════════
// 🔒 Authenticated — App (User)
// ═══════════════════════════════════════════════

Route::middleware(['auth:sanctum', 'maintenance'])->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::post('profile/photo', [ProfileController::class, 'updatePhoto']);
    Route::put('profile/password', [ProfileController::class, 'changePassword']);
    Route::delete('profile', [ProfileController::class, 'destroy']);

    // Plants — App
    Route::post('plants', [\App\Http\Controllers\App\PlantController::class, 'store']);
    Route::get('plants/my', [\App\Http\Controllers\App\PlantController::class, 'myPlants']);
    Route::get('plants/geojson', [\App\Http\Controllers\App\PlantController::class, 'geojson']);
    Route::get('plants/nearby', [\App\Http\Controllers\App\PlantController::class, 'nearby']);
    Route::get('plants/{plant}', [\App\Http\Controllers\App\PlantController::class, 'show']);
    Route::delete('plants/{plant}', [\App\Http\Controllers\App\PlantController::class, 'destroy']);
    Route::post('plants/{plant}/report', [\App\Http\Controllers\App\PlantController::class, 'report']);

    // Device Token
    Route::post('device-token', function (Request $request) {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:android,ios',
        ]);

        $request->user()->deviceTokens()->updateOrCreate(
            ['token' => $request->token],
            ['platform' => $request->platform]
        );

        return response()->json(['success' => true, 'message' => 'تم حفظ التوكن']);
    });
});

// ═══════════════════════════════════════════════
// 🔒 Authenticated — Admin Dashboard
// ═══════════════════════════════════════════════

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    Route::post('auth/logout', [AdminAuthController::class, 'logout']);

    // Statistics (Read access to any major module is usually enough, but let's just make it available to admins)
    Route::get('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index']);
    Route::get('statistics/governorates', [\App\Http\Controllers\Admin\StatisticsController::class, 'governoratesStats']);

    // Users
    Route::middleware('permission:read_users')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
    });
    Route::middleware('permission:write_users')->group(function () {
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle']);
        Route::patch('users/{user}/toggle-trusted', [UserController::class, 'toggleTrusted']);
    });

    // Plants
    Route::middleware('permission:read_plants')->group(function () {
        Route::get('plants', [AdminPlantController::class, 'index']);
        Route::get('plants/{plant}', [AdminPlantController::class, 'show']);
    });
    Route::middleware('permission:write_plants')->group(function () {
        Route::patch('plants/{plant}/approve', [AdminPlantController::class, 'approve']);
        Route::patch('plants/{plant}/reject', [AdminPlantController::class, 'reject']);
        Route::patch('plants/{plant}/pending', [AdminPlantController::class, 'pending']);
    });
    Route::middleware('permission:create_plants')->group(function () {
        Route::delete('plants/{plant}', [AdminPlantController::class, 'destroy']);
    });

    // Reports
    Route::middleware('permission:read_reports')->get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index']);
    Route::middleware('permission:write_reports')->group(function () {
        Route::patch('reports/{report}/resolve', [\App\Http\Controllers\Admin\ReportController::class, 'resolve']);
    });

    // Admin Notifications
    Route::get('admin-notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index']);
    Route::post('admin-notifications/mark-read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAsRead']);
    Route::delete('admin-notifications/{id}', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'destroy']);

    // Banners
    Route::middleware('permission:read_banners')->get('banners', [AdminBannerController::class, 'index']);
    Route::middleware('permission:write_banners')->group(function () {
        Route::put('banners/{banner}', [AdminBannerController::class, 'update']);
        Route::patch('banners/{banner}/toggle', [AdminBannerController::class, 'toggle']);
    });
    Route::middleware('permission:create_banners')->group(function () {
        Route::post('banners', [AdminBannerController::class, 'store']);
        Route::delete('banners/{banner}', [AdminBannerController::class, 'destroy']);
    });

    // Screen Ads (Splash)
    Route::middleware('permission:read_screen_ads')->get('screen-ads', [\App\Http\Controllers\Admin\ScreenAdController::class, 'index']);
    Route::middleware('permission:write_screen_ads')->group(function () {
        Route::put('screen-ads/{screen_ad}', [\App\Http\Controllers\Admin\ScreenAdController::class, 'update']);
        Route::patch('screen-ads/{screen_ad}/toggle-status', [\App\Http\Controllers\Admin\ScreenAdController::class, 'toggle']);
    });
    Route::middleware('permission:create_screen_ads')->group(function () {
        Route::post('screen-ads', [\App\Http\Controllers\Admin\ScreenAdController::class, 'store']);
        Route::delete('screen-ads/{screen_ad}', [\App\Http\Controllers\Admin\ScreenAdController::class, 'destroy']);
    });
    // Governorates
    Route::middleware('permission:read_governorates')->get('governorates', [GovernorateController::class, 'index']);
    Route::middleware('permission:write_governorates')->patch('governorates/{governorate}/toggle-coverage', [GovernorateController::class, 'toggleCoverage']);

    // Notifications
    Route::middleware('permission:read_notifications')->get('notifications', [NotificationController::class, 'index']);
    Route::middleware('permission:create_notifications')->post('notifications/send', [NotificationController::class, 'send']);
    Route::middleware('permission:write_notifications')->post('notifications/{notification}', [NotificationController::class, 'update']);
    Route::middleware('permission:create_notifications')->delete('notifications/{notification}', [NotificationController::class, 'destroy']);

    // App Versions
    Route::middleware('permission:read_app_versions')->get('app-versions', [AppVersionController::class, 'index']);
    Route::middleware('permission:write_app_versions')->put('app-versions/{appVersion}', [AppVersionController::class, 'update']);
    Route::middleware('permission:create_app_versions')->group(function () {
        Route::post('app-versions', [AppVersionController::class, 'store']);
        Route::delete('app-versions/{appVersion}', [AppVersionController::class, 'destroy']);
    });

    // User Levels (Leaderboard Badges)
    Route::middleware('permission:read_user_levels')->get('user-levels', [\App\Http\Controllers\Admin\UserLevelController::class, 'index']);
    Route::middleware('permission:write_user_levels')->group(function () {
        Route::post('user-levels', [\App\Http\Controllers\Admin\UserLevelController::class, 'store']);
        Route::put('user-levels/{userLevel}', [\App\Http\Controllers\Admin\UserLevelController::class, 'update']);
        Route::delete('user-levels/{userLevel}', [\App\Http\Controllers\Admin\UserLevelController::class, 'destroy']);
    });

    // Campaigns
    Route::middleware('permission:read_campaigns')->get('campaigns', [\App\Http\Controllers\Admin\CampaignController::class, 'index']);
    Route::middleware('permission:write_campaigns')->group(function () {
        Route::post('campaigns', [\App\Http\Controllers\Admin\CampaignController::class, 'store']);
        Route::put('campaigns/{campaign}', [\App\Http\Controllers\Admin\CampaignController::class, 'update']); // PUT for FormData with _method=PUT
        Route::delete('campaigns/{campaign}', [\App\Http\Controllers\Admin\CampaignController::class, 'destroy']);
    });

    // Roles and Permissions
    Route::middleware('permission:read_roles')->get('roles', [RoleController::class, 'index']);
    Route::middleware('permission:read_settings')->get('settings', [SettingController::class, 'index']);
    Route::middleware('permission:write_settings')->group(function () {
        Route::put('settings', [SettingController::class, 'update']);
        Route::post('settings', [SettingController::class, 'update']); 
        Route::put('settings/coverage', [SettingController::class, 'updateCoverage']);
    });

    // Roles & Permissions & Admins
    Route::middleware('permission:read_roles')->group(function () {
        Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index']);
        Route::get('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'show']);
        Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index']);
        Route::get('admins', [\App\Http\Controllers\Admin\AdminManagementController::class, 'index']);
    });
    Route::middleware('permission:write_roles')->group(function () {
        Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update']);
        Route::put('admins/{admin}', [\App\Http\Controllers\Admin\AdminManagementController::class, 'update']);
        Route::put('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'update']);
    });
    Route::middleware('permission:create_roles')->group(function () {
        Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store']);
        Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy']);
        Route::post('admins', [\App\Http\Controllers\Admin\AdminManagementController::class, 'store']);
        Route::delete('admins/{admin}', [\App\Http\Controllers\Admin\AdminManagementController::class, 'destroy']);
        Route::post('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store']);
        Route::delete('permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy']);
    });
});
