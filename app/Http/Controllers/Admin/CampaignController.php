<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $campaigns = Campaign::withCount(['plants' => function ($query) {
            $query->where('status', 'approved');
        }])->latest()->paginate(15);

        return $this->paginated($campaigns);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'target_plants' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('campaigns', 'public');
        }

        $campaign = Campaign::create($data);

        return $this->success($campaign, 'تم إنشاء الحملة بنجاح', 201);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'target_plants' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            if ($campaign->image) {
                Storage::disk('public')->delete($campaign->image);
            }
            $data['image'] = $request->file('image')->store('campaigns', 'public');
        }

        $campaign->update($data);

        return $this->success($campaign, 'تم تحديث الحملة بنجاح');
    }

    public function destroy(Campaign $campaign)
    {
        if ($campaign->image) {
            Storage::disk('public')->delete($campaign->image);
        }
        
        $campaign->delete();

        return $this->success(null, 'تم حذف الحملة بنجاح');
    }
}
