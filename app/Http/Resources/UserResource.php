<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_photo' => $this->profile_photo
                ? asset('storage/' . $this->profile_photo)
                : null,
            'governorate' => new GovernorateResource($this->whenLoaded('governorate')),
            'is_active' => $this->is_active,
            'is_trusted' => (bool) $this->is_trusted,
            'plants_count' => $this->whenCounted('plants'),
            'approved_plants_count' => $this->whenCounted('approved_plants_count'),
            'rejected_plants_count' => $this->whenCounted('rejected_plants_count'),
            'plants' => PlantResource::collection($this->whenLoaded('plants')),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}
