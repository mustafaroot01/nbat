<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlantReport;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = PlantReport::with(['plant.user', 'plant.governorate', 'plant.statusLogs.admin', 'reporter'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $admin = $request->user();
        if ($admin && $admin->governorate_id) {
            $query->whereHas('plant', function ($q) use ($admin) {
                $q->where('governorate_id', $admin->governorate_id);
            });
        } elseif ($request->filled('governorate_id')) {
            $query->whereHas('plant', function ($q) use ($request) {
                $q->where('governorate_id', $request->governorate_id);
            });
        }

        $itemsPerPage = $request->get('itemsPerPage', 15);
        
        if ($itemsPerPage == -1) {
            $itemsPerPage = $query->count();
            $itemsPerPage = $itemsPerPage > 0 ? $itemsPerPage : 15;
        }

        $reports = $query->paginate($itemsPerPage);

        return $this->paginated(
            $reports,
            \App\Http\Resources\ReportResource::class
        );
    }

    public function resolve(PlantReport $report)
    {
        $report->update([
            'status' => 'resolved'
        ]);

        return $this->success(null, 'تم إغلاق البلاغ بنجاح');
    }
}
