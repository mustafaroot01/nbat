<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plant\StorePlantRequest;
use App\Http\Resources\PlantResource;
use App\Models\Plant;
use App\Repositories\PlantRepository;
use App\Services\FirebaseNotificationService;
use App\Services\PlantService;
use App\Traits\ApiResponse;
use App\Models\PlantReport;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PlantService $plantService,
        private PlantRepository $plantRepository
    ) {}

    public function store(StorePlantRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('plants', 'public');
        }

        $plant = $this->plantService->store($data, auth()->id());

        $admins = \App\Models\Admin::whereNull('governorate_id')
            ->orWhere('governorate_id', $plant->governorate_id)
            ->get();
            
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewPlantNotification($plant));

        app(FirebaseNotificationService::class)->sendToAdmin(
            'نبتة جديدة تنتظر المراجعة 🌿',
            auth()->user()->name . " أضاف {$plant->name}"
        );

        return $this->success(new PlantResource($plant->load(['user', 'governorate'])), 'تم إضافة النبتة', 201);
    }

    public function myPlants()
    {
        $plants = Plant::with(['governorate', 'statusLogs.admin'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return $this->paginated($plants, \App\Http\Resources\PlantResource::class);
    }

    public function show(Plant $plant)
    {
        $plant->load(['user', 'governorate', 'statusLogs.admin']);

        return $this->success(new PlantResource($plant));
    }

    public function destroy(Plant $plant)
    {
        if ($plant->user_id !== auth()->id()) {
            return $this->error('غير مصرح', 403);
        }

        $plant->delete();

        return $this->success(null, 'تم حذف النبتة');
    }

    public function geojson(Request $request)
    {
        $governorateId = $request->query('governorate_id');
        $data = $this->plantRepository->getAsGeoJson($governorateId);

        return response()->json($data);
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|integer|min:100|max:50000',
        ]);

        $plants = $this->plantRepository->getNearby(
            $request->lat,
            $request->lng,
            $request->radius ?? 5000
        );

        return $this->success(PlantResource::collection($plants));
    }

    public function report(Request $request, Plant $plant)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        // Check if already reported by this user
        $exists = PlantReport::where('plant_id', $plant->id)
            ->where('reporter_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return $this->error('لقد قمت بالإبلاغ عن هذه النبتة مسبقاً وبلاغك قيد المراجعة', 422);
        }

        $report = PlantReport::create([
            'plant_id' => $plant->id,
            'reporter_id' => auth()->id(),
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $admins = \App\Models\Admin::whereNull('governorate_id')
            ->orWhere('governorate_id', $plant->governorate_id)
            ->get();
            
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewReportNotification($report));

        return $this->success(null, 'تم استلام بلاغك بنجاح، شكراً لتعاونك', 201);
    }
}
