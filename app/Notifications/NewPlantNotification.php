<?php

namespace App\Notifications;

use App\Models\Plant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewPlantNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Plant $plant)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_plant',
            'title' => 'نبتة جديدة بانتظار المراجعة 🌿',
            'subtitle' => "تمت زراعة {$this->plant->name} في " . ($this->plant->governorate?->name_ar ?? 'محافظة غير محددة'),
            'plant_id' => $this->plant->id,
            'time' => now()->toIso8601String(),
        ];
    }
}
