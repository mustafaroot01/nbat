<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $result = $this->authService->loginAdmin(
            $request->email,
            $request->password
        );

        if (!$result) {
            return $this->error('بيانات الدخول غير صحيحة', 401);
        }

        return $this->success($result, 'تم تسجيل الدخول بنجاح');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'تم تسجيل الخروج');
    }
}
