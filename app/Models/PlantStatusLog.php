<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'admin_id',
        'old_status',
        'new_status',
        'reason',
    ];

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
