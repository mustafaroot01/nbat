<?php

namespace App\Services;

use App\Models\DeviceToken;

class FirebaseNotificationService
{
    public function sendToUser(int $userId, string $title, string $body): void
    {
        $tokens = DeviceToken::where('user_id', $userId)->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        // TODO: Implement Firebase Cloud Messaging via kreait/laravel-firebase
        // $messaging = app('firebase.messaging');
        // $message = CloudMessage::new()
        //     ->withNotification(Notification::create($title, $body));
        // $messaging->sendMulticast($message, $tokens);
    }

    public function sendToAdmin(string $title, string $body): void
    {
        // TODO: Send web push notification to admin dashboard
    }

    public function sendToAll(string $title, string $body, string $target = 'all'): void
    {
        $query = DeviceToken::query();

        if ($target === 'android') {
            $query->where('platform', 'android');
        } elseif ($target === 'ios') {
            $query->where('platform', 'ios');
        }

        $tokens = $query->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        // TODO: Implement batch sending via Firebase
    }

    public function sendToTopic(string $title, string $body, string $topic): void
    {
        // TODO: Implement topic messaging via Firebase
    }
}
