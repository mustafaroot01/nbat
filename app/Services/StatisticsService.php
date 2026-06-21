<?php

namespace App\Services;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    public function getDashboardStats()
    {
        return [
            'total_plants' => Plant::count(),
            'plants_this_month' => Plant::whereMonth('created_at', now()->month)->count(),
            'most_active_governorate' => $this->getMostActiveGovernorate(),
            'pending_requests' => Plant::where('status', 'pending')->count(),
            'total_users' => User::count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];
    }

    private function getMostActiveGovernorate()
    {
        $gov = DB::table('plants')
            ->join('governorates', 'plants.governorate_id', '=', 'governorates.id')
            ->select('governorates.id', 'governorates.name_ar', DB::raw('count(*) as plants_count'))
            ->groupBy('governorates.id', 'governorates.name_ar')
            ->orderByDesc('plants_count')
            ->first();

        return $gov ? (array)$gov : null;
    }
}
