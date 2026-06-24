<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Plant\ApprovePlantAction;
use App\Actions\Plant\RejectPlantAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plant\RejectPlantRequest;
use App\Http\Resources\PlantResource;
use App\Models\Plant;
use App\Services\PlantService;
use App\Traits\ApiResponse;

class PlantController extends Controller
{
    use ApiResponse;

    public function __construct(private PlantService $plantService) {}

    public function index()
    {
        $filters = request()->only(['q', 'status', 'governorate_id']);
        
        $admin = request()->user();
        if ($admin && $admin->governorate_id) {
            $filters['governorate_id'] = $admin->governorate_id;
        }
        
        $itemsPerPage = request()->get('itemsPerPage', 15);
        if ($itemsPerPage == -1) {
            $itemsPerPage = Plant::count();
            if ($itemsPerPage == 0) $itemsPerPage = 15;
        }

        $plants = $this->plantService->getAllPlants($filters, $itemsPerPage);
        $plants->load(['user', 'governorate', 'statusLogs.admin']);

        return response()->json([
            'success' => true,
            'message' => 'تم جلب البيانات',
            'data' => PlantResource::collection($plants),
            'meta' => [
                'current_page' => $plants->currentPage(),
                'last_page' => $plants->lastPage(),
                'per_page' => $plants->perPage(),
                'total' => $plants->total(),
            ],
        ]);
    }

    public function show(Plant $plant)
    {
        $plant->load(['user', 'governorate', 'statusLogs.admin']);

        return $this->success(new PlantResource($plant));
    }

    public function approve(Plant $plant)
    {
        app(ApprovePlantAction::class)->execute($plant);
        $plant->refresh()->load(['user', 'governorate', 'statusLogs.admin']);

        return $this->success(new PlantResource($plant), 'تمت الموافقة على النبتة');
    }

    public function pending(Plant $plant)
    {
        app(\App\Repositories\PlantRepository::class)->pending($plant);
        $plant->refresh()->load(['user', 'governorate', 'statusLogs.admin']);

        return $this->success(new PlantResource($plant), 'تم تعليق النبتة');
    }

    public function reject(RejectPlantRequest $request, Plant $plant)
    {
        app(RejectPlantAction::class)->execute($plant, $request->rejection_reason);
        $plant->refresh()->load(['user', 'governorate', 'statusLogs.admin']);

        return $this->success(new PlantResource($plant), 'تم رفض النبتة');
    }

    public function destroy(Plant $plant)
    {
        if ($plant->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($plant->image);
        }

        $plant->delete();

        return $this->success(null, 'تم حذف النبتة');
    }
}
