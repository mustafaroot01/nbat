<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request)
    {
        $result = $this->authService->loginUser(
            $request->phone,
            $request->password
        );

        if (!$result) {
            return $this->error('بيانات الدخول غير صحيحة', 401);
        }

        return $this->success([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ], 'تم تسجيل الدخول بنجاح');
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->registerUser($request->validated());

        return $this->success([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ], 'تم إنشاء الحساب بنجاح', 201);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify OTP is valid
        $otp = \App\Models\OtpVerification::where('phone', $request->phone)
            ->where('verified', true)
            ->latest()
            ->first();

        if (!$otp || $otp->created_at->diffInMinutes(now()) > 10) {
            return $this->error('رمز التحقق غير صالح أو منتهي', 422);
        }

        $user = \App\Models\User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->error('المستخدم غير موجود', 404);
        }

        $user->update(['password' => $request->password]);

        return $this->success(null, 'تم تغيير كلمة السر بنجاح');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('user-token', ['*'], now()->addDays(30))->plainTextToken;

        return $this->success([
            'token' => $token,
        ], 'تم تجديد التوكن بنجاح');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'تم تسجيل الخروج');
    }
}
