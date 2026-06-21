<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $admins = Admin::with(['roles', 'governorate'])->get();
        return $this->success($admins);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'governorate_id' => 'nullable|exists:governorates,id',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => true,
            'governorate_id' => $request->governorate_id,
        ]);

        $admin->assignRole($request->role);

        return $this->success($admin->load(['roles', 'governorate']), 'تم إنشاء المشرف بنجاح', 201);
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|exists:roles,name',
            'governorate_id' => 'nullable|exists:governorates,id',
        ]);

        if ($request->has('name')) $admin->name = $request->name;
        if ($request->has('email')) $admin->email = $request->email;
        if ($request->filled('password')) $admin->password = $request->password;
        if ($request->has('governorate_id')) $admin->governorate_id = $request->governorate_id;
        
        $admin->save();

        if ($request->has('role')) {
            $admin->syncRoles([$request->role]);
        }

        return $this->success($admin->load(['roles', 'governorate']), 'تم تحديث المشرف بنجاح');
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === request()->user()->id) {
            return $this->error('لا يمكنك حذف حسابك الخاص', 403);
        }

        if ($admin->hasRole('superadmin') && Admin::role('superadmin')->count() === 1) {
            return $this->error('لا يمكن حذف المشرف العام الوحيد', 403);
        }

        $admin->delete();
        return $this->success(null, 'تم حذف المشرف بنجاح');
    }
}
