<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plant;

class PlantSeeder extends Seeder
{
    public function run(): void
    {
        $plants = [
            [
                'user_id' => 1,
                'name' => 'نخلة برحي',
                'type' => 'نخيل',
                'age' => 5,
                'latitude' => 33.3128,
                'longitude' => 44.3615,
                'governorate_id' => 1,
                'notes' => 'تمت الزراعة في الحديقة المنزلية',
                'status' => 'approved',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
            ],
            [
                'user_id' => 2,
                'name' => 'شجرة سدر',
                'type' => 'أشجار مثمرة',
                'age' => 2,
                'latitude' => 30.5081,
                'longitude' => 47.8153,
                'governorate_id' => 2,
                'notes' => 'سدر عراقي مطعم',
                'status' => 'approved',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
            ],
            [
                'user_id' => 3,
                'name' => 'زيتون أربقينو',
                'type' => 'زيتون',
                'age' => 3,
                'latitude' => 36.1901,
                'longitude' => 44.0092,
                'governorate_id' => 4,
                'notes' => 'زيتون مقاوم للجفاف',
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
            [
                'user_id' => 4,
                'name' => 'شجرة تين',
                'type' => 'أشجار مثمرة',
                'age' => 1,
                'latitude' => 36.3489,
                'longitude' => 43.1319,
                'governorate_id' => 3,
                'notes' => 'زراعة أمام المنزل',
                'status' => 'approved',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
            ],
            [
                'user_id' => 5,
                'name' => 'شجرة كينوكاربس',
                'type' => 'أشجار زينة',
                'age' => 1,
                'latitude' => 32.4705,
                'longitude' => 44.4258,
                'governorate_id' => 5,
                'notes' => 'زراعة على الرصيف الخارجي',
                'status' => 'rejected',
                'rejection_reason' => 'هذا النوع من الأشجار يؤثر على البنية التحتية، يرجى زراعة نوع آخر',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
            ],
        ];

        foreach ($plants as $plant) {
            Plant::firstOrCreate([
                'user_id' => $plant['user_id'],
                'name' => $plant['name'],
            ], $plant);
        }
    }
}
