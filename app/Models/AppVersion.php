<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'platform',
        'version_number',
        'version_code',
        'update_type',
        'store_url',
        'release_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'version_code' => 'integer',
        ];
    }
}
