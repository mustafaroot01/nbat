<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Services\FirebaseNotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(private FirebaseNotificationService $firebase) {}

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'target' => 'required|in:all,android,ios,topic',
            'topic' => 'required_if:target,topic|nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->target === 'topic') {
            $this->firebase->sendToTopic($request->title, $request->body, $request->topic);
        } else {
            $this->firebase->sendToAll($request->title, $request->body, $request->target);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
        }

        NotificationLog::create([
            'title' => $request->title,
            'body' => $request->body,
            'image' => $imagePath,
            'target' => $request->target,
            'topic' => $request->topic,
            'sent_by' => auth()->id(),
        ]);

        return $this->success(null, 'تم إرسال الإشعار');
    }

    public function index()
    {
        $logs = NotificationLog::with('admin')
            ->latest('created_at')
            ->paginate(15);

        return $this->paginated($logs);
    }

    public function update(Request $request, NotificationLog $notification)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'target' => 'required|in:all,android,ios,topic',
            'topic' => 'required_if:target,topic|nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
            $notification->image = $imagePath;
        }

        $notification->title = $request->title;
        $notification->body = $request->body;
        $notification->target = $request->target;
        $notification->topic = $request->topic;
        $notification->save();

        return $this->success($notification, 'تم تحديث الإشعار / المقال بنجاح');
    }

    public function destroy(NotificationLog $notification)
    {
        $notification->delete();

        return $this->success(null, 'تم الحذف بنجاح');
    }
}
