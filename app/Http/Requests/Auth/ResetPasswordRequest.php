<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'exists:users,phone'],
            'code' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.exists' => 'رقم الهاتف غير مسجل لدينا',
            'code.required' => 'رمز التحقق مطلوب',
            'password.required' => 'كلمة السر مطلوبة',
            'password.min' => 'كلمة السر يجب أن تكون 8 أحرف على الأقل',
            'password.confirmed' => 'كلمتا السر غير متطابقتين',
        ];
    }
}
