<?php

namespace App\Http\Requests\Plant;

use Illuminate\Foundation\Http\FormRequest;

class RejectPlantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'سبب الرفض مطلوب',
        ];
    }
}
