<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Traits\ApiResponse;

class CampaignController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $campaigns = Campaign::where('is_active', true)
            ->withCount(['plants' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'description' => $campaign->description,
                    'image' => $campaign->image ? asset('storage/' . $campaign->image) : null,
                    'target_plants' => $campaign->target_plants,
                    'current_plants' => $campaign->plants_count,
                    'start_date' => $campaign->start_date ? $campaign->start_date->format('Y-m-d') : null,
                    'end_date' => $campaign->end_date ? $campaign->end_date->format('Y-m-d') : null,
                ];
            });

        return $this->success($campaigns);
    }
}
