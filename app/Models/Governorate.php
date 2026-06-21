<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Governorate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name_ar',
        'name_en',
        'is_covered',
    ];

    protected function casts(): array
    {
        return [
            'is_covered' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }
}
