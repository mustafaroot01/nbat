<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'target_plants',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'target_plants' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function plants()
    {
        return $this->hasMany(Plant::class);
    }
}
