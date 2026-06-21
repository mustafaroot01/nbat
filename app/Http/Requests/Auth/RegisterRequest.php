<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'governorate_id' => ['required', 'exists:governorates,id'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.unique' => 'رقم الهاتف مسجل مسبقاً',
            'password.required' => 'كلمة السر مطلوبة',
            'password.min' => 'كلمة السر يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمة السر غير متطابقة',
            'governorate_id.required' => 'المحافظة مطلوبة',
            'governorate_id.exists' => 'المحافظة غير موجودة',
        ];
    }
}
