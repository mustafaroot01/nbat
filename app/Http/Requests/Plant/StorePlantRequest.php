<?php

namespace App\Http\Requests\Plant;

use Illuminate\Foundation\Http\FormRequest;

class StorePlantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'age' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'governorate_id' => ['required', 'exists:governorates,id'],
            'image' => ['nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
        ];
    }
}
