<?php

namespace App\Services;

use App\Models\Plant;
use App\Models\PlantStatusLog;
use App\Models\Setting;
use App\Repositories\PlantRepository;

class PlantService
{
    public function __construct(private PlantRepository $plantRepository) {}

    public function getPendingPlants()
    {
        return $this->plantRepository->getPending();
    }

    public function getAllPlants(array $filters = [], int $perPage = 15)
    {
        return $this->plantRepository->getAll($filters, $perPage);
    }

    public function getApprovedForMap(?int $governorateId = null)
    {
        return $this->plantRepository->getApproved($governorateId);
    }

    public function store(array $data, int $userId): Plant
    {
        $user = \App\Models\User::find($userId);
        $isTrusted = $user?->is_trusted ?? false;

        $approvalRequired = Setting::getValue('plant_approval_required', 'true') === 'true';
        $autoApprove = !$approvalRequired || $isTrusted;

        $data['status'] = $autoApprove ? 'approved' : 'pending';
        $data['user_id'] = $userId;

        if ($autoApprove) {
            $data['reviewed_at'] = now();
        }

        $plant = $this->plantRepository->create($data);

        // Log auto-approval for trusted users or when approval is disabled
        if ($autoApprove) {
            PlantStatusLog::create([
                'plant_id'   => $plant->id,
                'admin_id'   => null,
                'old_status' => 'pending',
                'new_status' => 'approved',
                'reason'     => $isTrusted
                    ? 'اعتماد تلقائي — حساب موثّق'
                    : 'اعتماد تلقائي — نظام الاعتماد الفوري مفعّل',
            ]);
        }

        return $plant;
    }
}
