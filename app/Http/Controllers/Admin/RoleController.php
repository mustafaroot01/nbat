<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return $this->success($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->success($role->load('permissions'), 'تم إنشاء الدور بنجاح', 201);
    }

    public function show(Role $role)
    {
        return $this->success($role->load('permissions'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'superadmin') {
            return $this->error('لا يمكن تعديل دور المشرف العام', 403);
        }
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return $this->success($role->load('permissions'), 'تم تحديث الدور بنجاح');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'superadmin') {
            return $this->error('لا يمكن حذف دور المشرف العام', 403);
        }

        $role->delete();

        return $this->success(null, 'تم حذف الدور بنجاح');
    }
}
