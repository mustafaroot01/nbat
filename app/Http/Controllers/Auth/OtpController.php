<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    use ApiResponse;

    public function __construct(private OtpService $otpService) {}

    /**
     * POST /api/auth/forgot-password (send OTP)
     */
    public function send(Request $request): JsonResponse
    {
        if ($this->otpService->isDisabled()) {
            return $this->success(['otp_disabled' => true], 'التحقق معطل حالياً، يمكنك المتابعة');
        }

        $request->validate([
            'phone' => ['required', 'string', 'regex:/^((\+?964)|0)?7[0-9]{9}$/'],
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.regex'    => 'رقم الهاتف العراقي غير صحيح',
        ]);

        $result = $this->otpService->send($request->phone, $request->ip());

        if (!$result['success']) {
            return $this->error($result['message'], $result['code'], $result['data'] ?? null);
        }

        return $this->success($result['data'], $result['message']);
    }

    /**
     * POST /api/auth/verify-otp
     */
    public function verify(Request $request): JsonResponse
    {
        if ($this->otpService->isDisabled()) {
            return $this->success(['verified' => true], 'تم التحقق بنجاح');
        }

        $request->validate([
            'phone' => ['required', 'string'],
            'code'  => ['required', 'string', 'size:6'],
        ], [
            'phone.required' => 'رقم الهاتف مطلوب',
            'code.required'  => 'رمز التحقق مطلوب',
            'code.size'      => 'رمز التحقق يجب أن يكون 6 أرقام',
        ]);

        $result = $this->otpService->verify($request->phone, $request->code);

        if (!$result['success']) {
            return $this->error($result['message'], $result['code'], $result['data'] ?? null);
        }

        return $this->success($result['data'], $result['message']);
    }
}
