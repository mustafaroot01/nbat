<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'age' => $this->age,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'notes' => $this->notes,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i'),
            'campaign_name' => $this->whenLoaded('campaign', fn () => $this->campaign?->title),
            'user' => new UserResource($this->whenLoaded('user')),
            'governorate' => new GovernorateResource($this->whenLoaded('governorate')),
            'status_logs' => $this->whenLoaded('statusLogs', function () {
                return $this->statusLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'old_status' => $log->old_status,
                        'new_status' => $log->new_status,
                        'reason' => $log->reason,
                        'created_at' => $log->created_at->format('Y-m-d H:i'),
                        'admin' => $log->admin ? [
                            'name' => $log->admin->name,
                            'profile_photo' => $log->admin->profile_photo ? asset('storage/' . $log->admin->profile_photo) : null,
                        ] : null,
                    ];
                });
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
