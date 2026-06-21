<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class OtpVerification extends Model
{
    use Prunable;

    protected $fillable = [
        'phone',
        'code',
        'attempts',
        'verified',
        'expires_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'verified'   => 'boolean',
            'expires_at' => 'datetime',
            'attempts'   => 'integer',
        ];
    }

    // ── Helpers ─────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(string $code): bool
    {
        return !$this->isExpired()
            && !$this->verified
            && hash_equals($this->code, $code);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone', $phone);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('verified', false);
    }

    // ── Auto-cleanup old records (php artisan model:prune) ──

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDay());
    }
}
