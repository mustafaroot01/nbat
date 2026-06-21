<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScreenAdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => asset('storage/' . $this->image),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
