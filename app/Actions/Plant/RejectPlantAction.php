<?php

namespace App\Actions\Plant;

use App\Models\Plant;
use App\Repositories\PlantRepository;
use App\Services\FirebaseNotificationService;

class RejectPlantAction
{
    public function __construct(
        private PlantRepository $plantRepository,
        private FirebaseNotificationService $firebase
    ) {}

    public function execute(Plant $plant, string $reason): void
    {
        $this->plantRepository->reject($plant, $reason);

        $this->firebase->sendToUser(
            $plant->user_id,
            'تم رفض نبتتك ❌',
            "نبتتك ({$plant->name}) تم رفضها — السبب: {$reason}"
        );
    }
}
