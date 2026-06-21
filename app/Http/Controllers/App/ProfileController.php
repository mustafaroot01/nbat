<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        $user = $request->user()->load('governorate');

        return $this->success(new UserResource($user));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'governorate_id' => 'sometimes|exists:governorates,id',
        ]);

        $request->user()->update($request->only(['name', 'governorate_id']));

        return $this->success(null, 'تم تحديث البيانات');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('photo')->store('profiles', 'public');
        $user->update(['profile_photo' => $path]);

        return $this->success(['photo' => asset('storage/' . $path)], 'تم تحديث الصورة');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('كلمة السر الحالية غير صحيحة', 422);
        }

        $user->update(['password' => $request->password]);

        return $this->success(null, 'تم تغيير كلمة السر');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return $this->success(null, 'تم حذف الحساب');
    }
}
