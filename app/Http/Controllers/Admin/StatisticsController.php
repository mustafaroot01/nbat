<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use App\Models\User;
use App\Models\PlantReport;
use App\Models\Governorate;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $admin = request()->user();
        $govId = $admin ? $admin->governorate_id : null;

        $mostActiveQuery = DB::table('plants')
            ->join('governorates', 'plants.governorate_id', '=', 'governorates.id')
            ->select('governorates.id', 'governorates.name_ar', DB::raw('COUNT(*) as plants_count'));
            
        if ($govId) {
            $mostActiveQuery->where('plants.governorate_id', $govId);
        }

        $mostActive = $mostActiveQuery
            ->groupBy('governorates.id', 'governorates.name_ar')
            ->orderByDesc('plants_count')
            ->first();

        return $this->success([
            'total_plants' => Plant::when($govId, fn($q) => $q->where('governorate_id', $govId))->count(),
            'plants_this_month' => Plant::when($govId, fn($q) => $q->where('governorate_id', $govId))->whereMonth('created_at', now()->month)->count(),
            'most_active_governorate' => $mostActive,
            'pending_requests' => Plant::when($govId, fn($q) => $q->where('governorate_id', $govId))->where('status', 'pending')->count(),
            'total_users' => User::when($govId, fn($q) => $q->where('governorate_id', $govId))->count(),
            'users_this_month' => User::when($govId, fn($q) => $q->where('governorate_id', $govId))->whereMonth('created_at', now()->month)->count(),
            'total_reports' => PlantReport::when($govId, fn($q) => $q->whereHas('plant', fn($p) => $p->where('governorate_id', $govId)))->count(),
            'pending_reports' => PlantReport::when($govId, fn($q) => $q->whereHas('plant', fn($p) => $p->where('governorate_id', $govId)))->where('status', 'pending')->count(),
            'resolved_reports' => PlantReport::when($govId, fn($q) => $q->whereHas('plant', fn($p) => $p->where('governorate_id', $govId)))->where('status', 'resolved')->count(),
        ]);
    }

    public function governoratesStats()
    {
        $admin = request()->user();
        $govId = $admin ? $admin->governorate_id : null;

        $governorates = Governorate::withCount('plants')
            ->when($govId, fn($q) => $q->where('id', $govId))
            ->orderByDesc('plants_count')
            ->get();

        return $this->success($governorates);
    }
}
