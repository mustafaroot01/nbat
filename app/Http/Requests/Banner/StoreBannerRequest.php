<?php

namespace App\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'link' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'الصورة مطلوبة',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 2MB',
            'link.url' => 'الرابط غير صحيح',
        ];
    }
}
