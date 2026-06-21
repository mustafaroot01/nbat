<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Plant extends Model
{
    protected $fillable = [
        'user_id',
        'governorate_id',
        'campaign_id',
        'name',
        'type',
        'age',
        'image',
        'latitude',
        'longitude',
        'location',
        'notes',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Plant $plant) {
            if ($plant->isDirty(['latitude', 'longitude'])) {
                $plant->location = DB::raw(
                    "ST_GeomFromText('POINT({$plant->longitude} {$plant->latitude})')"
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PlantStatusLog::class)->latest();
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }
}
