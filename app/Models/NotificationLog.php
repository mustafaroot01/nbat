<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'body',
        'image',
        'target',
        'topic',
        'sent_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sent_by');
    }
}
