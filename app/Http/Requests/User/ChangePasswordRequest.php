<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'كلمة السر الحالية مطلوبة',
            'current_password.current_password' => 'كلمة السر الحالية غير صحيحة',
            'password.required' => 'كلمة السر الجديدة مطلوبة',
            'password.min' => 'كلمة السر يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمتا السر غير متطابقتين',
        ];
    }
}
