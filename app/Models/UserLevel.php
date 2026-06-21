<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    protected $fillable = [
        'name',
        'min_plants',
    ];

    protected function casts(): array
    {
        return [
            'min_plants' => 'integer',
        ];
    }
}
