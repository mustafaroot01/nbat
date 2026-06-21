<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLevel;
use App\Traits\ApiResponse;

class LeaderboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $isEnabled = Setting::getValue('leaderboard_enabled', 'true') === 'true';

        if (!$isEnabled) {
            return $this->error('لوحة المتصدرين معطلة حالياً', 403);
        }

        $pointsPerPlant = (int) Setting::getValue('points_per_plant', 10);
        $levels = UserLevel::orderBy('min_plants', 'desc')->get(); // Highest first

        // Get users with approved plants count
        $users = User::whereHas('plants', function ($query) {
            $query->where('status', 'approved');
        })
        ->withCount(['plants' => function ($query) {
            $query->where('status', 'approved');
        }])
        ->orderBy('plants_count', 'desc')
        ->orderBy('id', 'asc') // tie breaker
        ->limit(28) // Top 3 + next 25
        ->get();

        $rankedUsers = $users->map(function ($user, $index) use ($pointsPerPlant, $levels) {
            // Determine Level
            $userLevelName = 'مبتدئ'; // Default
            foreach ($levels as $level) {
                if ($user->plants_count >= $level->min_plants) {
                    $userLevelName = $level->name;
                    break;
                }
            }

            // Extract first name
            $firstName = explode(' ', trim($user->name))[0] ?? 'مستخدم';

            return [
                'rank' => $index + 1,
                'first_name' => $firstName,
                'profile_photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
                'plants_count' => $user->plants_count,
                'points' => $user->plants_count * $pointsPerPlant,
                'level' => $userLevelName,
            ];
        });

        $top3 = $rankedUsers->take(3)->values();
        $others = $rankedUsers->slice(3)->values();

        return $this->success([
            'is_enabled' => true,
            'top_3' => $top3,
            'others' => $others,
        ]);
    }
}
