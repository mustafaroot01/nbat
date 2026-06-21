<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserLevel;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserLevelController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $levels = UserLevel::orderBy('min_plants', 'asc')->get();

        return $this->success($levels);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_levels,name',
            'min_plants' => 'required|integer|min:0',
        ]);

        $level = UserLevel::create($request->only('name', 'min_plants'));

        return $this->success($level, 'تم إضافة اللقب بنجاح', 201);
    }

    public function update(Request $request, UserLevel $userLevel)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:user_levels,name,' . $userLevel->id,
            'min_plants' => 'required|integer|min:0',
        ]);

        $userLevel->update($request->only('name', 'min_plants'));

        return $this->success($userLevel, 'تم تحديث اللقب بنجاح');
    }

    public function destroy(UserLevel $userLevel)
    {
        $userLevel->delete();

        return $this->success(null, 'تم حذف اللقب بنجاح');
    }
}
