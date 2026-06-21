<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::with('governorate')->withCount('plants');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admin = $request->user();
        if ($admin && $admin->governorate_id) {
            $query->where('governorate_id', $admin->governorate_id);
        } elseif ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('is_trusted')) {
            $isTrusted = $request->is_trusted === 'true';
            $query->where('is_trusted', $isTrusted);
        }

        $itemsPerPage = $request->get('itemsPerPage', 15);
        // Handle "all" case (-1) in VDataTable
        if ($itemsPerPage == -1) {
            $itemsPerPage = $query->count();
            if ($itemsPerPage == 0) $itemsPerPage = 15;
        }

        $users = $query->latest()->paginate($itemsPerPage);

        $statsQuery = User::query();
        if ($admin && $admin->governorate_id) {
            $statsQuery->where('governorate_id', $admin->governorate_id);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'trusted' => (clone $statsQuery)->where('is_trusted', true)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'suspended' => (clone $statsQuery)->where('is_active', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users)->resolve(),
            'stats' => $stats,
        ]);
    }

    public function show(User $user)
    {
        $user->load(['governorate', 'plants.governorate', 'plants.statusLogs.admin'])->loadCount([
            'plants',
            'plants as approved_plants_count' => function ($query) {
                $query->where('status', 'approved');
            },
            'plants as rejected_plants_count' => function ($query) {
                $query->where('status', 'rejected');
            }
        ]);

        return $this->success(new UserResource($user));
    }

    public function toggle(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'تم تفعيل' : 'تم إيقاف';

        return $this->success(null, "{$status} المستخدم");
    }

    public function toggleTrusted(User $user)
    {
        $user->update(['is_trusted' => !$user->is_trusted]);

        $status = $user->is_trusted ? 'تم توثيق' : 'تم إلغاء توثيق';

        return $this->success(null, "{$status} المستخدم");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'governorate_id' => 'required|exists:governorates,id',
        ]);

        $user->update($validated);

        return $this->success(new UserResource($user->load('governorate')), 'تم تحديث بيانات المستخدم بنجاح');
    }
}
