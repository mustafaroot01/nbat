<?php

namespace App\Notifications;

use App\Models\PlantReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PlantReport $report)
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
            'type' => 'new_report',
            'title' => 'بلاغ جديد عن نبتة ⚠️',
            'subtitle' => "هناك بلاغ حول {$this->report->plant->name} لسبب: {$this->report->reason}",
            'plant_id' => $this->report->plant_id,
            'report_id' => $this->report->id,
            'time' => now()->toIso8601String(),
        ];
    }
}
