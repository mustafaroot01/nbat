<?php

namespace App\Repositories;

use App\Models\Plant;
use Illuminate\Support\Collection;

class PlantRepository
{
    public function getPending()
    {
        return Plant::with(['user', 'governorate'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Plant::with(['user', 'governorate'])
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['governorate_id']), fn ($q) => $q->where('governorate_id', $filters['governorate_id']))
            ->when(isset($filters['q']), function ($q) use ($filters) {
                $search = $filters['q'];
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                              ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getApproved(?int $governorateId = null)
    {
        return Plant::with(['user', 'governorate'])
            ->where('status', 'approved')
            ->when($governorateId, fn ($q) => $q->where('governorate_id', $governorateId))
            ->get();
    }

    public function approve(Plant $plant): void
    {
        $oldStatus = $plant->status;
        $plant->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $plant->statusLogs()->create([
            'admin_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => 'approved',
            'reason' => null,
        ]);
    }

    public function reject(Plant $plant, string $reason): void
    {
        $oldStatus = $plant->status;
        $plant->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $plant->statusLogs()->create([
            'admin_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => 'rejected',
            'reason' => $reason,
        ]);
    }

    public function pending(Plant $plant): void
    {
        $oldStatus = $plant->status;
        $plant->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $plant->statusLogs()->create([
            'admin_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => 'pending',
            'reason' => null,
        ]);
    }

    public function create(array $data): Plant
    {
        return Plant::create($data);
    }

    public function getNearby(float $lat, float $lng, int $radius = 5000): Collection
    {
        return Plant::selectRaw("
                *,
                ST_Distance_Sphere(
                    location,
                    ST_GeomFromText('POINT({$lng} {$lat})')
                ) AS distance_meters
            ")
            ->where('status', 'approved')
            ->whereRaw("
                ST_Distance_Sphere(
                    location,
                    ST_GeomFromText('POINT({$lng} {$lat})')
                ) <= ?
            ", [$radius])
            ->orderBy('distance_meters')
            ->with(['user', 'governorate'])
            ->get();
    }

    public function getAsGeoJson(?int $governorateId = null): array
    {
        $plants = Plant::where('status', 'approved')
            ->when($governorateId, fn ($q) => $q->where('governorate_id', $governorateId))
            ->with(['user', 'governorate', 'campaign'])
            ->get();

        return [
            'type' => 'FeatureCollection',
            'features' => $plants->map(fn ($plant) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $plant->longitude, (float) $plant->latitude],
                ],
                'properties' => [
                    'id' => $plant->id,
                    'name' => $plant->name,
                    'type' => $plant->type,
                    'age' => $plant->age,
                    'user_name' => $plant->user->name,
                    'governorate' => $plant->governorate->name_ar,
                    'campaign_name' => $plant->campaign?->title,
                ],
            ])->toArray(),
        ];
    }
}
