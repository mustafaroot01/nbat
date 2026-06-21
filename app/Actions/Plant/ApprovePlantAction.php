<?php

namespace App\Actions\Plant;

use App\Models\Plant;
use App\Repositories\PlantRepository;
use App\Services\FirebaseNotificationService;

class ApprovePlantAction
{
    public function __construct(
        private PlantRepository $plantRepository,
        private FirebaseNotificationService $firebase
    ) {}

    public function execute(Plant $plant): void
    {
        $this->plantRepository->approve($plant);

        $this->firebase->sendToUser(
            $plant->user_id,
            'تمت الموافقة على نبتتك ✅',
            "نبتتك ({$plant->name}) تمت الموافقة عليها وتظهر الآن على الخريطة 🌱"
        );
    }
}
