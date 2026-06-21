<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $notifications = $user->notifications()
            ->take(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'info',
                    'title' => $notification->data['title'] ?? 'إشعار جديد',
                    'subtitle' => $notification->data['subtitle'] ?? '',
                    'plant_id' => $notification->data['plant_id'] ?? null,
                    'report_id' => $notification->data['report_id'] ?? null,
                    'time' => $notification->created_at->diffForHumans(),
                    'isSeen' => !is_null($notification->read_at),
                ];
            });

        $unreadCount = $user->unreadNotifications()->count();

        return $this->success([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string'
        ]);

        auth()->user()->unreadNotifications()
            ->whereIn('id', $request->ids)
            ->update(['read_at' => now()]);

        return $this->success(null, 'تم التحديد كمقروء');
    }

    public function destroy($id)
    {
        auth()->user()->notifications()->where('id', $id)->delete();
        
        return $this->success(null, 'تم مسح الإشعار');
    }
}
