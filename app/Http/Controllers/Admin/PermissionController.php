<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        // For permissions, we usually just need the list.
        $permissions = Permission::with('roles')->get();
        return $this->success($permissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name, 'guard_name' => 'admin']);

        return $this->success($permission, 'تم إنشاء الصلاحية بنجاح', 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return $this->success($permission, 'تم تحديث الصلاحية بنجاح');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return $this->success(null, 'تم حذف الصلاحية بنجاح');
    }
}
