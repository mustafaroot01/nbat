<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreenAd extends Model
{
    protected $fillable = [
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
